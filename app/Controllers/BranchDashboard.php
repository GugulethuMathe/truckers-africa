<?php

namespace App\Controllers;

use App\Models\BranchUserModel;
use App\Models\MerchantLocationModel;
use App\Models\MasterOrderModel;
use App\Models\OrderItemModel;
use App\Models\MerchantListingModel;
use App\Models\ListingRequestModel;
use CodeIgniter\Controller;

class BranchDashboard extends Controller
{
    protected $branchUserModel;
    protected $locationModel;
    protected $masterOrderModel;
    protected $orderItemModel;
    protected $listingModel;
    protected $listingRequestModel;

    public function __construct()
    {
        $this->branchUserModel = new BranchUserModel();
        $this->locationModel = new MerchantLocationModel();
        $this->masterOrderModel = new MasterOrderModel();
        $this->orderItemModel = new OrderItemModel();
        $this->listingModel = new MerchantListingModel();
        $this->listingRequestModel = new ListingRequestModel();
    }

    /**
     * Check if user is logged in as branch user and merchant has active subscription
     */
    private function checkAuth()
    {
        if (!session()->get('is_logged_in') || session()->get('user_type') !== 'branch') {
            return redirect()->to('branch/login')->with('error', 'Please login to continue');
        }

        // Check merchant subscription status
        $merchantId = session()->get('merchant_id');
        if ($merchantId) {
            $subscriptionModel = new \App\Models\SubscriptionModel();
            $merchantSubscription = $subscriptionModel
                ->where('merchant_id', $merchantId)
                ->whereIn('status', ['active', 'trial'])
                ->orderBy('updated_at', 'DESC')
                ->first();

            if (!$merchantSubscription) {
                // Merchant has no active subscription - log out the branch user
                session()->destroy();
                return redirect()->to('branch/login')->with('error', 'Access denied. Your merchant account does not have an active subscription. Please contact your business owner to renew the subscription.');
            }
        }

        return null;
    }

    /**
     * Branch Dashboard Home
     */
    public function index()
    {
        $authCheck = $this->checkAuth();
        if ($authCheck) return $authCheck;

        $locationId = session()->get('location_id');
        $branchUserId = session()->get('branch_user_id');

        // Get branch user details with location info
        $branchUser = $this->branchUserModel->getBranchUserWithDetails($branchUserId);

        // Get orders for this location
        $orders = $this->getLocationOrders($locationId);

        // Calculate statistics
        $stats = [
            'total_orders' => count($orders),
            'pending_orders' => count(array_filter($orders, fn($o) => $o['order_status'] === 'pending')),
            'completed_orders' => count(array_filter($orders, fn($o) => $o['order_status'] === 'completed')),
            'total_revenue' => array_sum(array_column($orders, 'grand_total'))
        ];

        // Get recent orders (last 10)
        $recentOrders = array_slice($orders, 0, 10);

        // Get listings count for this location
        $listingsCount = $this->listingModel->where('location_id', $locationId)
                                            ->where('status', 'approved')
                                            ->countAllResults();

        // Get merchant subscription status
        $merchantId = session()->get('merchant_id');
        $merchantSubscription = null;
        if ($merchantId) {
            $subscriptionModel = new \App\Models\SubscriptionModel();
            $merchantSubscription = $subscriptionModel->getCurrentSubscription($merchantId);
        }

        $data = [
            'page_title' => 'Branch Dashboard',
            'branch_user' => $branchUser,
            'stats' => $stats,
            'recent_orders' => $recentOrders,
            'listings_count' => $listingsCount,
            'merchant_subscription' => $merchantSubscription
        ];

        return view('branch/dashboard', $data);
    }

