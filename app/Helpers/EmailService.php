<?php

namespace App\Helpers;

use CodeIgniter\Config\Services;

/**
 * EmailService Helper
 *
 * Centralized email notification service for the Truckers Africa platform.
 * Handles all email notifications for important system actions.
 */
class EmailService
{
    protected $email;
    protected $config;

    public function __construct()
    {
        $this->email = Services::email();
        $this->config = config('Email');
    }

    /**
     * Send order confirmation email to driver
     *
     * @param array $orderData Order details
     * @param array $driverData Driver information
     * @return bool
     */
    public function sendOrderConfirmationToDriver($orderData, $driverData)
    {
        try {
            $this->email->setFrom($this->config->fromEmail, $this->config->fromName);
            $this->email->setTo($driverData['email']);
            $this->email->setSubject('Order Confirmation - Booking #' . $orderData['booking_reference']);

            $message = view('emails/order_confirmation_driver', [
                'driver_name' => $driverData['first_name'] . ' ' . $driverData['last_name'],
                'booking_reference' => $orderData['booking_reference'],
                'merchant_name' => $orderData['merchant_name'],
                'location_name' => $orderData['location_name'],
                'total_amount' => $orderData['total_amount'],
                'currency' => $orderData['currency'],
                'order_date' => $orderData['created_at']
            ]);

            $this->email->setMessage($message);

            if ($this->email->send()) {
                log_message('info', 'Order confirmation email sent to driver: ' . $driverData['email']);
                return true;
            } else {
                log_message('error', 'Failed to send order confirmation to driver: ' . $this->email->printDebugger(['headers']));
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Email service error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send new order notification to merchant
     *
     * @param array $orderData Order details
     * @param array $merchantData Merchant information
     * @return bool
     */
    public function sendNewOrderToMerchant($orderData, $merchantData)
    {
        try {
            $this->email->setFrom($this->config->fromEmail, $this->config->fromName);
            $this->email->setTo($merchantData['email']);
            $this->email->setSubject('New Order Received - Booking #' . $orderData['booking_reference']);

            $message = view('emails/new_order_merchant', [
                'merchant_name' => $merchantData['business_name'],
                'booking_reference' => $orderData['booking_reference'],
                'driver_name' => $orderData['driver_name'],
                'location_name' => $orderData['location_name'],
                'total_amount' => $orderData['total_amount'],
                'currency' => $orderData['currency'],
                'order_date' => $orderData['created_at'],
                'order_items' => $orderData['items'] ?? []
            ]);

            $this->email->setMessage($message);

            if ($this->email->send()) {
                log_message('info', 'New order notification sent to merchant: ' . $merchantData['email']);
                return true;
            } else {
                log_message('error', 'Failed to send new order to merchant: ' . $this->email->printDebugger(['headers']));
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Email service error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send new order notification to branch manager
     *
     * @param array $orderData Order details
     * @param array $branchData Branch manager information
     * @return bool
     */
    public function sendNewOrderToBranch($orderData, $branchData)
    {
        try {
            $this->email->setFrom($this->config->fromEmail, $this->config->fromName);
            $this->email->setTo($branchData['email']);
            $this->email->setSubject('New Order Received - Booking #' . $orderData['booking_reference']);

            $message = view('emails/new_order_branch', [
                'branch_name' => $branchData['name'],
                'location_name' => $orderData['location_name'],
                'booking_reference' => $orderData['booking_reference'],
                'driver_name' => $orderData['driver_name'],
                'total_amount' => $orderData['total_amount'],
                'currency' => $orderData['currency'],
                'order_date' => $orderData['created_at'],
                'order_items' => $orderData['items'] ?? []
            ]);

            $this->email->setMessage($message);

            if ($this->email->send()) {
                log_message('info', 'New order notification sent to branch: ' . $branchData['email']);
                return true;
            } else {
                log_message('error', 'Failed to send new order to branch: ' . $this->email->printDebugger(['headers']));
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Email service error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send order status update to driver
     *
     * @param array $orderData Order details
     * @param array $driverData Driver information
     * @param string $status New order status (accepted, rejected, completed)
     * @return bool
     */
    public function sendOrderStatusUpdateToDriver($orderData, $driverData, $status)
    {
        try {
            $this->email->setFrom($this->config->fromEmail, $this->config->fromName);
            $this->email->setTo($driverData['email']);

            $statusText = ucfirst($status);
            $this->email->setSubject('Order ' . $statusText . ' - Booking #' . $orderData['booking_reference']);

            $message = view('emails/order_status_driver', [
                'driver_name' => $driverData['first_name'] . ' ' . $driverData['last_name'],
                'booking_reference' => $orderData['booking_reference'],
                'status' => $statusText,
                'merchant_name' => $orderData['merchant_name'],
                'location_name' => $orderData['location_name'],
                'total_amount' => $orderData['total_amount'],
                'currency' => $orderData['currency']
            ]);

            $this->email->setMessage($message);

            if ($this->email->send()) {
                log_message('info', 'Order status update email sent to driver: ' . $driverData['email']);
                return true;
            } else {
                log_message('error', 'Failed to send order status to driver: ' . $this->email->printDebugger(['headers']));
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Email service error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send merchant approval notification
     *
     * @param array $merchantData Merchant information
     * @param string $status Approval status (approved, rejected)
     * @return bool
     */
    public function sendMerchantApprovalNotification($merchantData, $status)
    {
        try {
            $this->email->setFrom($this->config->fromEmail, $this->config->fromName);
            $this->email->setTo($merchantData['email']);

            $statusText = ucfirst($status);
            $this->email->setSubject('Merchant Account ' . $statusText . ' - Truckers Africa');

            $message = view('emails/merchant_approval', [
                'business_name' => $merchantData['business_name'],
                'status' => $statusText,
                'contact_name' => $merchantData['contact_person']
            ]);

            $this->email->setMessage($message);

            if ($this->email->send()) {
                log_message('info', 'Merchant approval notification sent: ' . $merchantData['email']);
                return true;
            } else {
                log_message('error', 'Failed to send merchant approval: ' . $this->email->printDebugger(['headers']));
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Email service error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send subscription activation notification
     *
     * @param array $merchantData Merchant information
     * @param array $subscriptionData Subscription details
     * @return bool
     */
    public function sendSubscriptionActivation($merchantData, $subscriptionData)
    {
        try {
            $this->email->setFrom($this->config->fromEmail, $this->config->fromName);
            $this->email->setTo($merchantData['email']);
            $this->email->setSubject('Subscription Activated - Truckers Africa');

            $message = view('emails/subscription_activated', [
                'business_name' => $merchantData['business_name'],
                'plan_name' => $subscriptionData['plan_name'],
                'start_date' => $subscriptionData['start_date'],
                'end_date' => $subscriptionData['end_date'],
                'amount' => $subscriptionData['amount']
            ]);

            $this->email->setMessage($message);

            if ($this->email->send()) {
                log_message('info', 'Subscription activation email sent: ' . $merchantData['email']);
                return true;
            } else {
                log_message('error', 'Failed to send subscription activation: ' . $this->email->printDebugger(['headers']));
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Email service error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send subscription expiry reminder
     *
     * @param array $merchantData Merchant information
     * @param array $subscriptionData Subscription details
     * @param int $daysRemaining Days until expiry
     * @return bool
     */
    public function sendSubscriptionExpiryReminder($merchantData, $subscriptionData, $daysRemaining)
    {
        try {
            $this->email->setFrom($this->config->fromEmail, $this->config->fromName);
            $this->email->setTo($merchantData['email']);
            $this->email->setSubject('Subscription Expiring Soon - Truckers Africa');

            $message = view('emails/subscription_expiry_reminder', [
                'business_name' => $merchantData['business_name'],
                'plan_name' => $subscriptionData['plan_name'],
                'days_remaining' => $daysRemaining,
                'end_date' => $subscriptionData['end_date']
            ]);

            $this->email->setMessage($message);

            if ($this->email->send()) {
                log_message('info', 'Subscription expiry reminder sent: ' . $merchantData['email']);
                return true;
            } else {
                log_message('error', 'Failed to send expiry reminder: ' . $this->email->printDebugger(['headers']));
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Email service error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send payment confirmation
     *
     * @param array $merchantData Merchant information
     * @param array $paymentData Payment details
     * @return bool
     */
    public function sendPaymentConfirmation($merchantData, $paymentData)
    {
        try {
            $this->email->setFrom($this->config->fromEmail, $this->config->fromName);
            $this->email->setTo($merchantData['email']);
            $this->email->setSubject('Payment Received - Truckers Africa');

            $message = view('emails/payment_confirmation', [
                'business_name' => $merchantData['business_name'],
                'amount' => $paymentData['amount'],
                'payment_reference' => $paymentData['reference'],
                'payment_date' => $paymentData['date'],
                'description' => $paymentData['description']
            ]);

            $this->email->setMessage($message);

            if ($this->email->send()) {
                log_message('info', 'Payment confirmation sent: ' . $merchantData['email']);
                return true;
            } else {
                log_message('error', 'Failed to send payment confirmation: ' . $this->email->printDebugger(['headers']));
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Email service error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send admin notification for new merchant registration
     *
     * @param array $merchantData Merchant information
     * @return bool
     */
    public function sendAdminNewMerchantNotification($merchantData)
    {
        try {
            $this->email->setFrom($this->config->fromEmail, $this->config->fromName);
            $this->email->setTo($this->config->fromEmail); // Send to admin
            $this->email->setSubject('New Merchant Registration - Truckers Africa');

            $message = view('emails/admin_new_merchant', [
                'business_name' => $merchantData['business_name'],
                'contact_person' => $merchantData['contact_person'],
                'email' => $merchantData['email'],
                'phone' => $merchantData['phone'],
                'registration_date' => $merchantData['created_at']
            ]);

            $this->email->setMessage($message);

            if ($this->email->send()) {
                log_message('info', 'Admin notification sent for new merchant: ' . $merchantData['business_name']);
                return true;
            } else {
                log_message('error', 'Failed to send admin notification: ' . $this->email->printDebugger(['headers']));
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Email service error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send welcome email to new driver
     *
     * @param array $driverData Driver information
     * @return bool
     */
    public function sendDriverWelcomeEmail($driverData)
    {
        try {
            $this->email->setFrom($this->config->fromEmail, $this->config->fromName);
            $this->email->setTo($driverData['email']);
            $this->email->setSubject('Welcome to Truckers Africa!');

            $message = view('emails/driver_welcome', [
                'driver_name' => $driverData['first_name'] . ' ' . $driverData['last_name']
            ]);

            $this->email->setMessage($message);

            if ($this->email->send()) {
                log_message('info', 'Welcome email sent to driver: ' . $driverData['email']);
                return true;
            } else {
                log_message('error', 'Failed to send welcome email: ' . $this->email->printDebugger(['headers']));
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Email service error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send welcome email to new merchant
     *
     * @param array $merchantData Merchant information
     * @return bool
     */
    public function sendMerchantWelcomeEmail($merchantData)
    {
        try {
            $this->email->setFrom($this->config->fromEmail, $this->config->fromName);
            $this->email->setTo($merchantData['email']);
            $this->email->setSubject('Welcome to Truckers Africa - Merchant Registration Received');

            $message = view('emails/merchant_welcome', [
                'business_name' => $merchantData['business_name'],
                'contact_name' => $merchantData['contact_person']
            ]);

            $this->email->setMessage($message);

            if ($this->email->send()) {
                log_message('info', 'Welcome email sent to merchant: ' . $merchantData['email']);
                return true;
            } else {
                log_message('error', 'Failed to send merchant welcome: ' . $this->email->printDebugger(['headers']));
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Email service error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send new listing request notification to merchant
     *
     * @param array $requestData Listing request details
     * @param array $merchantData Merchant information
     * @param array $branchData Branch user information
     * @return bool
     */
    public function sendNewListingRequestToMerchant($requestData, $merchantData, $branchData)
    {
        try {
            $this->email->setFrom($this->config->fromEmail, $this->config->fromName);
            $this->email->setTo($merchantData['email']);
            $this->email->setSubject('New Listing Request from Branch - Truckers Africa');

            $message = view('emails/new_listing_request', [
                'business_name' => $merchantData['business_name'],
                'branch_name' => $branchData['name'],
                'location_name' => $branchData['location_name'] ?? 'Branch Location',
                'request_title' => $requestData['title'],
                'request_description' => $requestData['description'],
                'suggested_price' => $requestData['suggested_price'],
                'currency_code' => $requestData['currency_code'],
                'justification' => $requestData['justification'],
                'request_date' => date('F j, Y'),
                'view_url' => base_url('merchant/listing-requests/view/' . $requestData['id'])
            ]);

            $this->email->setMessage($message);

            if ($this->email->send()) {
                log_message('info', 'New listing request notification sent to merchant: ' . $merchantData['email']);
                return true;
            } else {
                log_message('error', 'Failed to send listing request notification: ' . $this->email->printDebugger(['headers']));
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Email service error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send payment failed notification to merchant
     *
     * @param array $merchantData Merchant information
     * @param array $paymentData Payment failure details
     * @return bool
     */
    public function sendPaymentFailedNotification($merchantData, $paymentData)
    {
        try {
            $this->email->setFrom($this->config->fromEmail, $this->config->fromName);
            $this->email->setTo($merchantData['email']);
            $this->email->setSubject('Payment Failed - Action Required - Truckers Africa');

            $message = view('emails/payment_failed', [
                'business_name' => $merchantData['business_name'],
                'amount' => $paymentData['amount'],
                'payment_date' => $paymentData['date'] ?? date('Y-m-d H:i:s'),
                'reason' => $paymentData['reason'] ?? null
            ]);

            $this->email->setMessage($message);

            if ($this->email->send()) {
                log_message('info', 'Payment failed notification sent: ' . $merchantData['email']);
                return true;
            } else {
                log_message('error', 'Failed to send payment failed notification: ' . $this->email->printDebugger(['headers']));
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Email service error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send subscription status change notification to merchant
     *
     * @param array $merchantData Merchant information
     * @param array $subscriptionData Subscription details with old and new status
     * @return bool
     */
    public function sendSubscriptionStatusChange($merchantData, $subscriptionData)
    {
        try {
            $this->email->setFrom($this->config->fromEmail, $this->config->fromName);
            $this->email->setTo($merchantData['email']);

            $oldStatus = $subscriptionData['old_status'];
            $newStatus = $subscriptionData['new_status'];

            // Determine email styling based on new status
            $statusColors = [
                'active' => ['header' => '#10b981', 'badge_bg' => '#d1fae5', 'badge_border' => '#10b981', 'icon' => '✅', 'heading_color' => '#047857'],
                'trial' => ['header' => '#3b82f6', 'badge_bg' => '#dbeafe', 'badge_border' => '#3b82f6', 'icon' => '🎉', 'heading_color' => '#1e40af'],
                'expired' => ['header' => '#ef4444', 'badge_bg' => '#fee2e2', 'badge_border' => '#ef4444', 'icon' => '⚠️', 'heading_color' => '#991b1b'],
                'cancelled' => ['header' => '#f59e0b', 'badge_bg' => '#fef3c7', 'badge_border' => '#f59e0b', 'icon' => 'ℹ️', 'heading_color' => '#92400e'],
                'past_due' => ['header' => '#f59e0b', 'badge_bg' => '#fff3cd', 'badge_border' => '#ffc107', 'icon' => '⚠️', 'heading_color' => '#856404']
            ];

            $colors = $statusColors[$newStatus] ?? $statusColors['active'];

            // Status-specific headings and messages
            $statusHeadings = [
                'active' => 'Subscription Activated',
                'trial' => 'Trial Period Started',
                'expired' => 'Subscription Expired',
                'cancelled' => 'Subscription Cancelled',
                'past_due' => 'Payment Overdue'
            ];

            $statusMessages = [
                'active' => 'Your subscription is now active and your business is visible to drivers.',
                'trial' => 'Your free trial has started and your business is now visible to drivers.',
                'expired' => 'Your subscription has expired and your business is no longer visible to drivers.',
                'cancelled' => 'Your subscription has been cancelled.',
                'past_due' => 'Your subscription payment is overdue. Please update your payment method.'
            ];

            $this->email->setSubject($statusHeadings[$newStatus] . ' - Truckers Africa');

            $message = view('emails/subscription_status_change', [
                'business_name' => $merchantData['business_name'],
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'plan_name' => $subscriptionData['plan_name'],
                'date_changed' => date('Y-m-d H:i:s'),
                'expiry_date' => $subscriptionData['expiry_date'] ?? null,
                'trial_ends' => $subscriptionData['trial_ends'] ?? null,
                'status_color' => $colors['header'],
                'badge_bg' => $colors['badge_bg'],
                'badge_border' => $colors['badge_border'],
                'email_icon' => $colors['icon'],
                'heading_color' => $colors['heading_color'],
                'status_heading' => $statusHeadings[$newStatus],
                'status_message' => $statusMessages[$newStatus],
                'additional_message' => $subscriptionData['additional_message'] ?? null
            ]);

            $this->email->setMessage($message);

            if ($this->email->send()) {
                log_message('info', 'Subscription status change notification sent: ' . $merchantData['email'] . ' (Status: ' . $oldStatus . ' -> ' . $newStatus . ')');
                return true;
            } else {
                log_message('error', 'Failed to send subscription status change notification: ' . $this->email->printDebugger(['headers']));
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Email service error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send trial expiry reminder to merchant
     *
     * @param array $merchantData Merchant information
     * @param array $subscriptionData Subscription and trial details
     * @param int $daysRemaining Days remaining until trial expires
     * @return bool
     */
    public function sendTrialExpiryReminder($merchantData, $subscriptionData, $daysRemaining)
    {
        try {
            $this->email->setFrom($this->config->fromEmail, $this->config->fromName);
            $this->email->setTo($merchantData['email']);
            $this->email->setSubject('Your Trial Ends in ' . $daysRemaining . ' ' . ($daysRemaining == 1 ? 'Day' : 'Days') . ' - Truckers Africa');

            $message = view('emails/trial_expiry_reminder', [
                'business_name' => $merchantData['business_name'],
                'days_remaining' => $daysRemaining,
                'trial_started' => $subscriptionData['trial_started'],
                'trial_ends' => $subscriptionData['trial_ends'],
                'plan_name' => $subscriptionData['plan_name'],
                'plan_price' => $subscriptionData['plan_price'],
                'plan_features' => $subscriptionData['plan_features'] ?? null,
                'performance_stats' => $subscriptionData['performance_stats'] ?? null,
                'additional_message' => $subscriptionData['additional_message'] ?? null
            ]);

            $this->email->setMessage($message);

            if ($this->email->send()) {
                log_message('info', 'Trial expiry reminder sent: ' . $merchantData['email'] . ' (Days remaining: ' . $daysRemaining . ')');
                return true;
            } else {
                log_message('error', 'Failed to send trial expiry reminder: ' . $this->email->printDebugger(['headers']));
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Email service error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send listing approval notification to branch manager
     *
     * @param array $listingData Listing details
     * @param array $branchData Branch user information
     * @param array $locationData Location information
     * @return bool
     */
    public function sendBranchListingApprovalNotification($listingData, $branchData, $locationData)
    {
        try {
            $this->email->setFrom($this->config->fromEmail, $this->config->fromName);
            $this->email->setTo($branchData['email']);
            $this->email->setSubject('Listing Approved - ' . $listingData['title'] . ' - Truckers Africa');

            $message = view('emails/branch_listing_approved', [
                'branchUser' => $branchData,
                'listing' => $listingData,
                'location' => $locationData
            ]);

            $this->email->setMessage($message);

            if ($this->email->send()) {
                log_message('info', 'Branch listing approval email sent to: ' . $branchData['email'] . ' for listing ID: ' . $listingData['id']);
                return true;
            } else {
                log_message('error', 'Failed to send branch listing approval email: ' . $this->email->printDebugger(['headers']));
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Email service error in sendBranchListingApprovalNotification: ' . $e->getMessage());
            return false;
        }
    }
}
