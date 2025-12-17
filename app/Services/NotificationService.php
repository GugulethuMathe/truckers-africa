<?php

namespace App\Services;

use App\Models\NotificationModel;
use App\Models\TruckDriverModel;
use App\Models\MerchantModel;
use App\Models\AdminModel;
use App\Libraries\Smsutil;
use CodeIgniter\Email\Email;

class NotificationService
{
    protected $notificationModel;
    protected $driverModel;
    protected $merchantModel;
    protected $email;
    protected $adminEmails;

    public function __construct()
    {
        $this->notificationModel = new NotificationModel();
        $this->driverModel = new TruckDriverModel();
        $this->merchantModel = new MerchantModel();
        $this->email = \Config\Services::email();
        // Admin recipients for system notifications (comma-separated in ENV)
        $configured = getenv('email.adminRecipients');
        $this->adminEmails = $configured ? array_filter(array_map('trim', explode(',', $configured))) : [];
    }
    /**
     * Resolve admin email recipients, always including the default admin mailbox
     */
    private function getAdminRecipients(): array
    {
        $recipients = $this->adminEmails;
        // Always include the primary admin inbox
        $recipients[] = 'admin@truckersafrica.com';

        // Backfill from admins table if still empty (or to add more addresses)
        try {
            $admins = (new AdminModel())->select('email')->findAll();
            foreach ($admins as $row) {
                if (!empty($row['email'])) {
                    $recipients[] = $row['email'];
                }
            }
        } catch (\Throwable $t) {
            log_message('error', 'Failed to load admin emails: ' . $t->getMessage());
        }

        // Deduplicate and sanitize
        $recipients = array_values(array_unique(array_filter(array_map('trim', $recipients))));
        return $recipients;
    }


    /**
     * Send notification when a driver places an order
     * Now supports branch-specific notifications
     */
    public function notifyOrderPlaced($orderId, $driverId, $merchantIds, $orderData, $locationId = null)
    {
        // Get driver details
        $driver = $this->driverModel->find($driverId);
        if (!$driver) return false;

        $driverName = $driver['name'] . ' ' . $driver['surname'];
        $bookingRef = $orderData['booking_reference'];
        $totalAmount = $orderData['grand_total'];

        // Notify each merchant about the new order
        foreach ($merchantIds as $merchantId) {
            $merchant = $this->merchantModel->find($merchantId);
            if (!$merchant) continue;

            // Check if there's a branch user for this location
            $branchUserModel = new \App\Models\BranchUserModel();
            $branchUser = null;
            if ($locationId) {
                $branchUser = $branchUserModel->findByLocationId($locationId);
            }

            if ($branchUser) {
                // Send notification to branch user instead of merchant
                // Create in-app notification for branch user
                $this->notificationModel->createNotification([
                    'recipient_type' => 'branch',
                    'recipient_id' => $branchUser['id'],
                    'notification_type' => 'booking_request',
                    'title' => 'New Order Received',
                    'message' => "New order #{$bookingRef} from {$driverName} for R{$totalAmount}. Please review and respond.",
                    'related_order_id' => $orderId,
                    'action_url' => site_url("branch/orders/view/{$orderId}"),
                    'priority' => 'high'
                ]);

                // Send email to branch user
                $this->sendOrderPlacedEmailToBranch($branchUser, $driver, $orderData);

                // Send SMS to branch user
                $this->sendOrderPlacedSMSToBranch($branchUser, $bookingRef, $driverName, $totalAmount);
            } else {
                // No branch user, send to main merchant
                // Create in-app notification for merchant
                $this->notificationModel->createNotification([
                    'recipient_type' => 'merchant',
                    'recipient_id' => $merchantId,
                    'notification_type' => 'booking_request',
                    'title' => 'New Order Received',
                    'message' => "New order #{$bookingRef} from {$driverName} for R{$totalAmount}. Please review and respond.",
                    'related_order_id' => $orderId,
                    'action_url' => site_url("merchant/orders/view/{$orderId}"),
                    'priority' => 'high'
                ]);

                // Send email to merchant
                $this->sendOrderPlacedEmailToMerchant($merchant, $driver, $orderData);
            }
        }

        // Create confirmation notification for driver
        $this->notificationModel->createNotification([
            'recipient_type' => 'driver',
            'recipient_id' => $driverId,
            'notification_type' => 'booking_request',
            'title' => 'Order Placed Successfully',
            'message' => "Your order #{$bookingRef} has been placed successfully. Merchants will review and respond soon.",
            'related_order_id' => $orderId,
            'action_url' => site_url("driver/orders/view/{$orderId}"),
            'priority' => 'normal'
        ]);

        // Send confirmation email to driver
        $this->sendOrderPlacedEmailToDriver($driver, $orderData);

        return true;
    }

