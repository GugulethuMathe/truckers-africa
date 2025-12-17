<?php

namespace App\Controllers;

use App\Models\MerchantModel;
use App\Models\SubscriptionModel;
use App\Models\UserModel;
use CodeIgniter\Controller;

class MerchantDashboard extends Controller
{
    /**
     * Displays the merchant's main dashboard.
     * This method checks the merchant's verification and profile status
     * to show the correct content or redirects.
     */
    public function index()
    {

        // Get the logged-in merchant's ID from the session
        $merchantId = session()->get('user_id');

        // If no user is logged in, redirect to the login page.
        if (!$merchantId) {
            return redirect()->to('/login')->with('error', 'You must be logged in to view this page.');
        }

        $merchantModel = new MerchantModel();
        $subscriptionModel = new SubscriptionModel();
        $documentModel = new \App\Models\MerchantDocumentModel();
        $verificationModel = new \App\Models\VerificationRequirementModel();
        $listingRequestModel = new \App\Models\ListingRequestModel();
        $merchant = $merchantModel->find($merchantId);

        // If merchant data doesn't exist, log them out for safety.
        if (!$merchant) {
            session()->destroy();
            return redirect()->to('/login')->with('error', 'Could not find your account data. Please log in again.');
        }

        // Check if merchant needs to complete onboarding (admin-created merchants)
        if ($merchant['onboarding_completed'] == 0) {
            return redirect()->to('merchant/onboarding')->with('message', 'Please complete your profile setup to continue.');
        }

        // 1. Check if the merchant is still pending approval.
        // if ($merchant['verification_status'] === 'pending') {
        //     return redirect()->to('approval/pending');
        // }

        // 2. Fetch subscription details
        $subscription = $subscriptionModel->findByMerchantId($merchantId);

        // If no subscription exists, create a default one or set to null with defaults
        if (!$subscription) {
            $subscription = [
                'status' => 'inactive',
                'plan_name' => 'No Plan',
                'trial_days' => 0,
                'price' => 0
            ];
        }

        // 3. Check if the profile is incomplete (e.g., no physical address)
        $profileIncomplete = empty($merchant['physical_address']) || empty($merchant['business_contact_number']);

        // 4. Get verification progress
        $businessType = $merchant['business_type'] ?? 'individual';
        $verificationProgress = $documentModel->getVerificationProgress($merchantId, $businessType);
        $requiredDocuments = $verificationModel->getRequiredDocuments($businessType);

        $showApprovalNotification = ($merchant['verification_status'] === 'approved' && !$merchant['approval_notification_seen']);

        if ($showApprovalNotification) {
            $merchantModel->update($merchantId, ['approval_notification_seen' => true]);
        }

        // Get pending listing requests count
        $pendingRequestsCount = $listingRequestModel->getPendingCountByMerchant($merchantId);

        $data = [
            'merchant' => $merchant,
            'subscription' => $subscription,
            'profileIncomplete' => $profileIncomplete,
            'verificationProgress' => $verificationProgress,
            'requiredDocuments' => $requiredDocuments,
            'businessType' => $businessType,
            'showApprovalNotification' => $showApprovalNotification,
            'pendingRequestsCount' => $pendingRequestsCount,
            'page_title' => 'Merchant Dashboard'
        ];

        return view('merchant/dashboard', $data);
    }

