<?php

namespace App\Controllers;

use App\Models\ServiceModel;
use App\Models\MerchantModel;
use App\Models\MerchantListingModel;
use App\Models\RouteModel;
use App\Models\TruckDriverModel;
use App\Models\DriverLocationHistoryModel;
use App\Models\UserLoginModel;
use App\Models\MerchantListingImageModel;
use App\Models\ServiceCategoryModel;
use App\Models\CurrencyModel;
use App\Services\CurrencyService;
use App\Controllers\BaseController;
use App\Entities\TruckDriver;

/**
 * @property \CodeIgniter\HTTP\IncomingRequest $request
 */
class DriverDashboard extends BaseController
{
    protected $merchantModel;
    protected $merchantListingModel;
    protected $routeModel;
    protected $driverModel;
    protected $userLoginModel;
    protected $driverLocationHistoryModel;
    
    public function __construct()
    {
        $this->merchantModel = new MerchantModel();
        $this->merchantListingModel = new MerchantListingModel();
        $this->routeModel = new RouteModel();
        $this->driverModel = new TruckDriverModel();
        $this->userLoginModel = new UserLoginModel();
        $this->driverLocationHistoryModel = new DriverLocationHistoryModel();
    }

    /**
     * Displays the driver's main dashboard view.
     */
    public function index()
    {
        $session = session();
        $userId = $session->get('user_id');
        
        if (!$userId) {
            return redirect()->to('/login');
        }

        // The user_id in session should be the driver's ID directly
        // Let's check if this is a driver by looking up in user_logins
        $userLogin = $this->userLoginModel->where('user_id', $userId)
                                         ->where('user_type', 'truck_driver')
                                         ->first();
        if (!$userLogin) {
            return redirect()->to('/login');
        }
        
        // Get driver details. TruckDriverModel's find() returns an array.
        /** @var array $driver */
        $driver = $this->driverModel->find($userId);
        if (!$driver) {
            return redirect()->to('/login');
        }
        
        $serviceModel = new ServiceModel();
        $serviceCategoryModel = new ServiceCategoryModel();

        // Get recent routes
        $recentRoutes = $this->routeModel->findByDriverId($driver['id']);
        $recentRoutes = array_slice($recentRoutes, 0, 3); // Get last 3 routes

        // Fetch all merchants with their services for filtering
        $merchants = $this->getMerchantsWithServices();

        $routeMerchants = $this->getMerchantsOnRoute($driver['id']);

        // Get all service categories for filtering
        $categories = $serviceCategoryModel->getAllCategories();

        // Get all business locations (both primary and branches) for approved merchants with active subscriptions
        $locationModel = new \App\Models\MerchantLocationModel();
        $businessLocations = $locationModel->select('merchant_locations.*, merchants.business_name, merchants.business_image_url')
                                           ->join('merchants', 'merchants.id = merchant_locations.merchant_id')
                                           ->join('subscriptions', 'subscriptions.merchant_id = merchants.id', 'left')
                                           ->where('merchant_locations.is_active', 1)
                                           ->where('merchant_locations.latitude IS NOT NULL')
                                           ->where('merchant_locations.longitude IS NOT NULL')
                                           ->where('merchants.is_visible', 1)
                                           ->where('merchants.verification_status', 'approved')
                                           ->groupStart()
                                               ->where('subscriptions.status', 'active')
                                               ->orWhere('subscriptions.status', 'trial')
                                           ->groupEnd()
                                           ->findAll();

        // Get driver's last known location
        $driverLocation = $this->driverLocationHistoryModel
            ->where('truck_driver_id', $driver['id'])
            ->orderBy('recorded_at', 'DESC')
            ->first();

        // Calculate distance for each location and sort by proximity
        if ($driverLocation && !empty($driverLocation['latitude']) && !empty($driverLocation['longitude'])) {
            $driverLat = (float)$driverLocation['latitude'];
            $driverLng = (float)$driverLocation['longitude'];

            // Add distance to each location
            foreach ($businessLocations as &$location) {
                $location['distance'] = $this->calculateDistance(
                    $driverLat,
                    $driverLng,
                    (float)$location['latitude'],
                    (float)$location['longitude']
                );
            }
            unset($location); // Break reference

            // Sort by distance (closest first)
            usort($businessLocations, function($a, $b) {
                return $a['distance'] <=> $b['distance'];
            });
        } else {
            // If no driver location, sort by primary first, then by created date
            usort($businessLocations, function($a, $b) {
                if ($a['is_primary'] != $b['is_primary']) {
                    return $b['is_primary'] <=> $a['is_primary'];
                }
                return strtotime($b['created_at']) <=> strtotime($a['created_at']);
            });
        }

        $data = [
            'services' => $serviceModel->getHomepageServices(8),
            'page_title' => 'Driver Dashboard',
            'driver' => $driver,
            'recent_routes' => $recentRoutes,
            'merchants' => $merchants,
            'route_merchants' => $routeMerchants,
            'categories' => $categories,
            'business_locations' => $businessLocations,
            'geoapify_api_key' => getenv('GEOAPIFY_API_KEY'),
            'driver_location' => $driverLocation
        ];

        return view('driver/dashboard', $data);
    }

