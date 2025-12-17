<?php

namespace App\Controllers;

use App\Models\MerchantListingModel;
use App\Models\MerchantModel;
use App\Models\ServiceCategoryModel;
use App\Services\CurrencyService;
use CodeIgniter\Controller;

class Services extends Controller
{
    protected $merchantListingModel;
    protected $merchantModel;
    protected $serviceCategoryModel;

    public function __construct()
    {
        $this->merchantListingModel = new MerchantListingModel();
        $this->merchantModel = new MerchantModel();
        $this->serviceCategoryModel = new ServiceCategoryModel();
    }

    public function index()
    {
        // Check if driver is logged in
        if (!session()->get('is_logged_in') || session()->get('user_type') !== 'driver') {
            return redirect()->to('/login')->with('error', 'Please log in as a driver to access this page.');
        }

        // Get search and category parameters
        $request = \Config\Services::request();
        $searchTerm = $request->getVar('search') ?? '';
        $categoryId = $request->getVar('category') ?? '';

        // Build the query for all approved listings using new junction table structure
        $builder = $this->merchantListingModel->builder();
        $builder->select('merchant_listings.*, merchants.business_name, merchants.physical_address, merchants.business_image_url, merchants.profile_image_url, merchants.is_verified, merchant_locations.location_name, merchant_locations.is_primary, merchant_locations.physical_address as location_address, supported_currencies.currency_symbol, GROUP_CONCAT(DISTINCT service_categories.name) as category_name')
                ->join('merchants', 'merchants.id = merchant_listings.merchant_id')
                ->join('merchant_locations', 'merchant_locations.id = merchant_listings.location_id', 'left')
                ->join('supported_currencies', 'supported_currencies.currency_code = merchant_listings.currency_code COLLATE utf8mb4_unicode_ci', 'left', false)
                ->join('merchant_listing_categories mlc', 'mlc.listing_id = merchant_listings.id', 'left')
                ->join('service_categories', 'service_categories.id = mlc.category_id', 'left')
                ->where('merchant_listings.status', 'approved')
                ->where('merchant_listings.is_available', 1)
                ->where('merchants.status', 'approved')
                ->groupBy('merchant_listings.id');

        // Apply category filter if specified
        if (!empty($categoryId) && $categoryId !== 'all') {
            $builder->where('mlc.category_id', $categoryId);
        }

        // Apply search filter if specified
        if (!empty($searchTerm)) {
            $builder->groupStart()
                    ->like('merchant_listings.title', $searchTerm)
                    ->orLike('merchant_listings.description', $searchTerm)
                    ->orLike('merchants.business_name', $searchTerm)
                    ->orLike('service_categories.name', $searchTerm)
                    ->groupEnd();
        }

        $builder->orderBy('merchant_listings.created_at', 'DESC');
    
    // Debug: Log the SQL query
    log_message('debug', 'Services SQL: ' . $builder->getCompiledSelect(false));

    $listings = $builder->get()->getResultArray();

    // Debug: Log the results count
    log_message('debug', 'Services found: ' . count($listings) . ' listings');
    if (!empty($searchTerm)) {
        log_message('debug', 'Search term: ' . $searchTerm);
    }
    if (!empty($categoryId)) {
        log_message('debug', 'Category ID: ' . $categoryId);
    }

        // Check verification status for each listing's merchant
        $db = \Config\Database::connect();
        foreach ($listings as &$listing) {
            $merchantId = $listing['merchant_id'];

            // Check if merchant has approved documents
            $approvedDocs = $db->table('merchant_documents')
                ->where('merchant_id', $merchantId)
                ->where('is_verified', 'approved')
                ->countAllResults();

            // Merchant is verified if they have approved documents OR if is_verified is set to 'verified'
            $listing['is_verified'] = ($approvedDocs > 0) || (isset($listing['is_verified']) && $listing['is_verified'] === 'verified');
        }

        // Get all service categories for filtering
        $categories = $this->serviceCategoryModel->getAllCategories();

        // Prepare page title
        $pageTitle = 'All Services & Products';
        if (!empty($searchTerm) && !empty($categoryId) && $categoryId !== 'all') {
            $category = $this->serviceCategoryModel->find($categoryId);
            $pageTitle = "Search results for '{$searchTerm}' in " . ($category['name'] ?? 'Unknown Category');
        } elseif (!empty($searchTerm)) {
            $pageTitle = "Search results for '{$searchTerm}'";
        } elseif (!empty($categoryId) && $categoryId !== 'all') {
            $category = $this->serviceCategoryModel->find($categoryId);
            $pageTitle = ($category['name'] ?? 'Category') . ' Services';
        }

        $data = [
            'page_title' => $pageTitle,
            'listings' => $listings,
            'categories' => $categories,
            'search_term' => $searchTerm,
            'selected_category' => $categoryId,
            'total_results' => count($listings)
        ];

        return view('driver/services', $data);
    }

    /**
     * API endpoint to get all merchants with their services
     * This replicates the getMerchantsWithServices() method from Routes controller
     */
    public function merchantsWithServices()
    {
        // Get all approved merchants with their service categories
        $db = \Config\Database::connect();
        $builder = $db->table('merchants m');
        
        $merchants = $builder->select('m.*, GROUP_CONCAT(DISTINCT sc.id) as service_category_ids, GROUP_CONCAT(DISTINCT sc.name) as service_categories')
                            ->join('merchant_listings ml', 'ml.merchant_id = m.id', 'left')
                            ->join('merchant_listing_categories mlc', 'mlc.listing_id = ml.id', 'left')
                            ->join('service_categories sc', 'sc.id = mlc.category_id', 'left')
                            ->where('m.status', 'approved')
                            ->where('m.is_visible', 1)
                            ->where('m.latitude IS NOT NULL')
                            ->where('m.longitude IS NOT NULL')
                            ->groupBy('m.id')
                            ->get()
                            ->getResultArray();
        
        // Process the results to add service category arrays
        foreach ($merchants as &$merchant) {
            $merchant['service_category_ids'] = $merchant['service_category_ids'] ? explode(',', $merchant['service_category_ids']) : [];
            $merchant['service_categories'] = $merchant['service_categories'] ? explode(',', $merchant['service_categories']) : [];
        }
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $merchants,
            'message' => 'Merchants with services retrieved successfully'
        ]);
    }

    /**
     * API endpoint to get all services/listings (existing functionality for mobile app)
     */
    public function all()
    {
        // Build the query for all approved listings using new junction table structure
        $builder = $this->merchantListingModel->builder();
        $builder->select('merchant_listings.*, merchants.business_name, merchants.physical_address, merchants.latitude, merchants.longitude, merchants.business_image_url, merchants.profile_image_url, GROUP_CONCAT(DISTINCT service_categories.name) as category_name')
                ->join('merchants', 'merchants.id = merchant_listings.merchant_id')
                ->join('merchant_listing_categories mlc', 'mlc.listing_id = merchant_listings.id', 'left')
                ->join('service_categories', 'service_categories.id = mlc.category_id', 'left')
                ->where('merchant_listings.status', 'approved')
                ->where('merchant_listings.is_available', 1)
                ->where('merchants.status', 'approved')
                ->groupBy('merchant_listings.id')
                ->orderBy('merchant_listings.created_at', 'DESC');

        $listings = $builder->get()->getResultArray();

        return $this->response->setJSON([
            'success' => true,
            'data' => $listings,
            'message' => 'Services retrieved successfully'
        ]);
    }
}