    /**
     * Get all orders for a specific location
     */
    private function getLocationOrders($locationId)
    {
        // Get all order items for this location
        $orderItems = $this->orderItemModel
                           ->select('order_items.*, master_orders.*, merchant_listings.title as listing_title, merchant_listings.location_id')
                           ->join('master_orders', 'master_orders.id = order_items.master_order_id')
                           ->join('merchant_listings', 'merchant_listings.id = order_items.listing_id')
                           ->where('merchant_listings.location_id', $locationId)
                           ->orderBy('master_orders.created_at', 'DESC')
                           ->findAll();

        // Group by master_order_id
        $ordersGrouped = [];
        foreach ($orderItems as $item) {
            $orderId = $item['master_order_id'];
            if (!isset($ordersGrouped[$orderId])) {
                $ordersGrouped[$orderId] = [
                    'id' => $item['master_order_id'],
                    'booking_reference' => $item['booking_reference'],
                    'driver_id' => $item['driver_id'],
                    'grand_total' => $item['grand_total'],
                    'order_status' => $item['order_status'],
                    'estimated_arrival' => $item['estimated_arrival'],
                    'vehicle_model' => $item['vehicle_model'],
                    'created_at' => $item['created_at'],
                    'items' => []
                ];
            }
            $ordersGrouped[$orderId]['items'][] = $item;
        }

        return array_values($ordersGrouped);
    }

    /**
     * View all orders for this branch
     */
    public function orders()
    {
        $authCheck = $this->checkAuth();
        if ($authCheck) return $authCheck;

        $locationId = session()->get('location_id');
        $orders = $this->getLocationOrders($locationId);

        $data = [
            'page_title' => 'Branch Orders',
            'orders' => $orders
        ];

        return view('branch/orders', $data);
    }

    /**
     * View single order details
     */
    public function viewOrder($orderId)
    {
        $authCheck = $this->checkAuth();
        if ($authCheck) return $authCheck;

        $locationId = session()->get('location_id');

        // Get order with items
        $order = $this->masterOrderModel->find($orderId);
        if (!$order) {
            return redirect()->to('branch/orders')->with('error', 'Order not found');
        }

        // Get order items for this location only with currency
        $orderItems = $this->orderItemModel
                           ->select('order_items.*, merchant_listings.title as listing_title, merchant_listings.location_id, merchant_listings.currency_code')
                           ->join('merchant_listings', 'merchant_listings.id = order_items.listing_id')
                           ->where('order_items.master_order_id', $orderId)
                           ->where('merchant_listings.location_id', $locationId)
                           ->findAll();

        if (empty($orderItems)) {
            return redirect()->to('branch/orders')->with('error', 'This order does not belong to your branch');
        }

        // Get driver details
        $driver = null;
        if (!empty($order['driver_id'])) {
            $driverModel = new \App\Models\TruckDriverModel();
            $driver = $driverModel->find($order['driver_id']);
        }

        $data = [
            'page_title' => 'Order Details',
            'order' => $order,
            'order_items' => $orderItems,
            'driver' => $driver
        ];

        return view('branch/order_view', $data);
    }