    /**
     * Notify a driver that their registration was received
     */
    public function notifyDriverRegistered(int $driverId): void
    {
        $driver = $this->driverModel->find($driverId);
        if (!$driver) return;

        // Email
        try {
            $subject = 'Welcome to Truckers Africa – Driver Registration Received';
            $message = view('emails/driver_registered', [
                'driver' => $driver,
            ]);
            $this->sendEmail($driver['email'], $subject, $message);
        } catch (\Throwable $t) {
            log_message('error', 'Failed to email driver registration confirmation: ' . $t->getMessage());
        }

        // SMS
        try {
            $phone = $driver['contact_number'] ?? null;
            if (!empty($phone)) {
                $sms = new Smsutil();
                $smsMessage = 'Truckers Africa: Welcome! Your driver account has been created.';
                $sms->sendSMS($phone, $smsMessage);
            }
        } catch (\Throwable $t) {
            log_message('error', 'Failed to SMS driver registration confirmation: ' . $t->getMessage());
        }

        // Optional admin heads-up
        $adminSmsNumber = getenv('sms.adminPhone');
        if ($adminSmsNumber) {
            try {
                $sms = new Smsutil();
                $sms->sendSMS($adminSmsNumber, 'New driver registered: ' . ($driver['name'] ?? 'Unknown'));
            } catch (\Throwable $t) {
                log_message('error', 'Failed to SMS admin about new driver: ' . $t->getMessage());
            }
        }
    }

    /**
     * Send notification when merchant accepts an order
     */
    public function notifyOrderAccepted($orderId, $merchantId, $driverId, $orderData)
    {
        $merchant = $this->merchantModel->find($merchantId);
        $driver = $this->driverModel->find($driverId);
        
        if (!$merchant || !$driver) return false;

        $merchantName = $merchant['business_name'];
        $driverName = $driver['name'] . ' ' . $driver['surname'];
        $bookingRef = $orderData['booking_reference'];

        // Notify driver about acceptance
        $this->notificationModel->createNotification([
            'recipient_type' => 'driver',
            'recipient_id' => $driverId,
            'notification_type' => 'booking_accepted',
            'title' => 'Order Accepted!',
            'message' => "Great news! {$merchantName} has accepted your order #{$bookingRef}. Check your order details for next steps.",
            'related_order_id' => $orderId,
            'action_url' => site_url("driver/orders/view/{$orderId}"),
            'priority' => 'high'
        ]);

        // Notify merchant about their action
        $this->notificationModel->createNotification([
            'recipient_type' => 'merchant',
            'recipient_id' => $merchantId,
            'notification_type' => 'booking_accepted',
            'title' => 'Order Accepted',
            'message' => "You have accepted order #{$bookingRef} from {$driverName}. The driver has been notified.",
            'related_order_id' => $orderId,
            'action_url' => site_url("merchant/orders/view/{$orderId}"),
            'priority' => 'normal'
        ]);

        // Send emails
        $this->sendOrderAcceptedEmailToDriver($driver, $merchant, $orderData);
        $this->sendOrderAcceptedEmailToMerchant($merchant, $driver, $orderData);

        return true;
    }