    public function service_view($listing_id = null)
    {
        if (!$listing_id) {
            // Redirect or show an error if no ID is provided
            return redirect()->to('/driver/dashboard')->with('error', 'Service not found.');
        }

        // Fetch listing with currency symbol
        $db = \Config\Database::connect();
        $listing = $db->table('merchant_listings ml')
                      ->select('ml.*, sc.currency_symbol')
                      ->join('supported_currencies sc', 'sc.currency_code = ml.currency_code COLLATE utf8mb4_unicode_ci', 'left', false)
                      ->where('ml.id', $listing_id)
                      ->get()
                      ->getRowArray();

        if (!$listing || $listing['status'] !== 'approved') {
            // Handle case where listing is not found or not approved
            return redirect()->to('/driver/dashboard')->with('error', 'Service not available.');
        }

        // Fetch merchant details
        $merchantModel = new \App\Models\MerchantModel();
        $merchant = $merchantModel->find($listing['merchant_id']);

        // Check if merchant has verified documents
        $db = \Config\Database::connect();
        $documentsQuery = $db->table('merchant_documents')
            ->where('merchant_id', $listing['merchant_id'])
            ->where('is_verified', 'approved')
            ->countAllResults();

        // Merchant is verified if they have approved documents OR if is_verified is set to 'verified'
        $isVerified = ($documentsQuery > 0) || (isset($merchant['is_verified']) && $merchant['is_verified'] === 'verified');

        // Fetch location details if available
        $location = null;
        if (!empty($listing['location_id'])) {
            $locationModel = new \App\Models\MerchantLocationModel();
            $location = $locationModel->find($listing['location_id']);
        }

        // Fetch gallery images
        $imageModel = new MerchantListingImageModel();
        $galleryImages = $imageModel->findByListingId($listing_id);

        $data = [
            'page_title' => $listing['title'],
            'listing' => $listing,
            'merchant' => $merchant,
            'location' => $location,
            'gallery_images' => $galleryImages,
            'is_verified' => $isVerified
        ];

        return view('driver/service_view', $data);
    }

    public function merchant_profile($merchant_id = null)
    {
        if (!$merchant_id) {
            return redirect()->to('dashboard/driver/')->with('error', 'Merchant not found.');
        }

        // Fetch merchant details
        $merchant = $this->merchantModel->find($merchant_id);

        if (!$merchant || $merchant['status'] !== 'approved') {
            return redirect()->to('dashboard/driver/')->with('error', 'Merchant not available.');
        }

        // Fetch all approved service listings for this merchant with location information
        $listings = $this->merchantListingModel
                         ->select('merchant_listings.*, merchant_locations.location_name, merchant_locations.id as location_id')
                         ->join('merchant_locations', 'merchant_locations.id = merchant_listings.location_id', 'left')
                         ->where('merchant_listings.merchant_id', $merchant_id)
                         ->where('merchant_listings.status', 'approved')
                         ->findAll();

        // Fetch service categories
        $categoryModel = new ServiceCategoryModel();
        $categories = $categoryModel->getAllCategories();

        $data = [
            'page_title' => $merchant['business_name'],
            'merchant' => $merchant,
            'listings' => $listings,
            'categories' => $categories
        ];

        return view('driver/merchant_profile', $data);
    }