    /**
     * Update order status
     */
    public function updateOrderStatus($orderId)
    {
        $authCheck = $this->checkAuth();
        if ($authCheck) return $authCheck;

        $locationId = session()->get('location_id');
        $newStatus = $this->request->getPost('status');

        // Verify order belongs to this location
        $orderItems = $this->orderItemModel
                           ->select('order_items.*')
                           ->join('merchant_listings', 'merchant_listings.id = order_items.listing_id')
                           ->where('order_items.master_order_id', $orderId)
                           ->where('merchant_listings.location_id', $locationId)
                           ->findAll();

        if (empty($orderItems)) {
            return redirect()->back()->with('error', 'This order does not belong to your branch');
        }

        // Update order status
        $this->masterOrderModel->update($orderId, ['order_status' => $newStatus]);

        // Send notifications to driver
        try {
            $order = $this->masterOrderModel->find($orderId);
            if ($order) {
                $driverModel = new \App\Models\TruckDriverModel();
                $merchantModel = new \App\Models\MerchantModel();
                $locationModel = new \App\Models\MerchantLocationModel();
                $emailService = new \App\Helpers\EmailService();

                $driver = $driverModel->find($order['driver_id']);
                $location = $locationModel->find($locationId);

                // Get merchant info from location
                $merchant = null;
                if ($location) {
                    $merchant = $merchantModel->find($location['merchant_id']);
                }

                if ($driver) {
                    // Prepare email data
                    $emailOrderData = [
                        'booking_reference' => $order['booking_reference'],
                        'merchant_name' => $merchant['business_name'] ?? 'Merchant',
                        'location_name' => $location['location_name'] ?? 'Location',
                        'total_amount' => $order['grand_total'],
                        'currency' => 'ZAR',
                        'order_date' => $order['created_at']
                    ];

                    $driverData = [
                        'email' => $driver['email'],
                        'first_name' => $driver['first_name'],
                        'last_name' => $driver['last_name']
                    ];

                    // Send email notification
                    $emailService->sendOrderStatusUpdateToDriver($emailOrderData, $driverData, $newStatus);

                    // Send SMS notification
                    if (!empty($driver['phone_number'])) {
                        $this->sendOrderStatusSMS($driver['phone_number'], $order['booking_reference'], $newStatus, $location['location_name'] ?? 'Location');
                    }
                }
            }
        } catch (\Exception $e) {
            // Log error but don't fail the status update
            log_message('error', 'Failed to send order status notifications: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Order status updated successfully and driver notified');
    }

    /**
     * Send SMS notification for order status change
     */
    private function sendOrderStatusSMS($phoneNumber, $bookingRef, $status, $locationName)
    {
        try {
            $statusMessages = [
                'accepted' => 'Your order has been ACCEPTED',
                'completed' => 'Your order has been COMPLETED',
                'rejected' => 'Your order has been REJECTED',
                'processing' => 'Your order is now being PROCESSED'
            ];

            $message = $statusMessages[$status] ?? 'Your order status has been updated';
            $smsText = "Truckers Africa: {$message}. Booking Ref: {$bookingRef} at {$locationName}. Check the app for details.";

            // Use Africa's Talking or other SMS service
            // For now, just log it
            log_message('info', "SMS to {$phoneNumber}: {$smsText}");

            // TODO: Integrate with SMS API (Africa's Talking, Twilio, etc.)
            // Example for Africa's Talking:
            // $sms = new \AfricasTalking\SMS\SMS();
            // $result = $sms->send([
            //     'to' => $phoneNumber,
            //     'message' => $smsText
            // ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to send SMS: ' . $e->getMessage());
        }
    }

    /**
     * Branch Profile Management
     */
    public function profile()
    {
        $authCheck = $this->checkAuth();
        if ($authCheck) return $authCheck;

        $branchUserId = session()->get('branch_user_id');
        $branchUser = $this->branchUserModel->getBranchUserWithDetails($branchUserId);

        $data = [
            'page_title' => 'Branch Profile',
            'branch_user' => $branchUser
        ];

        return view('branch/profile', $data);
    }

    /**
     * Update Branch Profile
     */
    public function updateProfile()
    {
        $authCheck = $this->checkAuth();
        if ($authCheck) return $authCheck;

        $branchUserId = session()->get('branch_user_id');
        $locationId = session()->get('location_id');

        // Get form data
        $branchData = [
            'full_name' => $this->request->getPost('full_name'),
            'phone_number' => $this->request->getPost('phone_number'),
            'email' => $this->request->getPost('email')
        ];

        $locationData = [
            'location_name' => $this->request->getPost('location_name'),
            // 'physical_address' => $this->request->getPost('physical_address'), // Read-only field
            'contact_number' => $this->request->getPost('contact_number'),
            'whatsapp_number' => $this->request->getPost('whatsapp_number'),
            'email' => $this->request->getPost('location_email'),
            'operating_hours' => $this->request->getPost('operating_hours')
        ];

        // Update branch user
        if (!$this->branchUserModel->update($branchUserId, $branchData)) {
            return redirect()->back()->with('error', 'Failed to update profile: ' . implode(', ', $this->branchUserModel->errors()));
        }

        // Update location
        if (!$this->locationModel->update($locationId, $locationData)) {
            return redirect()->back()->with('error', 'Failed to update location: ' . implode(', ', $this->locationModel->errors()));
        }

        // Update session data
        session()->set([
            'full_name' => $branchData['full_name'],
            'location_name' => $locationData['location_name']
        ]);

        return redirect()->back()->with('success', 'Profile updated successfully');
    }

    /**
     * Change Password
     */
    public function changePassword()
    {
        $authCheck = $this->checkAuth();
        if ($authCheck) return $authCheck;

        $branchUserId = session()->get('branch_user_id');

        $currentPassword = $this->request->getPost('current_password');
        $newPassword = $this->request->getPost('new_password');
        $confirmPassword = $this->request->getPost('confirm_password');

        // Validate
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            return redirect()->back()->with('error', 'All password fields are required');
        }

        if ($newPassword !== $confirmPassword) {
            return redirect()->back()->with('error', 'New passwords do not match');
        }

        if (strlen($newPassword) < 8) {
            return redirect()->back()->with('error', 'Password must be at least 8 characters');
        }

        // Verify current password
        $branchUser = $this->branchUserModel->find($branchUserId);
        if (!password_verify($currentPassword, $branchUser['password_hash'])) {
            return redirect()->back()->with('error', 'Current password is incorrect');
        }

        // Update password
        if (!$this->branchUserModel->updatePassword($branchUserId, $newPassword)) {
            return redirect()->back()->with('error', 'Failed to update password');
        }

        return redirect()->back()->with('success', 'Password changed successfully');
    }

