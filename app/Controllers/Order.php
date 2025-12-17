<?php

namespace App\Controllers;

use App\Models\OrderModel;
use App\Models\MerchantListingModel;
use App\Models\MasterOrderModel;
use App\Models\OrderItemModel;
use App\Models\OrderServiceModel;
use App\Models\TruckDriverModel;
use App\Models\MerchantModel;
use App\Models\MerchantLocationModel;
use App\Models\BranchUserModel;
use App\Services\NotificationService;
use App\Helpers\EmailService;

class Order extends BaseController
{
    public function placeOrder()
    {
        // 1. Check if user is a logged-in driver
        if ((!session()->get('is_logged_in') && !session()->get('isLoggedIn')) || session()->get('user_type') !== 'driver') {
            return redirect()->to('/login')->with('error', 'You must be logged in as a driver to place an order.');
        }

        // 2. Get data from POST request
        $request = \Config\Services::request();
        $listingId = $request->getPost('listing_id');
        $driverId = session()->get('user_id');

        // 3. Get merchant_id from the listing
        $listingModel = new MerchantListingModel();
        $listing = $listingModel->asArray()->find($listingId);

        if (!$listing || !is_array($listing)) {
            return redirect()->back()->with('error', 'The requested listing does not exist.');
        }

        $merchantId = $listing['merchant_id'];

        // 4. Save the order
        $orderModel = new OrderModel();
        $data = [
            'listing_id'  => $listingId,
            'driver_id'   => $driverId,
            'merchant_id' => $merchantId,
            'status'      => 'pending'
        ];

        if ($orderModel->save($data)) {
            return redirect()->back()->with('success', 'Your order has been placed successfully and is pending merchant approval.');
        } else {
            return redirect()->back()->with('error', 'There was a problem placing your order. Please try again.');
        }
    }

    public function acceptOrder($orderId)
    {
        return $this->updateOrderStatus($orderId, 'accepted', 'Order has been accepted.');
    }

    public function rejectOrder($orderId)
    {
        $rejectionReason = $_POST['reason'] ?? null;
        return $this->updateOrderStatus($orderId, 'rejected', 'Order has been rejected.', $rejectionReason);
    }