    public function location_view($location_id = null)
    {
        if (!$location_id) {
            return redirect()->to('dashboard/driver/')->with('error', 'Location not found.');
        }

        // Fetch location details with subscription check
        $locationModel = new \App\Models\MerchantLocationModel();
        $location = $locationModel->select('merchant_locations.*, merchants.business_name, merchants.business_image_url, merchants.id as merchant_id')
                                  ->join('merchants', 'merchants.id = merchant_locations.merchant_id')
                                  ->join('subscriptions', 'subscriptions.merchant_id = merchants.id', 'left')
                                  ->where('merchant_locations.id', $location_id)
                                  ->where('merchant_locations.is_active', 1)
                                  ->where('merchants.is_visible', 1)
                                  ->where('merchants.verification_status', 'approved')
                                  ->groupStart()
                                      ->where('subscriptions.status', 'active')
                                      ->orWhere('subscriptions.status', 'trial')
                                  ->groupEnd()
                                  ->first();

        if (!$location) {
            return redirect()->to('dashboard/driver/')->with('error', 'Location not available.');
        }

        // Fetch merchant details
        $merchant = $this->merchantModel->find($location['merchant_id']);

        // Fetch all approved service listings for this location with currency symbol
        $db = \Config\Database::connect();
        $merchantListings = $db->table('merchant_listings ml')
                               ->select('ml.*, sc.currency_symbol')
                               ->join('supported_currencies sc', 'sc.currency_code = ml.currency_code COLLATE utf8mb4_unicode_ci', 'left', false)
                               ->where('ml.merchant_id', $location['merchant_id'])
                               ->where('ml.status', 'approved')
                               ->groupStart()
                                   ->where('ml.location_id', $location_id)
                                   ->orWhere('ml.location_id', null)
                               ->groupEnd()
                               ->get()
                               ->getResultArray();

        // Fetch service categories
        $categoryModel = new ServiceCategoryModel();
        $categories = $categoryModel->getAllCategories();

        $data = [
            'page_title' => $location['location_name'],
            'location' => $location,
            'merchant' => $merchant,
            'listings' => $merchantListings,
            'categories' => $categories
        ];

        return view('driver/location_view', $data);
    }

    /**
     * Get nearby merchants for driver feed
     */
    public function getNearbyMerchantsFeed()
    {
        $session = session();
        $userId = $session->get('user_id');
        
        if (!$userId) {
            return $this->response->setJSON(['error' => 'User not authenticated']);
        }

        $userLogin = $this->userLoginModel->where('user_id', $userId)
                                         ->where('user_type', 'truck_driver')
                                         ->first();
        if (!$userLogin) {
            return $this->response->setJSON(['error' => 'Driver not found']);
        }
        
        // Note: TruckDriverModel returns arrays, not entity objects
        $driver = $this->driverModel->find($userId);
        if (!$driver) {
            return $this->response->setJSON(['error' => 'Driver not found']);
        }

        /** @var \CodeIgniter\HTTP\IncomingRequest $request */
        $request = $this->request;
        $lat = $request->getVar('lat') ?: ($driver['current_latitude'] ?? null);
        $lng = $request->getVar('lng') ?: ($driver['current_longitude'] ?? null);
        $radius = $request->getVar('radius') ?: 20;

        if (!$lat || !$lng) {
            return $this->response->setJSON(['error' => 'Location not available']);
        }

        $merchants = $this->getNearbyMerchants($lat, $lng, $radius);

        return $this->response->setJSON([
            'success' => true,
            'merchants' => $merchants
        ]);
    }