    public function orders($status = 'all')
    {
        $orderModel  = new \App\Models\OrderModel();
        $merchantId  = session()->get('user_id');

        // Ensure user is logged in
        if (!$merchantId) {
            return redirect()->to('/login')->with('error', 'You must be logged in to view this page.');
        }

        // Map friendly URL segments to actual DB statuses
        $statusMap = [
            'approved' => 'accepted', // "Approved" orders are stored as "accepted" in DB
        ];
        $dbStatus  = $statusMap[$status] ?? $status; // Fall back to same value if not mapped

        // Show only orders from PRIMARY location to main merchant
        // Orders from non-primary locations with branch users are handled by branch users
        $orders = $orderModel
            ->select('master_orders.*, COALESCE(NULLIF(TRIM(CONCAT(COALESCE(truck_drivers.name, ""), " ", COALESCE(truck_drivers.surname, ""))), ""), truck_drivers.email, "Unknown Driver") as driver_name, COUNT(order_items.id) as item_count, GROUP_CONCAT(DISTINCT merchant_listings.title SEPARATOR ", ") as listing_title', false)
            ->join('truck_drivers', 'truck_drivers.id = master_orders.driver_id', 'left')
            ->join('order_items', 'order_items.master_order_id = master_orders.id', 'left')
            ->join('merchant_listings', 'merchant_listings.id = order_items.listing_id', 'left')
            ->join('merchant_locations', 'merchant_locations.id = merchant_listings.location_id', 'left')
            ->where('merchant_listings.merchant_id', $merchantId)
            ->where('(merchant_locations.is_primary = 1 OR merchant_locations.id NOT IN (SELECT location_id FROM branch_users WHERE is_active = 1))', null, false)
            ->groupBy('master_orders.id')
            ->when($dbStatus !== 'all', static function($builder) use ($dbStatus) {
                return $builder->where('master_orders.order_status', $dbStatus);
            })
            ->orderBy('master_orders.created_at', 'DESC')
            ->paginate(10, 'orders');

        $pager = $orderModel->pager;

        $data = [
            'page_title'  => ucfirst($status) . ' Orders',
            'orders'      => $orders,
            'pager'       => $pager,
            'total'       => $pager->getTotal('orders'),
            'perPage'     => $pager->getPerPage('orders'),
            'currentPage' => $pager->getCurrentPage('orders'),
        ];

        return view('merchant/orders', $data);
    }

    /**
     * Displays the "Your profile is pending approval" page.
     */
    public function pending()
    {

        $data = [
            'page_title' => 'Pending Approval'
        ];
        return view('merchant/pending_approval', $data);
    }

    public function viewOrder($orderId)
    {
        $merchantId = session()->get('user_id');
        if (!$merchantId) {
            return redirect()->to('/login')->with('error', 'You must be logged in to view this page.');
        }

        $orderModel = new \App\Models\OrderModel();

        // First verify the order belongs to this merchant's primary location or locations without branch users
        $db = \Config\Database::connect();
        $orderCheck = $db->table('master_orders')
            ->select('master_orders.id')
            ->join('order_items', 'order_items.master_order_id = master_orders.id')
            ->join('merchant_listings', 'merchant_listings.id = order_items.listing_id')
            ->join('merchant_locations', 'merchant_locations.id = merchant_listings.location_id', 'left')
            ->where('master_orders.id', $orderId)
            ->where('merchant_listings.merchant_id', $merchantId)
            ->where('(merchant_locations.is_primary = 1 OR merchant_locations.id NOT IN (SELECT location_id FROM branch_users WHERE is_active = 1))', null, false)
            ->get()
            ->getRowArray();

        if (!$orderCheck) {
            return redirect()->to('merchant/orders/all')->with('error', 'Order not found or belongs to a branch location.');
        }

        $order = $orderModel->getOrderWithItems($orderId, $merchantId);

        if (!$order) {
            // Fallback: fetch order without merchant constraint to allow viewing other status if needed
            $order = (new \App\Models\OrderModel())->select('master_orders.*, CONCAT(truck_drivers.name, " ", truck_drivers.surname) as driver_name')
                    ->join('truck_drivers', 'truck_drivers.id = master_orders.driver_id')
                    ->find($orderId);
            if (!$order) {
                return redirect()->to('merchant/orders/all')->with('error', 'Order not found.');
            }
            // Also fetch all items for this order for display
            $order['items'] = (new \App\Models\OrderItemModel())
                ->select('order_items.*, merchant_listings.title as listing_title, merchant_listings.currency_code')
                ->join('merchant_listings', 'merchant_listings.id = order_items.listing_id')
                ->where('order_items.master_order_id', $orderId)
                ->findAll();
        }

        $data = [
            'page_title' => 'Order #' . $orderId,
            'order'      => $order,
            'driver'     => (new \App\Models\TruckDriverModel())->asArray()->find($order['driver_id'])
        ];

        return view('merchant/order_view', $data);
    }