    /**
     * View all listing requests for this branch
     */
    public function listingRequests()
    {
        $authCheck = $this->checkAuth();
        if ($authCheck) return $authCheck;

        $branchUserId = session()->get('branch_user_id');

        // Get all requests for this branch
        $requests = $this->listingRequestModel->getRequestsByBranchUser($branchUserId);

        // Get statistics
        $stats = $this->listingRequestModel->getBranchUserStats($branchUserId);

        $data = [
            'page_title' => 'Listing Requests',
            'requests' => $requests,
            'stats' => $stats
        ];

        return view('branch/listing_requests', $data);
    }

    /**
     * Show form to create new listing request
     */
    public function newListingRequest()
    {
        $authCheck = $this->checkAuth();
        if ($authCheck) return $authCheck;

        $branchUserId = session()->get('branch_user_id');
        $merchantId = session()->get('merchant_id');

        // Check if branch can submit more requests
        $canSubmit = $this->listingRequestModel->canSubmitRequest($branchUserId);

        if (!$canSubmit['can_submit']) {
            return redirect()->to('branch/listing-requests')->with('error', $canSubmit['message']);
        }

        // Get merchant's plan category limit
        $planLimitModel = new \App\Models\PlanLimitationModel();
        $maxCategories = $planLimitModel->getMerchantLimit($merchantId, 'max_categories');

        // Get categories for dropdown (alphabetically sorted)
        $categoryModel = new \App\Models\ServiceCategoryModel();
        $categories = $categoryModel->orderBy('name', 'ASC')->findAll();

        // Get active currencies for dropdown
        $currencyModel = new \App\Models\CurrencyModel();
        $currencies = $currencyModel->where('is_active', 1)->orderBy('priority', 'DESC')->orderBy('currency_name', 'ASC')->findAll();

        // Get merchant's default currency
        $merchantModel = new \App\Models\MerchantModel();
        $merchant = $merchantModel->find($merchantId);

        $data = [
            'page_title' => 'Request New Listing',
            'categories' => $categories,
            'maxCategories' => $maxCategories,
            'currencies' => $currencies,
            'merchant' => $merchant
        ];

        return view('branch/listing_request_form', $data);
    }