    private function updateOrderStatus($orderId, $status, $message, $rejectionReason = null)
    {
        // Debug session data
        log_message('debug', 'Session data: ' . json_encode(session()->get()));
        
        // Check if user is logged in
        if (!session()->get('is_logged_in') && !session()->get('isLoggedIn')) {
            return redirect()->to('login')->with('error', 'Please log in to continue.');
        }

        // Check if user is a merchant (more flexible check)
        $userType = session()->get('user_type');
        if ($userType !== 'merchant') {
            // Try to determine if this is a merchant by checking the referrer URL
            $referrer = $_SERVER['HTTP_REFERER'] ?? '';
            if (strpos($referrer, '/merchant/') === false) {
                return redirect()->to('login')->with('error', 'Access denied. Merchant login required.');
            }
            // If coming from merchant area, assume they're a merchant but session is corrupted
            log_message('warning', 'Merchant session corrupted, user_type: ' . $userType);
        }

        $merchantId = session()->get('user_id');
        $masterOrderModel = new MasterOrderModel();
        $orderItemModel = new OrderItemModel();
        $notificationService = new NotificationService();
        $emailService = new EmailService();
        $driverModel = new TruckDriverModel();
        $merchantModel = new MerchantModel();
        $locationModel = new MerchantLocationModel();

        // Get the master order
        $order = $masterOrderModel->find($orderId);
        if (!$order) {
            return redirect()->to('merchant/orders/all')->with('error', 'Order not found.');
        }

        // Check if this merchant has items in this order
        $merchantItems = $orderItemModel->where('master_order_id', $orderId)
                                       ->join('merchant_listings', 'merchant_listings.id = order_items.listing_id')
                                       ->where('merchant_listings.merchant_id', $merchantId)
                                       ->findAll();

        if (empty($merchantItems)) {
            return redirect()->to('merchant/orders/all')->with('error', 'You do not have items in this order.');
        }

        // Check if order is still pending
        if ($order['order_status'] !== 'pending') {
            return redirect()->to('merchant/orders/all')->with('error', 'This order has already been processed.');
        }

        // Update the master order status
        $masterOrderModel->update($orderId, ['order_status' => $status]);

        // Send notifications
        $orderData = [
            'id' => $orderId,
            'booking_reference' => $order['booking_reference'],
            'grand_total' => $order['grand_total'],
            'created_at' => $order['created_at']
        ];

        if ($status === 'accepted') {
            $notificationService->notifyOrderAccepted($orderId, $merchantId, $order['driver_id'], $orderData);
        } else {
            $notificationService->notifyOrderRejected($orderId, $merchantId, $order['driver_id'], $orderData, $rejectionReason);
        }

        // Send email notification to driver
        try {
            $driver = $driverModel->find($order['driver_id']);
            $merchant = $merchantModel->find($merchantId);

            // Get location from first order item
            $firstItem = $merchantItems[0] ?? null;
            $location = null;
            if ($firstItem && isset($firstItem['location_id'])) {
                $location = $locationModel->find($firstItem['location_id']);
            }

            if ($driver && isset($driver['email'])) {
                $emailOrderData = [
                    'booking_reference' => $order['booking_reference'],
                    'merchant_name' => $merchant['business_name'] ?? 'Merchant',
                    'location_name' => $location['location_name'] ?? 'Location',
                    'total_amount' => $order['grand_total'],
                    'currency' => 'ZAR'
                ];

                $driverData = [
                    'email' => $driver['email'],
                    'first_name' => $driver['first_name'],
                    'last_name' => $driver['last_name']
                ];

                $emailService->sendOrderStatusUpdateToDriver($emailOrderData, $driverData, $status);

                // Send SMS notification
                if (!empty($driver['phone_number'])) {
                    $this->sendOrderStatusSMS($driver['phone_number'], $order['booking_reference'], $status, $location['location_name'] ?? 'Location');
                }
            }
        } catch (\Exception $emailError) {
            // Log email error but don't fail the status update
            log_message('error', 'Failed to send order status email: ' . $emailError->getMessage());
        }

        return redirect()->to('merchant/orders/all')->with('success', $message . ' Driver has been notified via email and SMS.');
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
     * Display order checkout/completion page
     */
    public function checkout()
    {
        // Check if user is logged in as driver
        if (!session()->get('is_logged_in') || session()->get('user_type') !== 'driver') {
            return redirect()->to('login')->with('error', 'Please log in as a driver to complete your order.');
        }

        $driverId = session()->get('user_id');
        $driverModel = new TruckDriverModel();
        $driver = $driverModel->find($driverId);

        $data = [
            'page_title' => 'Complete Order',
            'driver' => $driver
        ];

        return view('driver/order_checkout', $data);
    }

    /**
     * Process order completion - Creates separate master orders for each merchant
     */
    public function completeOrder()
    {
        $request = \Config\Services::request();
        
        // Check if user is logged in as driver
        if (!session()->get('is_logged_in') || session()->get('user_type') !== 'driver') {
            return redirect()->to('login')->with('error', 'Please log in as a driver.');
        }

        $driverId = session()->get('user_id');
        
        // Get cart data from localStorage (will be sent via AJAX)
        $cartData = $request->getJSON(true);
        $estimatedArrivals = $cartData['estimated_arrivals'] ?? []; // Per-order arrival times
        $vehicleInfo = $cartData['vehicle_info'] ?? null;

        if (empty($cartData) || !isset($cartData['items'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cart is empty or invalid data received.'
            ]);
        }

        // Validate estimated arrivals are provided
        if (empty($estimatedArrivals) || !is_array($estimatedArrivals)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Estimated arrival times are required for all orders.'
            ]);
        }