    public function viewDriverProfile($driverId)
    {

        $truckDriverModel = new \App\Models\TruckDriverModel();

        $driver = $truckDriverModel->asArray()->find($driverId);

        // Security check: ensure the driver exists
        if (!$driver) {
            return redirect()->back()->with('error', 'The requested driver profile could not be found.');
        }

        $data = [
            'page_title' => 'Driver Profile',
            'driver' => $driver
        ];

        return view('merchant/driver_profile', $data);
    }

    /**
     * Display document verification page
     */
    public function verification()
    {
        $merchantId = session()->get('user_id');
        if (!$merchantId) {
            return redirect()->to('/login')->with('error', 'You must be logged in to view this page.');
        }

        $merchantModel = new MerchantModel();
        $documentModel = new \App\Models\MerchantDocumentModel();
        $verificationModel = new \App\Models\VerificationRequirementModel();

        $merchant = $merchantModel->find($merchantId);
        if (!$merchant) {
            return redirect()->to('/login')->with('error', 'Could not find your account data.');
        }

        // Get verification progress and documents
        $businessType = $merchant['business_type'] ?? 'individual';
        $verificationProgress = $documentModel->getVerificationProgress($merchantId, $businessType);
        $requiredDocuments = $verificationModel->getRequiredDocuments($businessType);
        $uploadedDocuments = $documentModel->getDocumentsByMerchant($merchantId);

        $data = [
            'merchant' => $merchant,
            'verificationProgress' => $verificationProgress,
            'requiredDocuments' => $requiredDocuments,
            'uploadedDocuments' => $uploadedDocuments,
            'businessType' => $businessType,
            'page_title' => 'Document Verification'
        ];

        return view('merchant/verification', $data);
    }