    /**
     * Update driver location
     */
    public function updateLocation()
    {
        $session = session();
        $userId = $session->get('user_id');
        
        if (!$userId) {
            return $this->response->setJSON(['error' => 'User not authenticated']);
        }

        $userLogin = $this->userLoginModel->where('user_id', $userId)
                                         ->where('user_type', 'truck_driver')
                                         ->first();
        if (!$userLogin) {
            return $this->response->setJSON(['error' => 'Driver not found']);
        }
        
        // Note: TruckDriverModel returns arrays, not entity objects
        $driver = $this->driverModel->find($userId);
        if (!$driver) {
            return $this->response->setJSON(['error' => 'Driver not found']);
        }

        /** @var \CodeIgniter\HTTP\IncomingRequest $request */
        $request = $this->request;
        $lat = $request->getPost('latitude');
        $lng = $request->getPost('longitude');
        $accuracy = $request->getPost('accuracy');
        $speed = $request->getPost('speed');
        $heading = $request->getPost('heading');

        if (!$lat || !$lng) {
            return $this->response->setJSON(['error' => 'Latitude and longitude required']);
        }

        $updated = $this->driverModel->update($driver['id'], [
            'current_latitude' => $lat,
            'current_longitude' => $lng,
            'last_location_update' => date('Y-m-d H:i:s')
        ]);

        if ($updated) {
            // Persist to location history (best-effort)
            try {
                $historyData = [
                    'truck_driver_id' => (int) $driver['id'],
                    'latitude' => (float) $lat,
                    'longitude' => (float) $lng,
                    'recorded_at' => date('Y-m-d H:i:s')
                ];
                if ($accuracy !== null && $accuracy !== '') {
                    $historyData['accuracy'] = (float) $accuracy;
                }
                if ($speed !== null && $speed !== '') {
                    $historyData['speed'] = (float) $speed; // expected km/h
                }
                if ($heading !== null && $heading !== '') {
                    $historyData['heading'] = (float) $heading;
                }
                $this->driverLocationHistoryModel->insert($historyData);
            } catch (\Throwable $e) {
                // Silently ignore history persistence errors
            }
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Location updated successfully'
            ]);
        }

        return $this->response->setJSON(['error' => 'Failed to update location']);
    }

    /**
     * Helper method to get nearby merchants
     */
    private function getNearbyMerchants($lat, $lng, $radiusKm = 20)
    {
        $merchants = $this->merchantModel->select('merchants.*')
                                       ->join('subscriptions', 'subscriptions.merchant_id = merchants.id', 'left')
                                       ->where('merchants.status', 'approved')
                                       ->where('merchants.is_visible', 1)
                                       ->where('merchants.latitude IS NOT NULL')
                                       ->where('merchants.longitude IS NOT NULL')
                                       ->groupStart()
                                           ->where('subscriptions.status', 'active')
                                           ->orWhere('subscriptions.status', 'trial')
                                       ->groupEnd()
                                       ->findAll();

        $nearbyMerchants = [];
        foreach ($merchants as $merchant) {
            $distance = $this->calculateDistance($lat, $lng, $merchant['latitude'], $merchant['longitude']);
            
            if ($distance <= $radiusKm) {
                $merchant['distance'] = round($distance, 2);
                $nearbyMerchants[] = $merchant;
            }
        }

        // Sort by distance
        usort($nearbyMerchants, function($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });

        return array_slice($nearbyMerchants, 0, 10); // Return top 10 closest
    }

    /**
     * Helper method to get nearby merchant listings
     */
    private function getNearbyMerchantListings($lat, $lng, $radiusKm = 50)
    {
        // Get all approved listings with merchant location data
        $listings = $this->merchantListingModel->select('merchant_listings.*, merchants.business_name, merchants.latitude, merchants.longitude')
                                              ->join('merchants', 'merchants.id = merchant_listings.merchant_id')
                                              ->where('merchant_listings.status', 'approved')
                                              ->where('merchants.status', 'approved')
                                              ->where('merchants.is_visible', 1)
                                              ->where('merchants.latitude IS NOT NULL')
                                              ->where('merchants.longitude IS NOT NULL')
                                              ->findAll();

        $nearbyListings = [];
        foreach ($listings as $listing) {
            $distance = $this->calculateDistance($lat, $lng, $listing['latitude'], $listing['longitude']);
            
            if ($distance <= $radiusKm) {
                $listing['distance'] = round($distance, 2);
                $nearbyListings[] = $listing;
            }
        }

        // Sort by distance
        usort($nearbyListings, function($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });

        return array_slice($nearbyListings, 0, 12); // Return top 12 closest listings
    }

    /**
     * Get nearby listings for driver feed
     */
    public function getNearbyListingsFeed()
    {
        $session = session();
        $userId = $session->get('user_id');
        
        if (!$userId) {
            return $this->response->setJSON(['error' => 'User not authenticated']);
        }

        $driver = $this->driverModel->find($userId);
        if (!$driver) {
            return $this->response->setJSON(['error' => 'Driver not found']);
        }

        /** @var \CodeIgniter\HTTP\IncomingRequest $request */
        $request = $this->request;
        $lat = $request->getVar('lat') ?: ($driver['current_latitude'] ?? null);
        $lng = $request->getVar('lng') ?: ($driver['current_longitude'] ?? null);
        $radius = $request->getVar('radius') ?: 50;

        if (!$lat || !$lng) {
            return $this->response->setJSON(['error' => 'Location not available']);
        }

        $listings = $this->getNearbyMerchantListings($lat, $lng, $radius);

        return $this->response->setJSON([
            'success' => true,
            'listings' => $listings
        ]);
    }

    /**
     * Latest location for external viewers to poll a driver's current position.
     */
    public function latestLocation($driverId = null)
    {
        if (!$driverId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Driver ID required'])->setStatusCode(400);
        }

        $driver = $this->driverModel->find($driverId);
        if (!$driver) {
            return $this->response->setJSON(['success' => false, 'message' => 'Driver not found'])->setStatusCode(404);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'latitude' => $driver['current_latitude'] ?? null,
                'longitude' => $driver['current_longitude'] ?? null,
                'last_update' => $driver['last_location_update'] ?? null,
            ]
        ]);
    }

    /**
     * Get merchants on the driver's most recent route.
     */
    private function getMerchantsOnRoute($driverId)
    {
        $latestRoute = $this->routeModel->findByDriverId($driverId);
        if (empty($latestRoute)) {
            return [];
        }

        // Get the most recent route
        $route = $this->routeModel->getRouteWithMerchants($latestRoute[0]['id']);
        if (empty($route['merchants'])) {
            return [];
        }

        $merchantIds = array_column($route['merchants'], 'merchant_id');
        if (empty($merchantIds)) {
            return [];
        }

        // Fetch unique merchants on the route
        return $this->merchantModel
                    ->whereIn('id', $merchantIds)
                    ->where('status', 'approved')
                    ->where('is_visible', 1)
                    ->findAll();
    }

    /**
     * Get merchants with their services for filtering
     */
    private function getMerchantsWithServices()
    {
        // Get all approved merchants with their service categories
        $db = \Config\Database::connect();
        $builder = $db->table('merchants m');
        
        $merchants = $builder->select('m.*, GROUP_CONCAT(DISTINCT sc.id) as service_category_ids, GROUP_CONCAT(DISTINCT sc.name) as service_categories')
                            ->join('merchant_listings ml', 'ml.merchant_id = m.id', 'left')
                            ->join('merchant_listing_categories mlc', 'mlc.listing_id = ml.id', 'left')
                            ->join('service_categories sc', 'sc.id = mlc.category_id', 'left')
                            ->join('subscriptions s', 's.merchant_id = m.id', 'left')
                            ->where('m.status', 'approved')
                            ->where('m.is_visible', 1)
                            ->where('m.latitude IS NOT NULL')
                            ->where('m.longitude IS NOT NULL')
                            ->groupStart()
                                ->where('s.status', 'active')
                                ->orWhere('s.status', 'trial')
                            ->groupEnd()
                            ->groupBy('m.id')
                            ->get()
                            ->getResultArray();
        
        // Process the results to add service category arrays
        foreach ($merchants as &$merchant) {
            $merchant['service_category_ids'] = $merchant['service_category_ids'] ? explode(',', $merchant['service_category_ids']) : [];
            $merchant['service_categories'] = $merchant['service_categories'] ? explode(',', $merchant['service_categories']) : [];
        }
        
        return $merchants;
    }

    /**
     * Calculate distance between two points using Haversine formula
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c;
    }

    /**
     * Display currency settings page
     */
    public function currencySettings()
    {
        // Check if user is logged in as driver (using correct session keys)
        $session = session();
        $userId = $session->get('user_id');
        $userType = $session->get('user_type');
        $isLoggedIn = $session->get('is_logged_in');

        // Authentication check using your actual session structure
        if (!$isLoggedIn || !$userId || $userType !== 'driver') {
            return redirect()->to('/login')->with('error', 'Please log in as a driver to access currency settings.');
        }

        $driver = $this->driverModel->find($userId);
        if (!$driver) {
            return redirect()->to('/login')->with('error', 'Driver account not found.');
        }

        $currencyModel = new CurrencyModel();
        $currencyService = new CurrencyService();

        // Get current currency info
        $currentCurrency = $currencyService->getCurrencyInfo($driver['preferred_currency']);
        if (!$currentCurrency) {
            // Fallback to ZAR if current currency is invalid
            $currentCurrency = $currencyService->getCurrencyInfo('ZAR');
        }

        return view('driver/settings/currency', [
            'driver' => $driver,
            'currencies' => $currencyModel->getAllActive(), // Show all active currencies including African currencies
            'currentCurrency' => $currentCurrency,
            'page_title' => 'Currency Preferences'
        ]);
    }

    /**
     * Update driver currency preference
     */
    public function updateCurrencyPreference()
    {
        // Check if user is logged in as driver (using correct session keys)
        $session = session();
        $userId = $session->get('user_id');
        $userType = $session->get('user_type');
        $isLoggedIn = $session->get('is_logged_in');

        // Authentication check using your actual session structure
        if (!$isLoggedIn || !$userId || $userType !== 'driver') {
            return redirect()->to('/login')->with('error', 'Please log in as a driver to update currency settings.');
        }

        $request = $this->request;
        $currencyCode = $request->getPost('preferred_currency');

        // Validate currency code
        $currencyModel = new CurrencyModel();
        if (!$currencyModel->isValidCurrency($currencyCode)) {
            return redirect()->back()->withInput()->with('errors', ['preferred_currency' => 'Invalid currency selected']);
        }

        // Update driver's currency preference
        $updateData = ['preferred_currency' => strtoupper($currencyCode)];

        if ($this->driverModel->update($userId, $updateData)) {
            return redirect()->to('driver/settings/currency')->with('success', 'Currency preference updated successfully!');
        } else {
            return redirect()->back()->withInput()->with('errors', ['general' => 'Failed to update currency preference. Please try again.']);
        }
    }