        // Group cart items by location_id (to separate orders by branch/location)
        // Even if branches belong to the same merchant, they should have separate orders
        $itemsByLocation = [];
        foreach ($cartData['items'] as $item) {
            $locationId = $item['location_id'] ?? null;
            $merchantId = $item['merchant_id'] ?? 1;

            // Create a unique key combining merchant_id and location_id
            // This ensures branches of the same merchant are treated as separate orders
            $locationKey = $merchantId . '_' . ($locationId ?? 'no_location');

            if (!isset($itemsByLocation[$locationKey])) {
                $itemsByLocation[$locationKey] = [
                    'merchant_id' => $merchantId,
                    'location_id' => $locationId,
                    'location_name' => $item['location_name'] ?? '',
                    'items' => []
                ];
            }
            $itemsByLocation[$locationKey]['items'][] = $item;
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $masterOrderModel = new MasterOrderModel();
            $orderItemModel = new OrderItemModel();
            $notificationService = new NotificationService();
            $emailService = new EmailService();
            $driverModel = new TruckDriverModel();
            $merchantModel = new MerchantModel();
            $locationModel = new MerchantLocationModel();
            $branchUserModel = new BranchUserModel();

            // Get driver information for emails
            $driver = $driverModel->find($driverId);

            $createdOrders = [];
            $baseBookingRef = 'TA' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $orderCounter = 1;

            // Create separate master order for each location (branch)
            foreach ($itemsByLocation as $locationKey => $locationData) {
                $merchantId = $locationData['merchant_id'];
                $locationId = $locationData['location_id'];
                $locationItems = $locationData['items'];

                // Calculate total for this location's items
                $locationTotal = 0;
                foreach ($locationItems as $item) {
                    $locationTotal += $item['price'] * $item['quantity'];
                }

                // Create unique booking reference for this location
                $bookingReference = $baseBookingRef . '-' . chr(64 + $orderCounter); // A, B, C, etc.

                // Get the estimated arrival for this specific location
                $locationEstimatedArrival = $estimatedArrivals[$locationKey] ?? null;

                // Validate this location has an estimated arrival time
                if (empty($locationEstimatedArrival)) {
                    throw new \Exception('Missing estimated arrival time for location: ' . ($locationData['location_name'] ?: $locationKey));
                }

                $masterOrderData = [
                    'driver_id' => $driverId,
                    'booking_reference' => $bookingReference,
                    'grand_total' => $locationTotal,
                    'order_status' => 'pending',
                    'estimated_arrival' => $locationEstimatedArrival,
                    'vehicle_model' => $vehicleInfo,
                    'terms_accepted' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $masterOrderId = $masterOrderModel->insert($masterOrderData);

                if (!$masterOrderId) {
                    $errors = $masterOrderModel->errors();
                    log_message('error', 'Failed to create master order for location ' . $locationKey . ': ' . json_encode($errors));
                    throw new \Exception('Failed to create order for location: ' . implode(', ', $errors));
                }

                // Create order items for this location
                foreach ($locationItems as $item) {
                    $orderItemData = [
                        'master_order_id' => $masterOrderId,
                        'listing_id' => $item['id'],
                        'merchant_id' => $merchantId,
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'total_cost' => $item['price'] * $item['quantity'],
                        'item_status' => 'pending',
                        'status' => 'pending'
                    ];
                    
                    $itemResult = $orderItemModel->insert($orderItemData);
                    
                    if (!$itemResult) {
                        $errors = $orderItemModel->errors();
                        log_message('error', 'Failed to create order item: ' . json_encode($errors));
                        throw new \Exception('Failed to create order item: ' . implode(', ', $errors));
                    }
                }
                
                // Store order info for notifications and response
                $createdOrders[] = [
                    'id' => $masterOrderId,
                    'booking_reference' => $bookingReference,
                    'merchant_id' => $merchantId,
                    'location_id' => $locationId,
                    'location_name' => $locationData['location_name'],
                    'total' => $locationTotal,
                    'items_count' => count($locationItems)
                ];

                // Send notification to this specific merchant/location
                $orderData = [
                    'id' => $masterOrderId,
                    'booking_reference' => $bookingReference,
                    'grand_total' => $locationTotal,
                    'created_at' => date('Y-m-d H:i:s')
                ];

                // Pass location_id so notification can be sent to branch user if exists
                $notificationService->notifyOrderPlaced($masterOrderId, $driverId, [$merchantId], $orderData, $locationId);

                // Send email notifications
                try {
                    // Get merchant and location details
                    $merchant = $merchantModel->find($merchantId);
                    $location = $locationModel->find($locationId);

                    // Prepare order data for emails
                    $emailOrderData = [
                        'booking_reference' => $bookingReference,
                        'merchant_name' => $merchant['business_name'] ?? 'Unknown Merchant',
                        'location_name' => $location['location_name'] ?? $locationData['location_name'],
                        'total_amount' => $locationTotal,
                        'currency' => 'ZAR', // Default currency, can be made dynamic
                        'created_at' => date('Y-m-d H:i:s'),
                        'items' => array_map(function($item) {
                            return [
                                'name' => $item['name'] ?? $item['title'] ?? 'Item',
                                'quantity' => $item['quantity']
                            ];
                        }, $locationItems)
                    ];

                    $driverData = [
                        'email' => $driver['email'],
                        'first_name' => $driver['first_name'],
                        'last_name' => $driver['last_name']
                    ];

                    // Send confirmation to driver
                    $emailService->sendOrderConfirmationToDriver($emailOrderData, $driverData);

                    // Send notification to merchant
                    if ($merchant && isset($merchant['email'])) {
                        $merchantData = [
                            'email' => $merchant['email'],
                            'business_name' => $merchant['business_name']
                        ];
                        $emailOrderData['driver_name'] = $driver['first_name'] . ' ' . $driver['last_name'];
                        $emailService->sendNewOrderToMerchant($emailOrderData, $merchantData);
                    }

                    // Send notification to branch manager if exists
                    $branchUser = $branchUserModel->where('location_id', $locationId)->first();
                    if ($branchUser && isset($branchUser['email'])) {
                        $branchData = [
                            'email' => $branchUser['email'],
                            'name' => $branchUser['name'] ?? 'Branch Manager'
                        ];
                        $emailService->sendNewOrderToBranch($emailOrderData, $branchData);
                    }
                } catch (\Exception $emailError) {
                    // Log email error but don't fail the order
                    log_message('error', 'Failed to send order emails: ' . $emailError->getMessage());
                }

                $orderCounter++;
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to create orders. Please try again.'
                ]);
            }