    /**
     * Handle document upload
     */
    public function uploadDocument()
    {
        // Check if user is logged in
        $merchantId = session()->get('user_id');
        if (!$merchantId) {
            return redirect()->to('/login')->with('error', 'You must be logged in to upload documents.');
        }

        // Validate request method
        if (!$this->request->is('post')) {
            return redirect()->to('merchant/verification')->with('error', 'Invalid request method.');
        }

        $documentType = $this->request->getPost('document_type');
        $file = $this->request->getFile('document_file');

        // Validate inputs
        if (empty($documentType)) {
            return redirect()->to('merchant/verification')->with('error', 'Document type is required.');
        }

        if (!$file || !$file->isValid() || $file->getError() !== UPLOAD_ERR_OK) {
            $errorMessage = 'Please select a valid file to upload.';
            if ($file && $file->getError() !== UPLOAD_ERR_OK) {
                switch ($file->getError()) {
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        $errorMessage = 'File is too large. Maximum size is 5MB.';
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $errorMessage = 'File upload was interrupted. Please try again.';
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $errorMessage = 'No file was selected for upload.';
                        break;
                    default:
                        $errorMessage = 'File upload failed. Please try again.';
                }
            }
            return redirect()->to('merchant/verification')->with('error', $errorMessage);
        }

        // Validate document type
        $validTypes = ['owner_id', 'company_registration', 'proof_of_residence', 'business_image'];
        if (!in_array($documentType, $validTypes)) {
            return redirect()->to('merchant/verification')->with('error', 'Invalid document type.');
        }

        // Validate file
        $validation = \App\Models\MerchantDocumentModel::validateFile($file, $documentType);
        if (!$validation['valid']) {
            return redirect()->to('merchant/verification')->with('error', $validation['error']);
        }

        try {
            $documentModel = new \App\Models\MerchantDocumentModel();

            // Get file information BEFORE moving the file to avoid finfo_file errors
            $originalFileName = $file->getName();
            $fileSize = $file->getSize();
            $clientExtension = $file->getClientExtension();

            // Get MIME type safely
            $mimeType = 'application/octet-stream'; // Default fallback
            try {
                $mimeType = $file->getMimeType();
            } catch (\Exception $mimeException) {
                // Fallback to guessing MIME type from extension
                $mimeTypes = [
                    'pdf' => 'application/pdf',
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'png' => 'image/png'
                ];
                $extension = strtolower($clientExtension);
                if (isset($mimeTypes[$extension])) {
                    $mimeType = $mimeTypes[$extension];
                }
                log_message('info', 'MIME type detection failed, using fallback for extension: ' . $extension);
            }

            // Check if document already exists and delete old one
            $existingDoc = $documentModel->getDocumentByType($merchantId, $documentType);
            if ($existingDoc) {
                $documentModel->deleteDocument($existingDoc['id']);
            }

            // Generate file path and name
            $uploadPath = \App\Models\MerchantDocumentModel::getUploadPath($documentType);
            $fileName = \App\Models\MerchantDocumentModel::generateFileName($merchantId, $documentType, $originalFileName);

            // Create upload directory if it doesn't exist
            $fullUploadPath = FCPATH . $uploadPath;
            if (!is_dir($fullUploadPath)) {
                if (!mkdir($fullUploadPath, 0755, true)) {
                    log_message('error', 'Failed to create upload directory: ' . $fullUploadPath);
                    return redirect()->to('merchant/verification')->with('error', 'Failed to create upload directory. Please contact support.');
                }
            }

            // Check if directory is writable
            if (!is_writable($fullUploadPath)) {
                log_message('error', 'Upload directory is not writable: ' . $fullUploadPath);
                return redirect()->to('merchant/verification')->with('error', 'Upload directory is not writable. Please contact support.');
            }

            // Move uploaded file
            if ($file->move($fullUploadPath, $fileName)) {
                // Verify file was actually moved
                $finalPath = $fullUploadPath . $fileName;
                if (!file_exists($finalPath)) {
                    log_message('error', 'File move succeeded but file not found at: ' . $finalPath);
                    return redirect()->to('merchant/verification')->with('error', 'File upload verification failed. Please try again.');
                }

                // Save document record using the pre-captured file information
                $documentData = [
                    'merchant_id' => $merchantId,
                    'document_type' => $documentType,
                    'file_name' => $originalFileName,
                    'file_path' => $uploadPath . $fileName,
                    'file_size' => $fileSize,
                    'mime_type' => $mimeType,
                    'is_verified' => 'pending'
                ];

                $documentId = $documentModel->insert($documentData);
                if (!$documentId) {
                    log_message('error', 'Failed to insert document record for merchant: ' . $merchantId);
                    // Clean up uploaded file
                    if (file_exists($finalPath)) {
                        unlink($finalPath);
                    }
                    return redirect()->to('merchant/verification')->with('error', 'Failed to save document record. Please try again.');
                }

                // Mark verification as submitted if all required documents are uploaded
                $merchantModel = new MerchantModel();
                $merchant = $merchantModel->find($merchantId);
                $businessType = $merchant['business_type'] ?? 'individual';

                if ($documentModel->hasAllRequiredDocuments($merchantId, $businessType)) {
                    $merchantModel->markVerificationSubmitted($merchantId);
                }

                // Notify admins about the document upload
                try {
                    $notifier = new \App\Services\NotificationService();
                    $notifier->notifyAdminsDocumentUploaded((int) $documentId, (array) $merchant, $documentType);
                } catch (\Throwable $t) {
                    log_message('error', 'Failed to send document upload notification: ' . $t->getMessage());
                }

                log_message('info', 'Document uploaded successfully for merchant ' . $merchantId . ': ' . $documentType);
                return redirect()->to('merchant/verification')->with('success', 'Document uploaded successfully! It will be reviewed by our team.');
            } else {
                $error = $file->getErrorString();
                log_message('error', 'File move failed for merchant ' . $merchantId . ': ' . $error);
                return redirect()->to('merchant/verification')->with('error', 'Failed to upload file: ' . $error);
            }
        } catch (\Exception $e) {
            log_message('error', 'Document upload error for merchant ' . $merchantId . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return redirect()->to('merchant/verification')->with('error', 'An error occurred while uploading the document. Please try again.');
        }
    }

    /**
     * Display help and support page
     */
    public function help()
    {
        $merchantId = session()->get('user_id');
        if (!$merchantId) {
            return redirect()->to('/login')->with('error', 'You must be logged in to view this page.');
        }

        $data = [
            'page_title' => 'Help & Support'
        ];

        return view('merchant/help', $data);
    }
}