/**
 * Debug method to check session data
 */
public function debugSession()
{
    $sessionData = session()->get();
    return $this->response->setJSON([
        'session_data' => $sessionData,
        'driver_id' => session()->get('driver_id'),
        'user_id' => session()->get('user_id'),
        'user_type' => session()->get('user_type'),
        'logged_in' => session()->get('logged_in'),
        'is_logged_in' => session()->get('is_logged_in')
    ]);
}

/**
 * Test currency settings page without authentication (for testing only)
 */
public function testCurrencySettings()
{
    $currencyModel = new CurrencyModel();
    $currencyService = new CurrencyService();

    // Use dummy driver data for testing
    $dummyDriver = [
        'id' => 1,
        'name' => 'Test Driver',
        'preferred_currency' => 'ZAR'
    ];

    // Get current currency info
    $currentCurrency = $currencyService->getCurrencyInfo($dummyDriver['preferred_currency']);
    if (!$currentCurrency) {
        $currentCurrency = $currencyService->getCurrencyInfo('ZAR');
    }

    return view('driver/settings/currency', [
        'driver' => $dummyDriver,
        'currencies' => $currencyModel->getHighPriorityCurrencies(),
        'currentCurrency' => $currentCurrency,
        'page_title' => 'Currency Preferences (Test Mode)'
    ]);
}
}