            // Calculate grand total across all orders
            $grandTotal = array_sum(array_column($createdOrders, 'total'));
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Orders completed successfully!',
                'orders_created' => count($createdOrders),
                'orders' => $createdOrders,
                'grand_total' => $grandTotal,
                'checkout_session_id' => $baseBookingRef // For grouping related orders
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Order completion failed: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * View order receipt
     */
    public function receipt($orderId)
    {
        // Check if user is logged in as driver
        if (!session()->get('is_logged_in') || session()->get('user_type') !== 'driver') {
            return redirect()->to('login')->with('error', 'Please log in to view your receipt.');
        }

        $driverId = session()->get('user_id');
        $masterOrderModel = new MasterOrderModel();
        $orderItemModel = new OrderItemModel();
        
        // Get order with security check
        $order = $masterOrderModel->where('id', $orderId)
                                 ->where('driver_id', $driverId)
                                 ->first();
        
        if (!$order) {
            return redirect()->to('dashboard/driver')->with('error', 'Order not found or access denied.');
        }

        // Get order items
        $orderItems = $orderItemModel->where('master_order_id', $orderId)->findAll();
        
        $data = [
            'page_title' => 'Order Receipt',
            'order' => $order,
            'order_items' => $orderItems
        ];

        return view('driver/order_receipt', $data);
    }

