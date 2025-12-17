<?php

namespace App\Controllers;

use App\Models\MerchantModel;
use App\Models\ServiceModel;
use App\Models\ReviewModel;
use App\Models\MerchantServiceModel;
use App\Models\MerchantListingModel;
use App\Models\ServiceCategoryModel;
use CodeIgniter\Controller;

class Search extends Controller
{
    /**
     * Driver-specific search with category filtering
     */
    public function index()
    {
        // Check if user is logged in as driver
        if (!session()->get('is_logged_in') || session()->get('user_type') !== 'driver') {
            return redirect()->to('/login');
        }

        $request = \Config\Services::request();
        $merchantListingModel = new MerchantListingModel();
        $serviceCategoryModel = new ServiceCategoryModel();
        $serviceModel = new ServiceModel();
        
        $searchTerm = $request->getVar('search');
        $categoryId = $request->getVar('category');
        
        // Get all categories for filter display
        $categories = $serviceCategoryModel->getAllCategories();
        
        // Build the query using new junction table structure
        $builder = $merchantListingModel->select('merchant_listings.*, merchants.business_name, merchants.physical_address, GROUP_CONCAT(DISTINCT service_categories.name) as category_name')
                                       ->join('merchants', 'merchants.id = merchant_listings.merchant_id')
                                       ->join('merchant_listing_categories mlc', 'mlc.listing_id = merchant_listings.id', 'left')
                                       ->join('service_categories', 'service_categories.id = mlc.category_id', 'left')
                                       ->where('merchant_listings.status', 'approved')
                                       ->where('merchant_listings.is_available', 1)
                                       ->where('merchants.status', 'approved')
                                       ->groupBy('merchant_listings.id');
        
        // Apply category filter
        if ($categoryId && $categoryId !== 'all') {
            $builder->where('mlc.category_id', $categoryId);
        }
        
        // Apply search term filter
        if ($searchTerm) {
            $builder->groupStart()
                   ->like('merchant_listings.title', $searchTerm)
                   ->orLike('merchant_listings.description', $searchTerm)
                   ->orLike('merchants.business_name', $searchTerm)
                   ->orLike('service_categories.name', $searchTerm)
                   ->groupEnd();
        }
        
        $listings = $builder->orderBy('merchant_listings.created_at', 'DESC')
                           ->findAll();
        
        // Get selected category name
        $selectedCategory = null;
        if ($categoryId && $categoryId !== 'all') {
            $selectedCategory = $serviceCategoryModel->find($categoryId);
        }
        
        $data = [
            'listings' => $listings,
            'categories' => $categories,
            'search_term' => $searchTerm,
            'selected_category' => $selectedCategory,
            'category_id' => $categoryId,
            'page_title' => $this->buildPageTitle($searchTerm, $selectedCategory),
            'total_results' => count($listings)
        ];
        
        return view('driver/search_results', $data);
    }
    
