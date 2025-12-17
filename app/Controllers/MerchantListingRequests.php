<?php

namespace App\Controllers;

use App\Models\ListingRequestModel;
use App\Models\MerchantListingModel;
use App\Models\PlanLimitationModel;
use App\Models\CategoryModel;
use App\Models\ServiceCategoryModel;
use App\Models\MerchantListingServiceModel;
use CodeIgniter\Controller;

class MerchantListingRequests extends Controller
{
    protected $listingRequestModel;
    protected $listingModel;
    protected $planLimitModel;

    public function __construct()
    {
        $this->listingRequestModel = new ListingRequestModel();
        $this->listingModel = new MerchantListingModel();
        $this->planLimitModel = new PlanLimitationModel();
    }

    /**
     * Check if user is logged in as merchant
     */
    private function checkAuth()
    {
        $merchantId = session()->get('user_id');
        if (!$merchantId) {
            return redirect()->to('login')->with('error', 'Please login to continue');
        }
        return null;
    }

    /**
     * List all listing requests for this merchant
     */
    public function index()
    {
        $authCheck = $this->checkAuth();
        if ($authCheck) return $authCheck;

        $merchantId = session()->get('user_id');
        $status = $this->request->getGet('status') ?? null;

        // Get all requests for this merchant
        $requests = $this->listingRequestModel->getRequestsByMerchant($merchantId, $status);

        // Get statistics
        $stats = $this->listingRequestModel->getMerchantStats($merchantId);

        $data = [
            'page_title' => 'Listing Requests',
            'requests' => $requests,
            'stats' => $stats,
            'current_status' => $status
        ];

        return view('merchant/listing_requests/index', $data);
    }

    /**
     * View single listing request details
     */
    public function view($requestId)
    {
        $authCheck = $this->checkAuth();
        if ($authCheck) return $authCheck;

        $merchantId = session()->get('user_id');

        // Get request with full details
        $request = $this->listingRequestModel->getRequestWithDetails($requestId);

        if (!$request || $request['merchant_id'] != $merchantId) {
            return redirect()->to('merchant/listing-requests')->with('error', 'Request not found or access denied');
        }

        $data = [
            'page_title' => 'Request Details',
            'request' => $request
        ];

        return view('merchant/listing_requests/view', $data);
    }

    /**
     * Approve a listing request and automatically convert to listing
     */
    public function approve($requestId)
    {
        $authCheck = $this->checkAuth();
        if ($authCheck) return $authCheck;

        $merchantId = session()->get('user_id');

        // Get request
        $request = $this->listingRequestModel->find($requestId);

        if (!$request || $request['merchant_id'] != $merchantId) {
            return redirect()->back()->with('error', 'Request not found or access denied');
        }

        if ($request['status'] !== 'pending') {
            return redirect()->back()->with('error', 'This request has already been reviewed');
        }

        // Check listing limit before approving
        $currentCount = $this->listingModel->where('merchant_id', $merchantId)
                                          ->where('status', 'approved')
                                          ->countAllResults();

        $limitCheck = $this->planLimitModel->checkLimit($merchantId, 'max_listings', $currentCount);

        if (!$limitCheck['allowed']) {
            return redirect()->back()->with('error', $limitCheck['message'] . ' <a href="' . site_url('merchant/subscription/plans') . '" class="underline">Upgrade your plan</a>');
        }

        // Approve the request
        if (!$this->listingRequestModel->approveRequest($requestId, $merchantId)) {
            return redirect()->back()->with('error', 'Failed to approve request');
        }

        // Automatically convert to listing
        $listingId = $this->convertToListing($requestId, $merchantId, $request);

        if (!$listingId) {
            return redirect()->back()->with('error', 'Request approved but failed to create listing. Please try converting manually.');
        }

        // Redirect to listing requests page
        return redirect()->to('merchant/listing-requests')
                        ->with('success', 'Request approved and listing created successfully! You can now add images and finalize details.');
    }

    /**
     * Reject a listing request
     */
    public function reject($requestId)
    {
        $authCheck = $this->checkAuth();
        if ($authCheck) return $authCheck;

        $merchantId = session()->get('user_id');
        $reason = $this->request->getPost('rejection_reason');

        if (empty($reason)) {
            return redirect()->back()->with('error', 'Please provide a reason for rejection');
        }

        // Get request
        $request = $this->listingRequestModel->find($requestId);

        if (!$request || $request['merchant_id'] != $merchantId) {
            return redirect()->back()->with('error', 'Request not found or access denied');
        }

        if ($request['status'] !== 'pending') {
            return redirect()->back()->with('error', 'This request has already been reviewed');
        }

        // Reject the request
        if ($this->listingRequestModel->rejectRequest($requestId, $merchantId, $reason)) {
            return redirect()->back()->with('success', 'Request rejected successfully');
        }

        return redirect()->back()->with('error', 'Failed to reject request');
    }