    /**
     * View multi-order receipt for orders from same checkout session
     */
    public function multiReceipt($checkoutSessionId)
    {
        // Check if user is logged in as driver
        if (!session()->get('is_logged_in') || session()->get('user_type') !== 'driver') {
            return redirect()->to('login')->with('error', 'Please log in to view your receipt.');
        }

        $driverId = session()->get('user_id');
        $masterOrderModel = new MasterOrderModel();
        $orderItemModel = new OrderItemModel();
        
        // Get all orders from this checkout session
        // Orders from same session have booking references like: TA20250804001-A, TA20250804001-B, etc.
        $orders = $masterOrderModel->where('driver_id', $driverId)
                                  ->like('booking_reference', $checkoutSessionId . '-', 'after')
                                  ->orderBy('booking_reference', 'ASC')
                                  ->findAll();
        
        if (empty($orders)) {
            return redirect()->to('dashboard/driver')->with('error', 'Orders not found or access denied.');
        }

        // Get order items for all orders, grouped by merchant
        $ordersWithItems = [];
        $grandTotal = 0;

        foreach ($orders as $order) {
            // Get items for this order with location information and currency
            $orderItems = $orderItemModel->select('order_items.*, merchant_listings.title as listing_title, merchant_listings.location_id, merchant_listings.currency_code, merchants.business_name, merchant_locations.location_name')
                                        ->join('merchant_listings', 'merchant_listings.id = order_items.listing_id')
                                        ->join('merchants', 'merchants.id = order_items.merchant_id')
                                        ->join('merchant_locations', 'merchant_locations.id = merchant_listings.location_id', 'left')
                                        ->where('master_order_id', $order['id'])
                                        ->findAll();

            // Determine display name: location name if available, otherwise merchant name
            $merchantName = !empty($orderItems) ? $orderItems[0]['business_name'] : 'Unknown Merchant';
            $locationName = !empty($orderItems) && !empty($orderItems[0]['location_name']) ? $orderItems[0]['location_name'] : null;
            $currencyCode = !empty($orderItems) && !empty($orderItems[0]['currency_code']) ? $orderItems[0]['currency_code'] : 'ZAR';

            // Use location name if it's a branch, otherwise use merchant name
            $displayName = $locationName ? $locationName : $merchantName;

            $ordersWithItems[] = [
                'order' => $order,
                'items' => $orderItems,
                'merchant_name' => $merchantName,
                'location_name' => $locationName,
                'display_name' => $displayName,
                'currency_code' => $currencyCode
            ];

            $grandTotal += $order['grand_total'];
        }
        
        $data = [
            'page_title' => 'Order Receipt',
            'orders' => $ordersWithItems,
            'checkout_session_id' => $checkoutSessionId,
            'grand_total' => $grandTotal,
            'order_date' => $orders[0]['created_at'] ?? date('Y-m-d H:i:s'),
            'estimated_arrival' => $orders[0]['estimated_arrival'] ?? null,
            'vehicle_model' => $orders[0]['vehicle_model'] ?? null
        ];

        return view('driver/multi_order_receipt', $data);
    }

    /**
     * View driver's order history - Groups orders by checkout session
     */
    public function myOrders()
    {
        // Check if user is logged in as driver
        if (!session()->get('is_logged_in') || session()->get('user_type') !== 'driver') {
            return redirect()->to('login')->with('error', 'Please log in to view your orders.');
        }

        $driverId = session()->get('user_id');
        $masterOrderModel = new MasterOrderModel();
        $orderItemModel = new OrderItemModel();
        
        // Get all orders with merchant information
        $allOrders = $masterOrderModel->select('master_orders.*, merchants.business_name')
                                     ->join('order_items', 'order_items.master_order_id = master_orders.id')
                                     ->join('merchants', 'merchants.id = order_items.merchant_id')
                                     ->where('master_orders.driver_id', $driverId)
                                     ->groupBy('master_orders.id')
                                     ->orderBy('master_orders.created_at', 'DESC')
                                     ->findAll();
        
        // Group orders by checkout session
        $groupedOrders = [];
        foreach ($allOrders as $order) {
            // Extract base booking reference (remove -A, -B, etc.)
            $bookingRef = $order['booking_reference'];
            $baseRef = preg_replace('/-[A-Z]$/', '', $bookingRef);
            
            if (!isset($groupedOrders[$baseRef])) {
                $groupedOrders[$baseRef] = [
                    'checkout_session_id' => $baseRef,
                    'orders' => [],
                    'total_amount' => 0,
                    'order_date' => $order['created_at'],
                    'estimated_arrival' => $order['estimated_arrival'],
                    'vehicle_model' => $order['vehicle_model'],
                    'overall_status' => 'pending' // Will be calculated
                ];
            }
            
            $groupedOrders[$baseRef]['orders'][] = $order;
            $groupedOrders[$baseRef]['total_amount'] += $order['grand_total'];
        }
        
        // Calculate overall status for each group
        foreach ($groupedOrders as &$group) {
            $statuses = array_column($group['orders'], 'order_status');
            
            if (in_array('rejected', $statuses)) {
                $group['overall_status'] = 'partially_rejected';
            } elseif (in_array('completed', $statuses)) {
                $allCompleted = count(array_filter($statuses, function($status) { return $status === 'completed'; })) === count($statuses);
                $group['overall_status'] = $allCompleted ? 'completed' : 'in_progress';
            } elseif (in_array('accepted', $statuses)) {
                $group['overall_status'] = 'in_progress';
            } else {
                $group['overall_status'] = 'pending';
            }
        }
        
        $data = [
            'page_title' => 'My Orders',
            'grouped_orders' => $groupedOrders
        ];

        return view('driver/my_orders', $data);
    }
}