    /**
     * Build page title based on search criteria
     */
    private function buildPageTitle($searchTerm, $selectedCategory)
    {
        if ($searchTerm && $selectedCategory) {
            return "Search Results for '{$searchTerm}' in {$selectedCategory['name']}";
        } elseif ($searchTerm) {
            return "Search Results for '{$searchTerm}'";
        } elseif ($selectedCategory) {
            return "{$selectedCategory['name']} Services";
        } else {
            return "All Services";
        }
    }
    /**
     * Takes a search query from the URL and returns a list of matching merchants and listings.
     * If no query is provided, it shows a list of all services.
     * This is the public search for non-logged-in users (home page search).
     */
    public function find()
    {
        $request = \Config\Services::request();
        $merchantListingModel = new MerchantListingModel();
        $serviceCategoryModel = new ServiceCategoryModel();
        $serviceModel = new ServiceModel();

        // Support both 'q' (home page) and 'search' (for consistency) parameters
        $query = $request->getVar('q') ?: $request->getVar('search');
        $categoryId = $request->getVar('category');

        $categories = $serviceCategoryModel->getAllCategories();

        $data = [
            'search_query' => $query,
            'page_class'   => 'bg-gray-900 text-slate-200', // Matches homepage style
            'categories'   => $categories,
        ];

        if ($query) {
            // Search for merchant listings (same logic as driver search but for public)
            $builder = $merchantListingModel->select('merchant_listings.*, merchants.business_name, merchants.physical_address, merchants.latitude, merchants.longitude, GROUP_CONCAT(DISTINCT service_categories.name) as category_names')
                                           ->join('merchants', 'merchants.id = merchant_listings.merchant_id')
                                           ->join('merchant_listing_categories mlc', 'mlc.listing_id = merchant_listings.id', 'left')
                                           ->join('service_categories', 'service_categories.id = mlc.category_id', 'left')
                                           ->where('merchant_listings.status', 'approved')
                                           ->where('merchant_listings.is_available', 1)
                                           ->where('merchants.verification_status', 'approved')
                                           ->where('merchants.is_visible', 1)
                                           ->groupBy('merchant_listings.id');

            // Apply category filter if provided
            if ($categoryId && $categoryId !== 'all') {
                $builder->where('mlc.category_id', $categoryId);
            }

            // Apply search term filter
            $builder->groupStart()
                   ->like('merchant_listings.title', $query)
                   ->orLike('merchant_listings.description', $query)
                   ->orLike('merchants.business_name', $query)
                   ->orLike('service_categories.name', $query)
                   ->groupEnd();

            $listings = $builder->orderBy('merchant_listings.created_at', 'DESC')
                               ->findAll();

            // Group listings by merchant for better display
            $merchantsWithListings = [];
            foreach ($listings as $listing) {
                $merchantId = $listing['merchant_id'];
                if (!isset($merchantsWithListings[$merchantId])) {
                    $merchantsWithListings[$merchantId] = [
                        'id' => $merchantId,
                        'business_name' => $listing['business_name'],
                        'physical_address' => $listing['physical_address'],
                        'latitude' => $listing['latitude'],
                        'longitude' => $listing['longitude'],
                        'listings' => []
                    ];
                }
                $merchantsWithListings[$merchantId]['listings'][] = $listing;
            }

            $data['merchants_with_listings'] = array_values($merchantsWithListings);
            $data['total_results'] = count($listings);
            $data['page_title'] = 'Search Results for "' . esc($query) . '"';
        } else {
            // No search query, so show all available services.
            $data['services'] = $serviceModel->orderBy('name', 'ASC')->findAll();
            $data['page_title'] = 'Browse All Services';
        }

        // Re-using the homepage header and footer for consistent design.
        return view('templates/home-header', $data)
             . view('search/results', $data)
             . view('templates/home-footer', $data);
    }

    /**
     * Displays the detailed public profile of a single merchant.
     */
    public function viewMerchant($id)
    {
        $merchantModel = new MerchantModel();
        $reviewModel = new ReviewModel();
        $merchantServiceModel = new MerchantServiceModel();
        $serviceModel = new ServiceModel();

        $merchant = $merchantModel->find($id);

        if (!$merchant || $merchant['verification_status'] !== 'approved' || !$merchant['is_visible']) {
            // Use CodeIgniter's built-in exception for a clean 404 page.
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Get the IDs of services offered by this merchant.
        $serviceIds = $merchantServiceModel->findByMerchantId($id);
        
        // Get the full service details from the IDs.
        $services = [];
        if (!empty($serviceIds)) {
            $services = $serviceModel->whereIn('id', $serviceIds)->orderBy('name', 'ASC')->findAll();
        }

        $data = [
            'merchant' => $merchant,
            'services' => $services,
            'reviews' => $reviewModel->findByMerchantId($id),
            'average_rating' => $reviewModel->getAverageRating($id),
            'page_title' => esc($merchant['business_name']),
            'page_class'   => 'bg-gray-900 text-slate-200',
        ];

        return view('templates/home-header', $data)
             . view('search/merchant_profile', $data)
             . view('templates/home-footer', $data);
    }
}