    /**
     * Send notification when merchant rejects an order
     */
    public function notifyOrderRejected($orderId, $merchantId, $driverId, $orderData, $rejectionReason = null)
    {
        $merchant = $this->merchantModel->find($merchantId);
        $driver = $this->driverModel->find($driverId);
        
        if (!$merchant || !$driver) return false;

        $merchantName = $merchant['business_name'];
        $driverName = $driver['name'] . ' ' . $driver['surname'];
        $bookingRef = $orderData['booking_reference'];

        $rejectionMessage = $rejectionReason ? " Reason: {$rejectionReason}" : "";

        // Notify driver about rejection
        $this->notificationModel->createNotification([
            'recipient_type' => 'driver',
            'recipient_id' => $driverId,
            'notification_type' => 'booking_rejected',
            'title' => 'Order Update',
            'message' => "Unfortunately, {$merchantName} cannot fulfill your order #{$bookingRef} at this time.{$rejectionMessage} You can browse other merchants or try again later.",
            'related_order_id' => $orderId,
            'action_url' => site_url("driver/orders/view/{$orderId}"),
            'priority' => 'normal'
        ]);

        // Notify merchant about their action
        $this->notificationModel->createNotification([
            'recipient_type' => 'merchant',
            'recipient_id' => $merchantId,
            'notification_type' => 'booking_rejected',
            'title' => 'Order Rejected',
            'message' => "You have declined order #{$bookingRef} from {$driverName}. The driver has been notified.",
            'related_order_id' => $orderId,
            'action_url' => site_url("merchant/orders/view/{$orderId}"),
            'priority' => 'normal'
        ]);

        // Send emails
        $this->sendOrderRejectedEmailToDriver($driver, $merchant, $orderData, $rejectionReason);
        $this->sendOrderRejectedEmailToMerchant($merchant, $driver, $orderData, $rejectionReason);

        return true;
    }

    /**
     * Get unread notification count for user
     */
    public function getUnreadCount($userType, $userId)
    {
        return $this->notificationModel->getUnreadCount($userType, $userId);
    }

    /**
     * Get notifications for user with pagination
     */
    public function getNotifications($userType, $userId, $limit = 20, $unreadOnly = false, $offset = 0)
    {
        return $this->notificationModel->getNotificationsForUser($userType, $userId, $limit, $unreadOnly, $offset);
    }