    /**
     * Convert approved request to actual listing (helper method)
     */
    private function convertToListing($requestId, $merchantId, $request)
    {
        // Handle main image - copy from listing-requests to listings folder
        $mainImagePath = null;
        if (!empty($request['main_image'])) {
            $sourcePath = FCPATH . 'uploads/listing-requests/' . $request['main_image'];
            if (file_exists($sourcePath)) {
                $destPath = FCPATH . 'uploads/listings/' . $request['main_image'];
                if (copy($sourcePath, $destPath)) {
                    // Store path in format: uploads/listings/filename.jpg
                    $mainImagePath = 'uploads/listings/' . $request['main_image'];
                }
            }
        }

        // Create the listing
        $listingData = [
            'merchant_id' => $merchantId,
            'location_id' => $request['location_id'],
            'title' => $request['title'],
            'description' => $request['description'],
            'price' => $request['suggested_price'] ?? 0,
            'price_numeric' => $request['suggested_price'] ?? 0,
            'currency_code' => $request['currency_code'] ?? 'ZAR',
            'unit' => $request['unit'],
            'main_image_path' => $mainImagePath,
            'status' => 'approved', // Auto-approve since merchant is approving it
            'listing_status' => 'approved'
        ];

        $listingId = $this->listingModel->insert($listingData);

        if (!$listingId) {
            log_message('error', 'Failed to create listing from request ' . $requestId . ': ' . implode(', ', $this->listingModel->errors()));
            return false;
        }

        // Handle gallery images
        if (!empty($request['gallery_images'])) {
            $galleryImages = json_decode($request['gallery_images'], true);
            if ($galleryImages && is_array($galleryImages)) {
                $listingImageModel = new \App\Models\MerchantListingImageModel();
                foreach ($galleryImages as $imageName) {
                    $sourcePath = FCPATH . 'uploads/listing-requests/' . $imageName;
                    if (file_exists($sourcePath)) {
                        $destPath = FCPATH . 'uploads/listings/' . $imageName;
                        if (copy($sourcePath, $destPath)) {
                            try {
                                // Store path in format: uploads/listings/filename.jpg
                                $listingImageModel->insert([
                                    'listing_id' => $listingId,
                                    'image_path' => 'uploads/listings/' . $imageName,
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s')
                                ]);
                            } catch (\Exception $e) {
                                log_message('error', 'Failed to add gallery image ' . $imageName . ' to listing ' . $listingId . ': ' . $e->getMessage());
                            }
                        }
                    }
                }
            }
        }

        // Add categories if provided
        if (!empty($request['suggested_categories'])) {
            $categoryIds = json_decode($request['suggested_categories'], true);
            if ($categoryIds && is_array($categoryIds)) {
                $categoryModel = new ServiceCategoryModel();
                $db = \Config\Database::connect();

                foreach ($categoryIds as $categoryId) {
                    // Verify category exists before inserting
                    $category = $categoryModel->find($categoryId);
                    if ($category) {
                        try {
                            $db->table('merchant_listing_categories')->insert([
                                'listing_id' => $listingId,
                                'category_id' => $categoryId
                            ]);
                        } catch (\Exception $e) {
                            // Log error but continue with other categories
                            log_message('error', 'Failed to add category ' . $categoryId . ' to listing ' . $listingId . ': ' . $e->getMessage());
                        }
                    }
                }
            }
        }

        // Mark request as converted
        $this->listingRequestModel->markAsConverted($requestId, $listingId);

        return $listingId;
    }

    /**
     * Convert approved request to actual listing (public method for backward compatibility)
     */
    public function convert($requestId)
    {
        $authCheck = $this->checkAuth();
        if ($authCheck) return $authCheck;

        $merchantId = session()->get('user_id');

        // Get request
        $request = $this->listingRequestModel->find($requestId);

        if (!$request || $request['merchant_id'] != $merchantId) {
            return redirect()->back()->with('error', 'Request not found or access denied');
        }

        if ($request['status'] !== 'approved') {
            return redirect()->back()->with('error', 'Only approved requests can be converted to listings');
        }

        // Check listing limit
        $currentCount = $this->listingModel->where('merchant_id', $merchantId)
                                          ->where('status', 'approved')
                                          ->countAllResults();

        $limitCheck = $this->planLimitModel->checkLimit($merchantId, 'max_listings', $currentCount);

        if (!$limitCheck['allowed']) {
            return redirect()->back()->with('error', $limitCheck['message'] . ' <a href="' . site_url('merchant/subscription/plans') . '" class="underline">Upgrade your plan</a>');
        }

        // Convert to listing using helper method
        $listingId = $this->convertToListing($requestId, $merchantId, $request);

        if (!$listingId) {
            return redirect()->back()->with('error', 'Failed to create listing: ' . implode(', ', $this->listingModel->errors()));
        }

        return redirect()->to('merchant/listings/edit/' . $listingId)
                        ->with('success', 'Listing created successfully from request! You can now add images and finalize details.');
    }
}