    /**
     * Process new listing request submission
     */
    public function submitListingRequest()
    {
        $authCheck = $this->checkAuth();
        if ($authCheck) return $authCheck;

        $branchUserId = session()->get('branch_user_id');
        $locationId = session()->get('location_id');
        $merchantId = session()->get('merchant_id');

        // Check if branch can submit more requests
        $canSubmit = $this->listingRequestModel->canSubmitRequest($branchUserId);

        if (!$canSubmit['can_submit']) {
            return redirect()->back()->with('error', $canSubmit['message']);
        }

        // Validate input
        $validation = $this->validate([
            'title' => 'required|min_length[3]|max_length[255]',
            'description' => 'permit_empty|max_length[5000]',
            'suggested_price' => 'permit_empty|decimal|greater_than_equal_to[0]',
            'currency_code' => 'permit_empty|exact_length[3]|alpha',
            'unit' => 'permit_empty|max_length[50]',
            'justification' => 'required|min_length[10]|max_length[1000]',
            'main_image' => 'permit_empty|uploaded[main_image]|max_size[main_image,2048]|is_image[main_image]|mime_in[main_image,image/jpg,image/jpeg,image/png,image/webp]',
            'gallery_images' => 'permit_empty|max_size[gallery_images,2048]|is_image[gallery_images]|mime_in[gallery_images,image/jpg,image/jpeg,image/png,image/webp]'
        ]);

        if (!$validation) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Get selected categories
        $categories = $this->request->getPost('categories');

        // Check category limit based on merchant's plan
        if (!empty($categories)) {
            $planLimitModel = new \App\Models\PlanLimitationModel();
            $maxCategories = $planLimitModel->getMerchantLimit($merchantId, 'max_categories');

            // -1 means unlimited
            if ($maxCategories !== -1 && count($categories) > $maxCategories) {
                return redirect()->back()->withInput()->with('error', "You can only select up to {$maxCategories} categories based on your merchant's subscription plan.");
            }
        }

        $categoriesJson = !empty($categories) ? json_encode($categories) : null;

        // Handle main image upload
        $mainImageName = null;
        $mainImage = $this->request->getFile('main_image');
        if ($mainImage && $mainImage->isValid() && !$mainImage->hasMoved()) {
            $mainImageName = $mainImage->getRandomName();
            $mainImage->move(FCPATH . 'uploads/listing-requests', $mainImageName);
        }

        // Handle gallery images upload
        $galleryImagesArray = [];
        $galleryImages = $this->request->getFileMultiple('gallery_images');
        if ($galleryImages) {
            foreach ($galleryImages as $image) {
                if ($image->isValid() && !$image->hasMoved()) {
                    $imageName = $image->getRandomName();
                    $image->move(FCPATH . 'uploads/listing-requests', $imageName);
                    $galleryImagesArray[] = $imageName;
                }
            }
        }
        $galleryImagesJson = !empty($galleryImagesArray) ? json_encode($galleryImagesArray) : null;

        // Create request
        $requestData = [
            'branch_user_id' => $branchUserId,
            'location_id' => $locationId,
            'merchant_id' => $merchantId,
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'suggested_price' => $this->request->getPost('suggested_price'),
            'currency_code' => $this->request->getPost('currency_code') ?: 'ZAR',
            'unit' => $this->request->getPost('unit'),
            'suggested_categories' => $categoriesJson,
            'main_image' => $mainImageName,
            'gallery_images' => $galleryImagesJson,
            'justification' => $this->request->getPost('justification'),
            'status' => 'pending'
        ];

        $requestId = $this->listingRequestModel->insert($requestData);

        if (!$requestId) {
            return redirect()->back()->withInput()->with('error', 'Failed to submit request: ' . implode(', ', $this->listingRequestModel->errors()));
        }

        // Send email notification to merchant
        try {
            $merchantModel = new \App\Models\MerchantModel();
            $branchUserModel = new \App\Models\BranchUserModel();
            $locationModel = new \App\Models\MerchantLocationModel();

            $merchant = $merchantModel->find($merchantId);
            $branchUser = $branchUserModel->find($branchUserId);
            $location = $locationModel->find($locationId);

            if ($merchant && $branchUser) {
                $emailService = new \App\Helpers\EmailService();

                // Prepare request data with ID
                $requestData['id'] = $requestId;

                // Prepare branch data
                $branchData = [
                    'name' => $branchUser['full_name'] ?? $branchUser['email'],
                    'location_name' => $location['location_name'] ?? 'Branch Location'
                ];

                $emailService->sendNewListingRequestToMerchant($requestData, $merchant, $branchData);
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to send listing request email: ' . $e->getMessage());
            // Don't fail the request if email fails, just log it
        }

        return redirect()->to('branch/listing-requests')->with('success', 'Listing request submitted successfully! The merchant will review it soon.');
    }

    /**
     * View single listing request details
     */
    public function viewListingRequest($requestId)
    {
        $authCheck = $this->checkAuth();
        if ($authCheck) return $authCheck;

        $branchUserId = session()->get('branch_user_id');

        // Get request with details
        $request = $this->listingRequestModel->getRequestWithDetails($requestId);

        if (!$request || $request['branch_user_id'] != $branchUserId) {
            return redirect()->to('branch/listing-requests')->with('error', 'Request not found or access denied');
        }

        $data = [
            'page_title' => 'Request Details',
            'request' => $request
        ];

        return view('branch/listing_request_view', $data);
    }

    /**
     * Accept an order (branch user)
     */
    public function acceptOrder($orderId)
    {
        $authCheck = $this->checkAuth();
        if ($authCheck) return $authCheck;

        $locationId = session()->get('location_id');
        $branchUserId = session()->get('branch_user_id');

        // Verify order belongs to this location
        $order = $this->masterOrderModel->where('id', $orderId)
                                       ->where('location_id', $locationId)
                                       ->first();

        if (!$order) {
            return redirect()->to('branch/orders')->with('error', 'Order not found or access denied.');
        }

        // Check if order is still pending
        if ($order['order_status'] !== 'pending') {
            return redirect()->to('branch/orders')->with('error', 'This order has already been processed.');
        }

        // Update order status
        $this->masterOrderModel->update($orderId, ['order_status' => 'accepted']);

        // Send notifications
        $notificationService = new \App\Services\NotificationService();
        $orderData = [
            'id' => $orderId,
            'booking_reference' => $order['booking_reference'],
            'grand_total' => $order['grand_total'],
            'created_at' => $order['created_at']
        ];

        // Get branch user details for notification
        $branchUserModel = new \App\Models\BranchUserModel();
        $branchUser = $branchUserModel->getBranchUserWithDetails($branchUserId);

        // Notify driver about acceptance
        $this->notificationModel->createNotification([
            'recipient_type' => 'driver',
            'recipient_id' => $order['driver_id'],
            'notification_type' => 'booking_accepted',
            'title' => 'Order Accepted!',
            'message' => "Great news! {$branchUser['business_name']} ({$branchUser['location_name']}) has accepted your order #{$order['booking_reference']}.",
            'related_order_id' => $orderId,
            'action_url' => site_url("order/receipt/{$orderId}"),
            'priority' => 'high'
        ]);

        log_message('info', "Branch user {$branchUserId} accepted order {$orderId}");

        return redirect()->to('branch/orders')->with('success', 'Order accepted successfully!');
    }

    /**
     * Reject an order (branch user)
     */
    public function rejectOrder($orderId)
    {
        $authCheck = $this->checkAuth();
        if ($authCheck) return $authCheck;

        $locationId = session()->get('location_id');
        $branchUserId = session()->get('branch_user_id');

        // Verify order belongs to this location
        $order = $this->masterOrderModel->where('id', $orderId)
                                       ->where('location_id', $locationId)
                                       ->first();

        if (!$order) {
            return redirect()->to('branch/orders')->with('error', 'Order not found or access denied.');
        }

        // Check if order is still pending
        if ($order['order_status'] !== 'pending') {
            return redirect()->to('branch/orders')->with('error', 'This order has already been processed.');
        }

        $rejectionReason = $this->request->getPost('rejection_reason') ?? 'No reason provided';

        // Update order status
        $this->masterOrderModel->update($orderId, [
            'order_status' => 'rejected',
            'rejection_reason' => $rejectionReason
        ]);

        // Send notifications
        $notificationService = new \App\Services\NotificationService();

        // Get branch user details for notification
        $branchUserModel = new \App\Models\BranchUserModel();
        $branchUser = $branchUserModel->getBranchUserWithDetails($branchUserId);

        // Notify driver about rejection
        $this->notificationModel->createNotification([
            'recipient_type' => 'driver',
            'recipient_id' => $order['driver_id'],
            'notification_type' => 'booking_rejected',
            'title' => 'Order Update',
            'message' => "Unfortunately, {$branchUser['business_name']} ({$branchUser['location_name']}) cannot fulfill your order #{$order['booking_reference']} at this time. Reason: {$rejectionReason}",
            'related_order_id' => $orderId,
            'action_url' => site_url("order/receipt/{$orderId}"),
            'priority' => 'normal'
        ]);

        log_message('info', "Branch user {$branchUserId} rejected order {$orderId}. Reason: {$rejectionReason}");

        return redirect()->to('branch/orders')->with('success', 'Order rejected successfully.');
    }
}