    /**
     * Get total count of notifications for user
     */
    public function getTotalNotifications($userType, $userId, $unreadOnly = false)
    {
        return $this->notificationModel->getTotalNotificationsForUser($userType, $userId, $unreadOnly);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId)
    {
        return $this->notificationModel->markAsRead($notificationId);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead($userType, $userId)
    {
        return $this->notificationModel->markAllAsRead($userType, $userId);
    }

    // Email sending methods
    private function sendOrderPlacedEmailToMerchant($merchant, $driver, $orderData)
    {
        $subject = "New Order #{$orderData['booking_reference']} - Action Required";
        $driverName = $driver['name'] . ' ' . $driver['surname'];
        
        $message = view('emails/order_placed_merchant', [
            'merchant' => $merchant,
            'driver' => $driver,
            'order' => $orderData,
            'driver_name' => $driverName
        ]);

        return $this->sendEmail($merchant['email'], $subject, $message);
    }

    private function sendOrderPlacedEmailToDriver($driver, $orderData)
    {
        $subject = "Order Confirmation #{$orderData['booking_reference']}";

        $message = view('emails/order_placed_driver', [
            'driver' => $driver,
            'order' => $orderData
        ]);

        return $this->sendEmail($driver['email'], $subject, $message);
    }

    /**
     * Send order placed email to branch user
     */
    private function sendOrderPlacedEmailToBranch($branchUser, $driver, $orderData)
    {
        $subject = "New Order #{$orderData['booking_reference']} - Action Required";
        $driverName = $driver['name'] . ' ' . $driver['surname'];

        // Get branch details
        $branchUserModel = new \App\Models\BranchUserModel();
        $branchDetails = $branchUserModel->getBranchUserWithDetails($branchUser['id']);

        $message = view('emails/order_placed_branch', [
            'branchUser' => $branchDetails,
            'driver' => $driver,
            'order' => $orderData,
            'driver_name' => $driverName
        ]);

        try {
            $emailSent = $this->sendEmail($branchUser['email'], $subject, $message);
            if ($emailSent) {
                log_message('info', 'Order placed email sent to branch user: ' . $branchUser['email']);
            } else {
                log_message('warning', 'Failed to send order placed email to branch user: ' . $branchUser['email']);
            }
            return $emailSent;
        } catch (\Exception $e) {
            log_message('error', 'Exception sending order placed email to branch: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send order placed SMS to branch user
     */
    private function sendOrderPlacedSMSToBranch($branchUser, $bookingRef, $driverName, $totalAmount)
    {
        try {
            $phone = $branchUser['phone_number'] ?? null;
            if (!empty($phone)) {
                $sms = new Smsutil();
                $smsMessage = "Truckers Africa: New order #{$bookingRef} from {$driverName} for R{$totalAmount}. Please review and respond. Login: " . site_url('branch/login');
                $smsSent = $sms->sendSMS($phone, $smsMessage);
                if ($smsSent) {
                    log_message('info', 'Order placed SMS sent to branch user: ' . $phone);
                } else {
                    log_message('warning', 'Failed to send order placed SMS to branch user: ' . $phone);
                }
                return $smsSent;
            }
        } catch (\Exception $e) {
            log_message('error', 'Exception sending order placed SMS to branch: ' . $e->getMessage());
        }
        return false;
    }

    private function sendOrderAcceptedEmailToDriver($driver, $merchant, $orderData)
    {
        $subject = "Order #{$orderData['booking_reference']} Accepted!";
        
        $message = view('emails/order_accepted_driver', [
            'driver' => $driver,
            'merchant' => $merchant,
            'order' => $orderData
        ]);

        return $this->sendEmail($driver['email'], $subject, $message);
    }

    private function sendOrderAcceptedEmailToMerchant($merchant, $driver, $orderData)
    {
        $subject = "Order #{$orderData['booking_reference']} - Accepted Confirmation";
        
        $message = view('emails/order_accepted_merchant', [
            'merchant' => $merchant,
            'driver' => $driver,
            'order' => $orderData
        ]);

        return $this->sendEmail($merchant['email'], $subject, $message);
    }

    private function sendOrderRejectedEmailToDriver($driver, $merchant, $orderData, $rejectionReason)
    {
        $subject = "Order #{$orderData['booking_reference']} Update";
        
        $message = view('emails/order_rejected_driver', [
            'driver' => $driver,
            'merchant' => $merchant,
            'order' => $orderData,
            'rejection_reason' => $rejectionReason
        ]);

        return $this->sendEmail($driver['email'], $subject, $message);
    }

    private function sendOrderRejectedEmailToMerchant($merchant, $driver, $orderData, $rejectionReason)
    {
        $subject = "Order #{$orderData['booking_reference']} - Rejection Confirmation";
        
        $message = view('emails/order_rejected_merchant', [
            'merchant' => $merchant,
            'driver' => $driver,
            'order' => $orderData,
            'rejection_reason' => $rejectionReason
        ]);

        return $this->sendEmail($merchant['email'], $subject, $message);
    }

    private function sendEmail($to, $subject, $message)
    {
        try {
            if (is_array($to)) {
                $this->email->setTo(reset($to));
                $rest = array_values(array_slice($to, 1));
                if (!empty($rest)) {
                    $this->email->setCC($rest);
                }
            } else {
                $this->email->setTo($to);
            }
            $this->email->setSubject($subject);
            $this->email->setMessage($message);
            
            return $this->email->send();
        } catch (\Exception $e) {
            log_message('error', 'Email sending failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Notify admins that a new merchant registered
     */
    public function notifyAdminsNewMerchant(array $merchant): void
    {
        $recipients = $this->getAdminRecipients();
        if (empty($recipients)) return;

        $subject = 'New Merchant Registration';
        $message = view('emails/new_merchant_notification', [
            'full_name'    => $merchant['owner_name'] ?? '',
            'company_name' => $merchant['business_name'] ?? '',
            'email'        => $merchant['email'] ?? '',
            'phone'        => $merchant['business_contact_number'] ?? ''
        ]);

        try {
            $this->sendEmail($recipients, $subject, $message);
        } catch (\Throwable $t) {
            log_message('error', 'Failed to email admins about new merchant: ' . $t->getMessage());
        }

        // Optional: send SMS to a single admin mobile if configured
        $adminSmsNumber = getenv('sms.adminPhone');
        if ($adminSmsNumber) {
            try {
                $sms = new Smsutil();
                $short = 'New merchant: ' . ($merchant['business_name'] ?? 'Unknown') . ' (' . ($merchant['business_contact_number'] ?? 'n/a') . ')';
                $sms->sendSMS($adminSmsNumber, $short);
            } catch (\Throwable $t) {
                log_message('error', 'Failed to SMS admin about new merchant: ' . $t->getMessage());
            }
        }
    }

    /**
     * Notify admins that a new driver registered
     */
    public function notifyAdminsNewDriver(array $driver): void
    {
        $recipients = $this->getAdminRecipients();
        if (empty($recipients)) return;

        $subject = 'New Driver Registration';
        $driverName = trim(($driver['name'] ?? '') . ' ' . ($driver['surname'] ?? ''));
        $email = $driver['email'] ?? '';
        $phone = $driver['contact_number'] ?? '';

        $message = '<p>Hello Admin,</p>' .
                   '<p>A new driver has registered.</p>' .
                   '<ul>' .
                   '<li><strong>Name:</strong> ' . esc($driverName) . '</li>' .
                   '<li><strong>Email:</strong> ' . esc($email) . '</li>' .
                   '<li><strong>Phone:</strong> ' . esc($phone) . '</li>' .
                   '</ul>' .
                   '<p>Regards,<br/>Truckers Africa System</p>';

        try {
            $this->sendEmail($recipients, $subject, $message);
        } catch (\Throwable $t) {
            log_message('error', 'Failed to email admins about new driver: ' . $t->getMessage());
        }
    }

    /**
     * Notify admins that a new listing was created
     */
    public function notifyAdminsNewListing(array $listing, array $merchant): void
    {
        $recipients = $this->getAdminRecipients();
        if (empty($recipients)) return;

        $subject = 'New Listing Submitted for Approval';
        $title = $listing['title'] ?? '';
        $listingId = $listing['id'] ?? '';
        $merchantName = $merchant['business_name'] ?? '';

        $message = '<p>Hello Admin,</p>' .
                   '<p>A new listing has been submitted and requires your review.</p>' .
                   '<ul>' .
                   '<li><strong>Listing Title:</strong> ' . esc($title) . '</li>' .
                   '<li><strong>Listing ID:</strong> ' . esc((string)$listingId) . '</li>' .
                   '<li><strong>Merchant:</strong> ' . esc($merchantName) . '</li>' .
                   '</ul>' .
                   '<p>Please review it in the admin panel.</p>' .
                   '<p>Regards,<br/>Truckers Africa System</p>';

        try {
            $this->sendEmail($recipients, $subject, $message);
        } catch (\Throwable $t) {
            log_message('error', 'Failed to email admins about new listing: ' . $t->getMessage());
        }
    }

    /**
     * Notify a merchant that their registration was received (pending approval)
     */
    public function notifyMerchantRegistered(int $merchantId): void
    {
        $merchant = $this->merchantModel->find($merchantId);
        if (!$merchant) return;

        // Email to merchant
        try {
            $subject = 'Welcome to Truckers Africa – Registration Received';
            $message = view('emails/merchant_registered', [
                'merchant' => $merchant,
            ]);
            $this->sendEmail($merchant['email'], $subject, $message);
        } catch (\Throwable $t) {
            log_message('error', 'Failed to email merchant registration confirmation: ' . $t->getMessage());
        }

        // SMS to merchant
        try {
            $phone = $merchant['business_contact_number'] ?? null;
            if (empty($phone)) {
                $phone = $merchant['business_whatsapp_number'] ?? null;
            }
            if (!empty($phone)) {
                $sms = new Smsutil();
                $smsMessage = 'Truckers Africa: Thanks for registering. Your account is pending approval. We will notify you once approved.';
                $sms->sendSMS($phone, $smsMessage);
            }
        } catch (\Throwable $t) {
            log_message('error', 'Failed to SMS merchant registration confirmation: ' . $t->getMessage());
        }
    }

    /**
     * Notify a merchant that their account has been approved.
     */
    public function notifyMerchantApproved(int $merchantId, ?string $approvedByName = null): bool
    {
        $merchant = $this->merchantModel->find($merchantId);
        if (!$merchant) {
            return false;
        }

        // Create in-app notification
        try {
            $this->notificationModel->createNotification([
                'recipient_type' => 'merchant',
                'recipient_id' => $merchantId,
                'notification_type' => 'general',
                'title' => 'Your account has been approved',
                'message' => 'Congratulations! Your merchant account has been approved. You can now access all merchant features.',
                'action_url' => site_url('merchant/dashboard'),
                'priority' => 'normal'
            ]);
        } catch (\Throwable $t) {
            // Non-fatal if notifications fail
            log_message('error', 'Failed to create approval notification: ' . $t->getMessage());
        }

        // Send email
        $subject = 'Your Truckers Africa merchant account is approved';
        $message = view('emails/merchant_approved', [
            'merchant' => $merchant,
            'approved_by' => $approvedByName,
        ]);

        $emailSent = $this->sendEmail($merchant['email'], $subject, $message);

        // Send SMS if contact number is present
        try {
            $phone = $merchant['business_contact_number'] ?? null;
            if (empty($phone)) {
                $phone = $merchant['business_whatsapp_number'] ?? null;
            }

            if (!empty($phone)) {
                $sms = new Smsutil();
                $smsMessage = 'Truckers Africa: Your merchant account has been approved. You can now log in and start receiving orders: ' . site_url('merchant/dashboard');
                $sms->sendSMS($phone, $smsMessage);
            }
        } catch (\Throwable $t) {
            log_message('error', 'Failed to send approval SMS: ' . $t->getMessage());
        }

        return $emailSent;
    }

    /**
     * Notify admins when a merchant updates their profile during onboarding
     */
    public function notifyAdminMerchantProfileUpdated(array $merchant): void
    {
        $recipients = $this->getAdminRecipients();
        if (empty($recipients)) return;

        $subject = 'Merchant Profile Updated - ' . ($merchant['business_name'] ?? 'Unknown Business');
        $message = view('emails/admin_merchant_profile_updated', [
            'merchant' => $merchant
        ]);

        try {
            $this->sendEmail($recipients, $subject, $message);
        } catch (\Throwable $t) {
            log_message('error', 'Failed to email admins about merchant profile update: ' . $t->getMessage());
        }
    }

    /**
     * Notify a merchant that their listing has been approved
     */
    public function notifyMerchantListingApproved(int $listingId, ?string $approvedByName = null): bool
    {
        // Get listing details
        $listingModel = new \App\Models\MerchantListingModel();
        $listing = $listingModel->find($listingId);

        if (!$listing) {
            log_message('error', 'Listing not found for approval notification: ' . $listingId);
            return false;
        }

        // Get merchant details
        $merchant = $this->merchantModel->find($listing['merchant_id']);
        if (!$merchant) {
            log_message('error', 'Merchant not found for listing approval notification: ' . $listing['merchant_id']);
            return false;
        }

        // Create in-app notification
        try {
            $this->notificationModel->createNotification([
                'recipient_type' => 'merchant',
                'recipient_id' => $merchant['id'],
                'notification_type' => 'listing_approved',
                'title' => 'Listing Approved!',
                'message' => 'Great news! Your listing "' . $listing['title'] . '" has been approved and is now live on the platform.',
                'action_url' => site_url('merchant/listings'),
                'priority' => 'high'
            ]);
        } catch (\Throwable $t) {
            log_message('error', 'Failed to create listing approval notification: ' . $t->getMessage());
        }

        // Send email
        $subject = 'Your Listing Has Been Approved - ' . $listing['title'];
        $message = view('emails/listing_approved', [
            'merchant' => $merchant,
            'listing' => $listing,
            'approved_by' => $approvedByName,
        ]);

        $emailSent = false;
        try {
            $emailSent = $this->sendEmail($merchant['email'], $subject, $message);
        } catch (\Throwable $t) {
            log_message('error', 'Failed to send listing approval email: ' . $t->getMessage());
        }

        // Send SMS if contact number is present
        try {
            $phone = $merchant['business_contact_number'] ?? null;
            if (empty($phone)) {
                $phone = $merchant['business_whatsapp_number'] ?? null;
            }

            if (!empty($phone)) {
                $sms = new Smsutil();
                $smsMessage = 'Truckers Africa: Your listing "' . substr($listing['title'], 0, 50) . '" has been approved and is now live! Drivers can now find and book your service.';
                $sms->sendSMS($phone, $smsMessage);
            }
        } catch (\Throwable $t) {
            log_message('error', 'Failed to send listing approval SMS: ' . $t->getMessage());
        }

        return $emailSent;
    }

    /**
     * Notify admins when a merchant uploads a document
     */
    public function notifyAdminsDocumentUploaded(int $documentId, array $merchant, string $documentType): void
    {
        $recipients = $this->getAdminRecipients();
        if (empty($recipients)) return;

        $documentTypeLabels = [
            'owner_id' => 'Owner ID',
            'company_registration' => 'Company Registration',
            'proof_of_residence' => 'Proof of Residence',
            'business_image' => 'Business Image'
        ];

        $documentLabel = $documentTypeLabels[$documentType] ?? ucwords(str_replace('_', ' ', $documentType));

        $subject = 'New Document Uploaded - ' . ($merchant['business_name'] ?? 'Merchant');
        $message = view('emails/admin_document_uploaded', [
            'merchant' => $merchant,
            'document_type' => $documentLabel,
            'document_id' => $documentId
        ]);

        try {
            $this->sendEmail($recipients, $subject, $message);
        } catch (\Throwable $t) {
            log_message('error', 'Failed to email admins about document upload: ' . $t->getMessage());
        }
    }

    /**
     * Notify merchant when their document is approved
     */
    public function notifyMerchantDocumentApproved(int $documentId, ?string $approvedByName = null): bool
    {
        $documentModel = new \App\Models\MerchantDocumentModel();
        $document = $documentModel->find($documentId);

        if (!$document) {
            log_message('error', 'Document not found for approval notification: ' . $documentId);
            return false;
        }

        $merchant = $this->merchantModel->find($document['merchant_id']);
        if (!$merchant) {
            log_message('error', 'Merchant not found for document approval notification: ' . $document['merchant_id']);
            return false;
        }

        $documentTypeLabels = [
            'owner_id' => 'Owner ID',
            'company_registration' => 'Company Registration',
            'proof_of_residence' => 'Proof of Residence',
            'business_image' => 'Business Image'
        ];

        $documentLabel = $documentTypeLabels[$document['document_type']] ?? ucwords(str_replace('_', ' ', $document['document_type']));

        // Send email
        $subject = 'Document Approved - ' . $documentLabel;
        $message = view('emails/document_approved', [
            'merchant' => $merchant,
            'document_type' => $documentLabel,
            'approved_by' => $approvedByName,
        ]);

        $emailSent = false;
        try {
            $emailSent = $this->sendEmail($merchant['email'], $subject, $message);
        } catch (\Throwable $t) {
            log_message('error', 'Failed to send document approval email: ' . $t->getMessage());
        }

        return $emailSent;
    }

    /**
     * Notify merchant when their document is rejected
     */
    public function notifyMerchantDocumentRejected(int $documentId, ?string $rejectionReason = null, ?string $rejectedByName = null): bool
    {
        $documentModel = new \App\Models\MerchantDocumentModel();
        $document = $documentModel->find($documentId);

        if (!$document) {
            log_message('error', 'Document not found for rejection notification: ' . $documentId);
            return false;
        }

        $merchant = $this->merchantModel->find($document['merchant_id']);
        if (!$merchant) {
            log_message('error', 'Merchant not found for document rejection notification: ' . $document['merchant_id']);
            return false;
        }

        $documentTypeLabels = [
            'owner_id' => 'Owner ID',
            'company_registration' => 'Company Registration',
            'proof_of_residence' => 'Proof of Residence',
            'business_image' => 'Business Image'
        ];

        $documentLabel = $documentTypeLabels[$document['document_type']] ?? ucwords(str_replace('_', ' ', $document['document_type']));

        // Send email
        $subject = 'Document Requires Attention - ' . $documentLabel;
        $message = view('emails/document_rejected', [
            'merchant' => $merchant,
            'document_type' => $documentLabel,
            'rejection_reason' => $rejectionReason ?? 'Document does not meet requirements',
            'rejected_by' => $rejectedByName,
        ]);

        $emailSent = false;
        try {
            $emailSent = $this->sendEmail($merchant['email'], $subject, $message);
        } catch (\Throwable $t) {
            log_message('error', 'Failed to send document rejection email: ' . $t->getMessage());
        }

        return $emailSent;
    }
}
