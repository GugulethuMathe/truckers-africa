<?php

namespace App\Controllers;

use App\Models\TruckDriverModel;
use App\Models\MerchantModel;
use App\Models\UserLoginModel;
use App\Models\ServiceModel;
use App\Models\ServiceCategoryModel;
use App\Models\MerchantListingModel;
use App\Models\RouteModel;
use App\Models\MasterOrderModel;
use App\Models\OrderItemModel;
use App\Models\NotificationModel;
use App\Models\ReviewModel;
use App\Models\DriverLocationHistoryModel;
use App\Models\DrivingSessionModel;
use App\Services\NotificationService;
use App\Services\CurrencyService;
use App\Models\CurrencyModel;
use App\Libraries\JWTService;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;
use League\OAuth2\Client\Provider\Google;


// also when l add a merchnt on the map as a stop l want the route line to pass by it

/**
 * @property \CodeIgniter\HTTP\IncomingRequest $request
 */
class ApiController extends Controller
{
    use ResponseTrait;

    protected $driverModel;
    protected $merchantModel;
    protected $userLoginModel;
    protected $serviceModel;
    protected $serviceCategoryModel;
    protected $merchantListingModel;
    protected $routeModel;
    protected $masterOrderModel;
    protected $orderItemModel;
    protected $notificationModel;
    protected $reviewModel;
    protected $driverLocationHistoryModel;
    protected $notificationService;
    protected $jwtService;
    protected $apiTokenModel;
    protected $currencyService;
    protected $currencyModel;
    protected $drivingSessionModel;

    public function __construct()
    {
        $this->driverModel = new \App\Models\TruckDriverModel();
        $this->merchantModel = new \App\Models\MerchantModel();
        $this->userLoginModel = new \App\Models\UserLoginModel();
        $this->serviceModel = new \App\Models\ServiceModel();
        $this->serviceCategoryModel = new \App\Models\ServiceCategoryModel();
        $this->merchantListingModel = new \App\Models\MerchantListingModel();
        $this->routeModel = new \App\Models\RouteModel();
        $this->masterOrderModel = new \App\Models\MasterOrderModel();
        $this->orderItemModel = new \App\Models\OrderItemModel();
        $this->notificationModel = new \App\Models\NotificationModel();
        $this->reviewModel = new \App\Models\ReviewModel();
        $this->driverLocationHistoryModel = new \App\Models\DriverLocationHistoryModel();
        $this->notificationService = new \App\Services\NotificationService();
        $this->currencyService = new \App\Services\CurrencyService();
        $this->currencyModel = new \App\Models\CurrencyModel();
        $this->drivingSessionModel = new \App\Models\DrivingSessionModel();
        // JWT service will be initialized when needed
        // $this->jwtService = new \App\Libraries\JWTService();
    }

    /**
     * Helper: JSON-first input accessor with form fallback
     */
    private function input(string $key, $default = null)
    {
        $json = $this->request->getJSON(true);
        if (is_array($json) && array_key_exists($key, $json)) {
            return $json[$key];
        }
        $post = $this->request->getPost($key);
        return $post !== null ? $post : $default;
    }

    /**
     * Standardized JSON response helpers
     */
    private function jsonSuccess(string $message = null, $data = null, $meta = null, int $code = 200)
    {
        $body = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];
        if ($meta !== null) {
            $body['meta'] = $meta;
        }
        return $this->response->setStatusCode($code)->setJSON($body);
    }

    private function jsonError(string $message, $errors = null, int $code = 400)
    {
        $body = [
            'success' => false,
            'message' => $message,
        ];
        if ($errors !== null) {
            $body['errors'] = $errors;
        }
        return $this->response->setStatusCode($code)->setJSON($body);
    }

    /**
     * Get authenticated driver ID from token
     * Simple token validation for now
     */
    private function getAuthenticatedDriverId()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
            return null;
        }

        $token = substr($authHeader, 7);
        
        // Simple validation - if token exists and is long enough, return test driver ID
        if ($token && strlen($token) > 10) {
            return 5; // Return the actual driver ID from the test user
        }
        
        return null;
    }

    /**
     * Test endpoint to verify API is working
     * GET /api/test
     */
    public function test()
    {
        return $this->jsonSuccess('API is working', [
            'timestamp' => date('Y-m-d H:i:s'),
            'php_version' => PHP_VERSION,
            'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'
        ]);
    }

    // ==================== AUTHENTICATION ENDPOINTS ====================

    /**
     * Driver login endpoint
     * POST /api/driver/login
     */
    public function driverLogin()
    {
        if (!$this->request->is('post')) {
            return $this->jsonError('Method not allowed', null, 405);
        }

        $rules = [
            'email' => 'required|valid_email',
            'password' => 'required'
        ];

        if (!$this->validate($rules)) {
            return $this->jsonError('Validation failed', $this->validator->getErrors(), 400);
        }

        $email = $this->input('email');
        $password = $this->input('password');

        try {
            // Check if user exists in user_logins table
            $userLogin = $this->userLoginModel->where('email', $email)
                                             ->where('user_type', 'truck_driver')
                                             ->first();

            if (!$userLogin || !password_verify($password, $userLogin['password_hash'])) {
                return $this->jsonError('Invalid email or password', null, 401);
            }

            if (!$userLogin['is_active']) {
                return $this->jsonError('Account is inactive. Please contact support.', null, 403);
            }

            // Get driver details
            $driver = $this->driverModel->find($userLogin['user_id']);
            if (!$driver) {
                return $this->jsonError('Driver profile not found', null, 404);
            }

            // Remove sensitive data
            unset($driver['password_hash']);

            // Generate simple token for now (JWT implementation needs fixing)
            $token = bin2hex(random_bytes(32));
            $refreshToken = bin2hex(random_bytes(32));

            return $this->jsonSuccess('Login successful', [
                'user' => $driver,
                'access_token' => $token,
                'refresh_token' => $refreshToken,
                'token_type' => 'Bearer',
                'expires_in' => 3600,
                'user_type' => 'driver'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Driver login failed: ' . $e->getMessage());
            return $this->jsonError('Login failed. Please try again.', null, 500);
        }
    }

    /**
     * Driver registration endpoint
     * POST /api/driver/register
     */
    public function driverRegister()
    {
        if (!$this->request->is('post')) {
            return $this->jsonError('Method not allowed', null, 405);
        }

        $rules = [
            'name' => 'required|min_length[2]|max_length[100]',
            'surname' => 'required|min_length[2]|max_length[100]',
            'email' => 'required|valid_email|is_unique[user_logins.email]',
            'password' => 'required|min_length[8]',
            'contact_number' => 'required|min_length[10]|max_length[15]',
            'vehicle_type' => 'required|max_length[50]',
            'license_number' => 'required|max_length[50]'
        ];

        if (!$this->validate($rules)) {
            return $this->jsonError('Validation failed', $this->validator->getErrors(), 400);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Create driver record
            $driverData = [
                'name' => $this->input('name'),
                'surname' => $this->input('surname'),
                'email' => $this->input('email'),
                'password' => $this->input('password'), // Will be hashed by model
                'contact_number' => $this->input('contact_number'),
                'whatsapp_number' => $this->input('whatsapp_number'),
                'country_of_residence' => $this->input('country_of_residence', 'South Africa'),
                'vehicle_type' => $this->input('vehicle_type'),
                'vehicle_registration' => $this->input('vehicle_registration'),
                'license_number' => $this->input('license_number'),
                'preferred_search_radius_km' => $this->input('preferred_search_radius_km', 50)
            ];

            $driverId = $this->driverModel->insert($driverData);

            if (!$driverId) {
                throw new \Exception('Failed to create driver profile: ' . implode(', ', $this->driverModel->errors()));
            }

            // Create user login record
            $loginData = [
                'email' => $driverData['email'],
                'password_hash' => password_hash($this->input('password'), PASSWORD_DEFAULT),
                'user_type' => 'truck_driver',
                'user_id' => $driverId,
                'is_active' => 1,
                'email_verified' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $loginId = $this->userLoginModel->insert($loginData);

            if (!$loginId) {
                throw new \Exception('Failed to create login record: ' . implode(', ', $this->userLoginModel->errors()));
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            // Get created driver (without password)
            $driver = $this->driverModel->find($driverId);
            unset($driver['password_hash']);

            // Simple response without JWT for now (to avoid JWT service issues)
            return $this->jsonSuccess('Driver registration successful', [
                'user' => $driver,
                'access_token' => 'temp_token_' . $driver['id'],
                'refresh_token' => 'temp_refresh_' . $driver['id'],
                'token_type' => 'Bearer',
                'expires_in' => 3600,
                'user_type' => 'driver'
            ], null, 201);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Driver registration failed: ' . $e->getMessage());
            return $this->jsonError('Registration failed: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Google OAuth authentication endpoint
     * GET /api/auth/google
     */
    public function googleAuth()
    {
        // Initialize Google OAuth provider
        $google = new Google([
            'clientId' => getenv('GOOGLE_CLIENT_ID'),
            'clientSecret' => getenv('GOOGLE_CLIENT_SECRET'),
            'redirectUri' => base_url('api/auth/google/callback')
        ]);

        if (!$this->request->getGet('code')) {
            // Get authorization URL
            $authUrl = $google->getAuthorizationUrl([
                'scope' => ['openid', 'profile', 'email']
            ]);

            return $this->jsonSuccess(null, ['auth_url' => $authUrl]);
        } else {
            // Handle callback
            try {
                $token = $google->getAccessToken('authorization_code', [
                    'code' => $this->request->getGet('code')
                ]);

                $user = $google->getResourceOwner($token);
                $userData = $user->toArray();

                // Check if user exists
                $existingDriver = $this->driverModel->findByGoogleId($userData['id']);

                if ($existingDriver) {
                    // User exists, log them in
                    unset($existingDriver['password_hash']);
                    $apiToken = bin2hex(random_bytes(32));

                    return $this->jsonSuccess('Google login successful', [
                        'user' => $existingDriver,
                        'api_token' => $apiToken,
                        'user_type' => 'driver'
                    ]);
                } else {
                    // New user, create account
                    $db = \Config\Database::connect();
                    $db->transStart();

                    try {
                        $driverData = [
                            'name' => $userData['given_name'] ?? '',
                            'surname' => $userData['family_name'] ?? '',
                            'email' => $userData['email'],
                            'google_id' => $userData['id'],
                            'profile_image_url' => $userData['picture'] ?? null
                        ];

                        $driverId = $this->driverModel->insert($driverData);

                        if (!$driverId) {
                            throw new \Exception('Failed to create driver profile');
                        }

                        // Create user login record
                        $loginData = [
                            'email' => $userData['email'],
                            'user_type' => 'driver',
                            'user_id' => $driverId,
                            'is_active' => 1,
                            'email_verified' => 1, // Google emails are pre-verified
                            'created_at' => date('Y-m-d H:i:s')
                        ];

                        $this->userLoginModel->insert($loginData);

                        $db->transComplete();

                        if ($db->transStatus() === false) {
                            throw new \Exception('Transaction failed');
                        }

                        $driver = $this->driverModel->find($driverId);
                        unset($driver['password_hash']);
                        $apiToken = bin2hex(random_bytes(32));

                        return $this->jsonSuccess('Google registration successful', [
                            'user' => $driver,
                            'api_token' => $apiToken,
                            'user_type' => 'driver'
                        ], null, 201);

                    } catch (\Exception $e) {
                        $db->transRollback();
                        throw $e;
                    }
                }

            } catch (\Exception $e) {
                log_message('error', 'Google auth failed: ' . $e->getMessage());
                return $this->jsonError('Google authentication failed', null, 500);
            }
        }
    }

    // ==================== DRIVER PROFILE ENDPOINTS ====================

    /**
     * Get driver profile
     * GET /api/driver/profile
     */
    public function getDriverProfile()
    {
        $driverId = $this->getAuthenticatedDriverId();
        
        if (!$driverId) {
            return $this->jsonError('Unauthorized', null, 401);
        }

        $driver = $this->driverModel->find($driverId);
        
        if (!$driver) {
            return $this->jsonError('Driver not found', null, 404);
        }

        // Remove sensitive data
        unset($driver['password_hash']);

        return $this->jsonSuccess(null, $driver);
    }

    /**
     * Update driver profile
     * POST /api/driver/profile/update
     */
    public function updateDriverProfile()
    {
        if (!$this->request->is('post')) {
            return $this->jsonError('Method not allowed', null, 405);
        }

        $driverId = $this->getAuthenticatedDriverId();
        
        if (!$driverId) {
            return $this->jsonError('Unauthorized', null, 401);
        }

        $driver = $this->driverModel->find($driverId);
        if (!$driver) {
            return $this->jsonError('Driver not found', null, 404);
        }

        $rules = [
            'name' => 'permit_empty|min_length[2]|max_length[100]',
            'surname' => 'permit_empty|min_length[2]|max_length[100]',
            'contact_number' => 'permit_empty|min_length[10]|max_length[15]',
            'whatsapp_number' => 'permit_empty|min_length[10]|max_length[15]',
            'country_of_residence' => 'permit_empty|max_length[100]',
            'vehicle_type' => 'permit_empty|max_length[50]',
            'vehicle_registration' => 'permit_empty|max_length[50]',
            'license_number' => 'permit_empty|max_length[50]',
            'preferred_search_radius_km' => 'permit_empty|integer|greater_than[0]'
        ];

        if (!$this->validate($rules)) {
            return $this->jsonError('Validation failed', $this->validator->getErrors(), 400);
        }

        $updateData = [];
        $allowedFields = ['name', 'surname', 'contact_number', 'whatsapp_number', 
                         'country_of_residence', 'vehicle_type', 'vehicle_registration', 
                         'license_number', 'preferred_search_radius_km'];

        foreach ($allowedFields as $field) {
            $value = $this->input($field);
            if ($value !== null) {
                $updateData[$field] = $value;
            }
        }

        if (empty($updateData)) {
            return $this->jsonError('No fields to update', null, 400);
        }

        if ($this->driverModel->update($driverId, $updateData)) {
            $updated = $this->driverModel->find($driverId);
            if (is_array($updated)) {
                unset($updated['password_hash']);
            }
            return $this->jsonSuccess('Profile updated successfully', $updated);
        } else {
            return $this->jsonError('Failed to update profile', $this->driverModel->errors(), 500);
        }
    }

    /**
     * POST /api/driver/password/update
     */
    public function updateDriverPassword()
    {
        if (!$this->request->is('post')) {
            return $this->jsonError('Method not allowed', null, 405);
        }

        $driverId = $this->getAuthenticatedDriverId();
        
        if (!$driverId) {
            return $this->jsonError('Unauthorized', null, 401);
        }

        $rules = [
            'current_password' => 'required',
            'new_password' => 'required|min_length[8]',
            'confirm_password' => 'required|matches[new_password]'
        ];

        if (!$this->validate($rules)) {
            return $this->jsonError('Validation failed', $this->validator->getErrors(), 400);
        }

        $driver = $this->driverModel->find($driverId);
        if (!$driver) {
            return $this->jsonError('Driver not found', null, 404);
        }

        // Verify current password
        $userLogin = $this->userLoginModel->where('user_id', $driverId)
                                         ->where('user_type', 'driver')
                                         ->first();

        if (!$userLogin || !password_verify($this->input('current_password'), $userLogin['password_hash'])) {
            return $this->jsonError('Current password is incorrect', null, 400);
        }

        // Update password
        $newPasswordHash = password_hash($this->input('new_password'), PASSWORD_DEFAULT);
        
        if ($this->userLoginModel->update($userLogin['id'], ['password_hash' => $newPasswordHash])) {
            return $this->jsonSuccess('Password updated successfully');
        } else {
            return $this->jsonError('Failed to update password', null, 500);
        }
    }

    // ==================== SERVICE DISCOVERY ENDPOINTS ====================

    /**
     * Get nearby services based on driver location
     * GET /api/services/nearby
     */
    public function getNearbyServices()
    {
        $latitude = $this->request->getGet('latitude');
        $longitude = $this->request->getGet('longitude');
        $radius = $this->request->getGet('radius') ?: 50; // Default 50km
        $category = $this->request->getGet('category');
        $limit = $this->request->getGet('limit') ?: 20;

        if (!$latitude || !$longitude) {
            return $this->jsonError('Latitude and longitude are required', null, 400);
        }

        // Get nearby merchant listings with distance calculation
        $builder = $this->merchantListingModel->select('
            merchant_listings.*,
            merchants.business_name,
            merchants.business_contact_number,
            merchants.business_whatsapp_number,
            merchants.latitude,
            merchants.longitude,
            service_categories.name as category_name,
            (6371 * acos(cos(radians(' . $latitude . ')) * cos(radians(merchants.latitude)) * 
            cos(radians(merchants.longitude) - radians(' . $longitude . ')) + 
            sin(radians(' . $latitude . ')) * sin(radians(merchants.latitude)))) AS distance
        ')
        ->join('merchants', 'merchants.id = merchant_listings.merchant_id')
        ->join('merchant_listing_categories', 'merchant_listing_categories.listing_id = merchant_listings.id', 'left')
        ->join('service_categories', 'service_categories.id = merchant_listing_categories.category_id', 'left')
        ->where('merchant_listings.status', 'approved')
        ->where('merchants.status', 'approved')
        ->having('distance <=', $radius);

        if ($category) {
            $builder->where('merchant_listing_categories.category_id', $category);
        }

        $listings = $builder->orderBy('distance', 'ASC')
                          ->limit($limit)
                          ->findAll();

        // Get images for each listing
        $listingImageModel = new \App\Models\MerchantListingImageModel();
        foreach ($listings as &$listing) {
            $images = $listingImageModel->where('listing_id', $listing['id'])->findAll();
            $listing['images'] = array_column($images, 'image_url');
            $listing['distance'] = round($listing['distance'], 2);
        }

        return $this->jsonSuccess(null, $listings, [
            'total' => count($listings),
            'radius_km' => $radius,
            'center' => ['lat' => $latitude, 'lng' => $longitude]
        ]);
    }

    /**
     * Search services by query
     * GET /api/services/search
     */
    public function searchServices()
    {
        $query = $this->request->getGet('q');
        $latitude = $this->request->getGet('latitude');
        $longitude = $this->request->getGet('longitude');
        $radius = $this->request->getGet('radius') ?: 10;
        $category = $this->request->getGet('category');
        $limit = $this->request->getGet('limit') ?: 20;
        $offset = $this->request->getGet('offset') ?: 0;

        if (!$query) {
            return $this->jsonError('Search query is required', null, 400);
        }

        $builder = $this->merchantListingModel->select('
            merchant_listings.*,
            merchants.business_name,
            merchants.business_contact_number,
            merchants.business_whatsapp_number,
            merchants.latitude,
            merchants.longitude,
            service_categories.name as category_name
        ')
        ->join('merchants', 'merchants.id = merchant_listings.merchant_id')
        ->join('merchant_listing_categories', 'merchant_listing_categories.listing_id = merchant_listings.id', 'left')
        ->join('service_categories', 'service_categories.id = merchant_listing_categories.category_id', 'left')
        ->where('merchant_listings.status', 'approved')
        ->where('merchants.status', 'approved')
        ->where('merchants.is_visible', 1)
        ->groupStart()
            ->like('merchant_listings.title', $query)
            ->orLike('merchant_listings.description', $query)
            ->orLike('merchants.business_name', $query)
            ->orLike('service_categories.name', $query)
        ->groupEnd();

        // Add location-based filtering if coordinates provided
        if ($latitude && $longitude) {
            $builder->select('(6371 * acos(cos(radians(' . $latitude . ')) * cos(radians(merchants.latitude)) * 
                cos(radians(merchants.longitude) - radians(' . $longitude . ')) + 
                sin(radians(' . $latitude . ')) * sin(radians(merchants.latitude)))) AS distance', false)
                ->having('distance <=', $radius)
                ->orderBy('distance', 'ASC');
        }

        if ($category) {
            $builder->where('merchant_listing_categories.category_id', $category);
        }

        $listings = $builder->limit($limit, $offset)->findAll();

        // Get images for each listing
        $listingImageModel = new \App\Models\MerchantListingImageModel();
        foreach ($listings as &$listing) {
            $images = $listingImageModel->where('listing_id', $listing['id'])->findAll();
            $listing['images'] = array_column($images, 'image_url');
            if (isset($listing['distance'])) {
                $listing['distance'] = round($listing['distance'], 2);
            }
        }

        return $this->jsonSuccess(null, $listings, [
            'total' => count($listings),
            'query' => $query,
            'limit' => $limit,
            'offset' => $offset
        ]);
    }

    /**
     * Get all service categories
     * GET /api/services/categories
     */
    public function getServiceCategories()
    {
        $categories = $this->serviceCategoryModel->select('id, name, description')
                                               ->orderBy('name', 'ASC')
                                               ->findAll();

        return $this->jsonSuccess(null, $categories);
    }

    /**
     * Get all services without location restrictions
     * GET /api/services/all
     */
    public function getAllServices()
    {
        $limit = $this->request->getGet('limit') ?: 50;
        $offset = $this->request->getGet('offset') ?: 0;
        $category = $this->request->getGet('category');

        // Get all merchant listings
        $builder = $this->merchantListingModel->select('
            merchant_listings.*,
            merchants.business_name,
            merchants.business_contact_number,
            merchants.business_whatsapp_number,
            merchants.latitude,
            merchants.longitude,
            service_categories.name as category_name
        ')
        ->join('merchants', 'merchants.id = merchant_listings.merchant_id')
        ->join('merchant_listing_categories', 'merchant_listing_categories.listing_id = merchant_listings.id', 'left')
        ->join('service_categories', 'service_categories.id = merchant_listing_categories.category_id', 'left')
        ->where('merchant_listings.status', 'approved')
        ->where('merchants.status', 'approved');

        if ($category) {
            $builder->where('merchant_listing_categories.category_id', $category);
        }

        $listings = $builder->orderBy('merchant_listings.created_at', 'DESC')
                          ->limit($limit, $offset)
                          ->findAll();

        // Get images for each listing
        $listingImageModel = new \App\Models\MerchantListingImageModel();
        foreach ($listings as &$listing) {
            $images = $listingImageModel->where('listing_id', $listing['id'])->findAll();
            $listing['images'] = array_map(function($img) {
                return base_url('uploads/listings/' . $img['image_path']);
            }, $images);
        }

        return $this->jsonSuccess('All services retrieved successfully', [
            'services' => $listings,
            'total' => count($listings),
            'limit' => $limit,
            'offset' => $offset
        ]);
    }

    /**
     * Get specific listing details
     * GET /api/listings/{id}
     */
    public function getListingDetails($listingId)
    {
        $listing = $this->merchantListingModel->select('
            merchant_listings.*,
            merchants.business_name,
            merchants.business_contact_number,
            merchants.business_whatsapp_number,
            merchants.latitude,
            merchants.longitude,
            merchants.business_address,
            merchants.profile_image_url,
            service_categories.name as category_name
        ')
        ->join('merchants', 'merchants.id = merchant_listings.merchant_id')
        ->join('merchant_listing_categories', 'merchant_listing_categories.listing_id = merchant_listings.id', 'left')
        ->join('service_categories', 'service_categories.id = merchant_listing_categories.category_id', 'left')
        ->where('merchant_listings.id', $listingId)
        ->where('merchant_listings.status', 'approved')
        ->first();

        if (!$listing) {
            return $this->jsonError('Listing not found', null, 404);
        }

        // Get listing images
        $listingImageModel = new \App\Models\MerchantListingImageModel();
        $images = $listingImageModel->where('listing_id', $listingId)->findAll();
        $listing['images'] = array_column($images, 'image_url');

        // Get merchant reviews
        $reviews = $this->reviewModel->select('
            reviews.*,
            truck_drivers.name as reviewer_name
        ')
        ->join('truck_drivers', 'truck_drivers.id = reviews.driver_id')
        ->where('reviews.merchant_id', $listing['merchant_id'])
        ->orderBy('reviews.created_at', 'DESC')
        ->limit(10)
        ->findAll();

        $listing['reviews'] = $reviews;
        $listing['average_rating'] = count($reviews) > 0 ? round(array_sum(array_column($reviews, 'rating')) / count($reviews), 1) : 0;

        return $this->jsonSuccess(null, $listing);
    }

    /**
     * Get merchant profile details
     * GET /api/merchants/{id}
     */
    public function getMerchantDetails($merchantId)
    {
        $merchant = $this->merchantModel->select('
            merchants.*,
            COUNT(merchant_listings.id) as total_listings
        ')
        ->join('merchant_listings', 'merchant_listings.merchant_id = merchants.id AND merchant_listings.status = "approved"', 'left')
        ->where('merchants.id', $merchantId)
        ->where('merchants.status', 'approved')
        ->where('merchants.is_visible', 1)
        ->groupBy('merchants.id')
        ->first();

        if (!$merchant) {
            return $this->jsonError('Merchant not found or not visible', null, 404);
        }

        // Get merchant listings
        $listings = $this->merchantListingModel->select('
            merchant_listings.*,
            service_categories.name as category_name
        ')
        ->join('merchant_listing_categories', 'merchant_listing_categories.listing_id = merchant_listings.id', 'left')
        ->join('service_categories', 'service_categories.id = merchant_listing_categories.category_id', 'left')
        ->where('merchant_listings.merchant_id', $merchantId)
        ->where('merchant_listings.status', 'approved')
        ->findAll();

        // Get images for each listing
        $listingImageModel = new \App\Models\MerchantListingImageModel();
        foreach ($listings as &$listing) {
            $images = $listingImageModel->where('listing_id', $listing['id'])->findAll();
            $listing['images'] = array_column($images, 'image_url');
        }

        // Get merchant reviews
        $reviews = $this->reviewModel->select('
            reviews.*,
            truck_drivers.name as reviewer_name
        ')
        ->join('truck_drivers', 'truck_drivers.id = reviews.driver_id')
        ->where('reviews.merchant_id', $merchantId)
        ->orderBy('reviews.created_at', 'DESC')
        ->findAll();

        $merchant['listings'] = $listings;
        $merchant['reviews'] = $reviews;
        $merchant['average_rating'] = count($reviews) > 0 ? round(array_sum(array_column($reviews, 'rating')) / count($reviews), 1) : 0;
        $merchant['total_reviews'] = count($reviews);

        return $this->jsonSuccess(null, $merchant);
    }

    // ==================== ROUTE PLANNING ENDPOINTS ====================

    /**
     * Create a new route
     * POST /api/routes/create
     */
    public function createRoute()
    {
        if (!$this->request->is('post')) {
            return $this->jsonError('Method not allowed', null, 405);
        }

        $driverId = $this->getAuthenticatedDriverId();
        
        if (!$driverId) {
            return $this->jsonError('Unauthorized', null, 401);
        }

        $rules = [
            'route_name' => 'required|max_length[255]',
            'origin_address' => 'required|max_length[500]',
            'destination_address' => 'required|max_length[500]',
            'origin_lat' => 'required|decimal',
            'origin_lng' => 'required|decimal',
            'destination_lat' => 'required|decimal',
            'destination_lng' => 'required|decimal'
        ];

        if (!$this->validate($rules)) {
            return $this->jsonError('Validation failed', $this->validator->getErrors(), 400);
        }

        $routeData = [
            'truck_driver_id' => $driverId,
            'route_name' => $this->input('route_name'),
            'start_address' => $this->input('origin_address'),
            'end_address' => $this->input('destination_address'),
            'start_lat' => $this->input('origin_lat'),
            'start_lng' => $this->input('origin_lng'),
            'end_lat' => $this->input('destination_lat'),
            'end_lng' => $this->input('destination_lng'),
            'total_distance_km' => $this->input('distance_km'),
            'estimated_duration_minutes' => $this->input('estimated_duration'),
            'route_polyline' => $this->input('route_polyline'),
            'is_saved' => $this->input('is_saved', 0)
        ];
        
        $routeId = $this->routeModel->insert($routeData);

        if (!$routeId) {
            return $this->jsonError('Failed to create route', $this->routeModel->errors(), 500);
        }

        $route = $this->routeModel->find($routeId);

        return $this->jsonSuccess('Route created successfully', $route, null, 201);
    }

    /**
     * Get all routes for a driver
     * GET /api/routes
     */
    public function getRoutes()
    {
        $driverId = $this->getAuthenticatedDriverId();
        if (!$driverId) {
            return $this->jsonError('Unauthorized', null, 401);
        }

        $savedOnly = $this->request->getGet('saved_only');
        $savedOnly = ($savedOnly === 'true' || $savedOnly === '1' || $savedOnly === 1 || $savedOnly === true);
        $limit = (int)$this->request->getGet('limit', 50);
        $offset = (int)$this->request->getGet('offset', 0);

        $builder = $this->routeModel->where('truck_driver_id', $driverId);

        if ($savedOnly) {
            $builder->where('is_saved', 1);
        }

        $routes = $builder->orderBy('created_at', 'DESC')
                         ->limit($limit, $offset)
                         ->findAll();

        return $this->jsonSuccess(null, $routes, [
            'total' => count($routes),
            'limit' => $limit,
            'offset' => $offset
        ]);
    }

    /**
     * Get specific route details
     * GET /api/routes/{id}
     */
    public function getRouteDetails($routeId)
    {
        $driverId = $this->getAuthenticatedDriverId();
        if (!$driverId) {
            return $this->jsonError('Unauthorized', null, 401);
        }

        $route = $this->routeModel->where('id', $routeId)
                                 ->where('truck_driver_id', $driverId)
                                 ->first();

        if (!$route) {
            return $this->jsonError('Route not found', null, 404);
        }

        // Get merchants along route if available
        $routeMerchantModel = new \App\Models\RouteMerchantModel();
        $routeMerchants = $routeMerchantModel->select('
            route_merchants.*,
            merchants.business_name,
            merchants.latitude,
            merchants.longitude,
            merchants.contact_number
        ')
        ->join('merchants', 'merchants.id = route_merchants.merchant_id')
        ->where('route_merchants.route_id', $routeId)
        ->orderBy('route_merchants.stop_order', 'ASC')
        ->findAll();

        $route['merchants'] = $routeMerchants;

        return $this->jsonSuccess(null, $route);
    }

    /**
     * Delete a route
     * DELETE /api/routes/{id}
     */
    public function deleteRoute($routeId)
    {
        if (!$this->request->is('delete')) {
            return $this->jsonError('Method not allowed', null, 405);
        }

        $driverId = $this->getAuthenticatedDriverId();
        if (!$driverId) {
            return $this->jsonError('Unauthorized', null, 401);
        }

        $route = $this->routeModel->where('id', $routeId)
                                 ->where('truck_driver_id', $driverId)
                                 ->first();

        if (!$route) {
            return $this->jsonError('Route not found', null, 404);
        }

        if ($this->routeModel->delete($routeId)) {
            return $this->jsonSuccess('Route deleted successfully');
        } else {
            return $this->jsonError('Failed to delete route', null, 500);
        }
    }

    // ==================== ORDER ENDPOINTS ====================

    /**
     * Place an order
     * POST /api/orders/place
     */
    public function placeOrder()
    {
        if (!$this->request->is('post')) {
            return $this->jsonError('Method not allowed', null, 405);
        }

        $driverId = $this->getAuthenticatedDriverId();
        if (!$driverId) {
            return $this->jsonError('Unauthorized', null, 401);
        }

        // Get cart data from request body
        $cartData = $this->request->getJSON(true);
        
        if (empty($cartData) || !isset($cartData['items'])) {
            return $this->jsonError('Cart items are required', null, 400);
        }

        $rules = [
            'estimated_arrival' => 'permit_empty|valid_date',
            'vehicle_info' => 'permit_empty|max_length[255]',
            'special_instructions' => 'permit_empty|max_length[1000]'
        ];

        if (!$this->validate($rules)) {
            return $this->jsonError('Validation failed', $this->validator->getErrors(), 400);
        }

        // Group cart items by merchant_id
        $itemsByMerchant = [];
        foreach ($cartData['items'] as $item) {
            $merchantId = $item['merchant_id'] ?? null;
            if (!$merchantId) {
                return $this->jsonError('All items must have a merchant_id', null, 400);
            }
            
            if (!isset($itemsByMerchant[$merchantId])) {
                $itemsByMerchant[$merchantId] = [];
            }
            $itemsByMerchant[$merchantId][] = $item;
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $createdOrders = [];
            $baseBookingRef = 'TA' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $orderCounter = 1;
            
            // Create separate master order for each merchant
            foreach ($itemsByMerchant as $merchantId => $merchantItems) {
                // Calculate total for this merchant's items
                $merchantTotal = 0;
                foreach ($merchantItems as $item) {
                    $merchantTotal += ($item['price'] ?? 0) * ($item['quantity'] ?? 1);
                }
                
                // Create unique booking reference for this merchant
                $bookingReference = $baseBookingRef . '-' . chr(64 + $orderCounter); // A, B, C, etc.
                
                $masterOrderData = [
                    'driver_id' => (int)$driverId,
                    'booking_reference' => $bookingReference,
                    'grand_total' => (float)$merchantTotal,
                    'order_status' => 'pending',
                    'estimated_arrival' => $this->input('estimated_arrival'),
                    'vehicle_model' => $this->input('vehicle_info'),
                    'terms_accepted' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $masterOrderId = $this->masterOrderModel->insert($masterOrderData);
                
                if (!$masterOrderId) {
                    throw new \Exception('Failed to create order for merchant ' . $merchantId);
                }
                
                // Create order items for this merchant
                foreach ($merchantItems as $item) {
                    $orderItemData = [
                        'master_order_id' => (int)$masterOrderId,
                        'listing_id' => (int)$item['id'],
                        'merchant_id' => (int)$merchantId,
                        'quantity' => (int)($item['quantity'] ?? 1),
                        'price' => (float)($item['price'] ?? 0),
                        'total_cost' => (float)(($item['price'] ?? 0) * ($item['quantity'] ?? 1)),
                        'item_status' => 'pending',
                        'status' => 'pending'
                    ];
                    
                    $itemResult = $this->orderItemModel->insert($orderItemData);
                    
                    if (!$itemResult) {
                        throw new \Exception('Failed to create order item');
                    }
                }
                
                // Store order info for response
                $createdOrders[] = [
                    'id' => $masterOrderId,
                    'booking_reference' => $bookingReference,
                    'merchant_id' => $merchantId,
                    'total' => $merchantTotal,
                    'items_count' => count($merchantItems)
                ];
                
                // Send notification to merchant
                $orderData = [
                    'id' => $masterOrderId,
                    'booking_reference' => $bookingReference,
                    'grand_total' => $merchantTotal,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                $this->notificationService->notifyOrderPlaced($masterOrderId, $driverId, [$merchantId], $orderData);
                
                $orderCounter++;
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            // Calculate grand total across all orders
            $grandTotal = array_sum(array_column($createdOrders, 'total'));
            
            return $this->jsonSuccess('Orders placed successfully!', [
                'orders_created' => count($createdOrders),
                'orders' => $createdOrders,
                'grand_total' => $grandTotal,
            ], null, 201);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Order placement failed: ' . $e->getMessage());
            return $this->jsonError('Failed to place order: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Get driver's order history
     * GET /api/orders/my-history
     */
    public function getOrderHistory()
    {
        $driverId = $this->getAuthenticatedDriverId();
        if (!$driverId) {
            return $this->jsonError('Unauthorized', null, 401);
        }

        $limit = $this->request->getGet('limit', 20);
        $offset = $this->request->getGet('offset', 0);
        $status = $this->request->getGet('status'); // pending, accepted, completed, rejected

        // Get all orders with merchant information
        $builder = $this->masterOrderModel->select('
            master_orders.*,
            merchants.business_name,
            merchants.profile_image_url as merchant_image,
            COUNT(order_items.id) as items_count
        ')
        ->join('order_items', 'order_items.master_order_id = master_orders.id')
        ->join('merchants', 'merchants.id = order_items.merchant_id')
        ->where('master_orders.driver_id', $driverId)
        ->groupBy('master_orders.id');

        if ($status) {
            $builder->where('master_orders.order_status', $status);
        }

        $orders = $builder->orderBy('master_orders.created_at', 'DESC')
                         ->limit($limit, $offset)
                         ->findAll();

        // Group orders by checkout session
        $groupedOrders = [];
        foreach ($orders as $order) {
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
                    'overall_status' => 'pending'
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

        return $this->jsonSuccess(null, array_values($groupedOrders), [
            'total' => count($groupedOrders),
            'limit' => $limit,
            'offset' => $offset
        ]);
    }

    // ==================== NOTIFICATION ENDPOINTS ====================

    /**
     * Get driver notifications
     * GET /api/notifications
     */
    public function getNotifications()
    {
        $driverId = $this->getAuthenticatedDriverId();
        if (!$driverId) {
            return $this->jsonError('Unauthorized', null, 401);
        }

        $limit = $this->request->getGet('limit', 50);
        $offset = $this->request->getGet('offset', 0);
        $unreadOnly = $this->request->getGet('unread_only', false);

        $notifications = $this->notificationModel->getNotificationsForUser('driver', $driverId, $limit, $unreadOnly, $offset);
        $totalCount = $this->notificationModel->getTotalNotificationsForUser('driver', $driverId, $unreadOnly);
        $unreadCount = $this->notificationModel->getUnreadCount('driver', $driverId);

        return $this->jsonSuccess(null, $notifications, [
            'total' => $totalCount,
            'unread_count' => $unreadCount,
            'limit' => $limit,
            'offset' => $offset
        ]);
    }

    /**
     * Mark notification as read
     * POST /api/notifications/mark-read
     */
    public function markNotificationRead()
    {
        if (!$this->request->is('post')) {
            return $this->jsonError('Method not allowed', null, 405);
        }

        $driverId = $this->getAuthenticatedDriverId();
        if (!$driverId) {
            return $this->jsonError('Unauthorized', null, 401);
        }

        $notificationId = $this->input('notification_id');
        $markAll = $this->input('mark_all', false);

        if ($markAll) {
            // Mark all notifications as read
            if ($this->notificationModel->markAllAsRead('driver', $driverId)) {
                return $this->jsonSuccess('All notifications marked as read');
            } else {
                return $this->jsonError('Failed to mark notifications as read', null, 500);
            }
        } else {
            if (!$notificationId) {
                return $this->jsonError('Notification ID is required', null, 400);
            }

            // Verify the notification belongs to this driver
            $notification = $this->notificationModel->where('id', $notificationId)
                                                  ->where('recipient_type', 'driver')
                                                  ->where('recipient_id', $driverId)
                                                  ->first();

            if (!$notification) {
                return $this->jsonError('Notification not found', null, 404);
            }

            if ($this->notificationModel->markAsRead($notificationId)) {
                return $this->jsonSuccess('Notification marked as read');
            } else {
                return $this->jsonError('Failed to mark notification as read', null, 500);
            }
        }
    }

    // ==================== REVIEW ENDPOINTS ====================

    /**
     * Submit a review for a merchant
     * POST /api/merchants/{id}/review
     */
    public function submitMerchantReview($merchantId)
    {
        if (!$this->request->is('post')) {
            return $this->jsonError('Method not allowed', null, 405);
        }

        $driverId = $this->getAuthenticatedDriverId();
        if (!$driverId) {
            return $this->jsonError('Unauthorized', null, 401);
        }

        $rules = [
            'rating' => 'required|integer|greater_than[0]|less_than_equal_to[5]',
            'review_text' => 'permit_empty|max_length[1000]',
            'order_id' => 'permit_empty|integer'
        ];

        if (!$this->validate($rules)) {
            return $this->jsonError('Validation failed', $this->validator->getErrors(), 400);
        }

        // Check if merchant exists
        $merchant = $this->merchantModel->find($merchantId);
        if (!$merchant) {
            return $this->jsonError('Merchant not found', null, 404);
        }

        // Check if driver has already reviewed this merchant
        $existingReview = $this->reviewModel->where('merchant_id', $merchantId)
                                          ->where('driver_id', $driverId)
                                          ->first();

        if ($existingReview) {
            return $this->jsonError('You have already reviewed this merchant', null, 409);
        }

        $reviewData = [
            'merchant_id' => $merchantId,
            'driver_id' => $driverId,
            'rating' => $this->input('rating'),
            'review_text' => $this->input('review_text'),
            'order_id' => $this->input('order_id'),
            'status' => 'approved', // Auto-approve for now
            'created_at' => date('Y-m-d H:i:s')
        ];

        $reviewId = $this->reviewModel->insert($reviewData);

        if (!$reviewId) {
            return $this->jsonError('Failed to submit review', $this->reviewModel->errors(), 500);
        }

        $review = $this->reviewModel->find($reviewId);

        return $this->jsonSuccess('Review submitted successfully', $review, null, 201);
    }

    // ==================== LOCATION TRACKING ENDPOINT ====================

    /**
     * Update driver location (keeping existing endpoint)
     * POST /api/driver/location
     */
    public function updateDriverLocation()
    {
        if (!$this->request->is('post')) {
            return $this->jsonError('Method not allowed', null, 405);
        }

        $driverId = $this->getAuthenticatedDriverId();
        if (!$driverId) {
            return $this->jsonError('Unauthorized', null, 401);
        }

        $rules = [
            'latitude' => 'required|decimal',
            'longitude' => 'required|decimal'
        ];

        if (!$this->validate($rules)) {
            return $this->jsonError('Validation failed', $this->validator->getErrors(), 400);
        }

        $latitude = $this->input('latitude');
        $longitude = $this->input('longitude');

        // Update driver's current location
        $updateData = [
            'current_latitude' => $latitude,
            'current_longitude' => $longitude,
            'last_location_update' => date('Y-m-d H:i:s')
        ];

        if ($this->driverModel->update($driverId, $updateData)) {
            // Also save to location history
            $historyData = [
                'driver_id' => $driverId,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'recorded_at' => date('Y-m-d H:i:s')
            ];

            $this->driverLocationHistoryModel->insert($historyData);

            return $this->jsonSuccess('Location updated successfully');
        } else {
            return $this->jsonError('Failed to update location', null, 500);
        }
    }


    /**
     * Validate JWT token and get driver ID
     */
    private function authenticateDriver(): int|false
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        
        if (!$authHeader) {
            return false;
        }

        // Extract Bearer token
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return false;
        }

        $token = $matches[1];

        // Skip JWT validation if jwtService is not initialized
        // In non-JWT mode, just validate that token exists and return test driver ID
        if (empty($this->jwtService) || empty($this->apiTokenModel)) {
            if ($token && strlen($token) > 10) {
                return 1; // Test driver ID
            }
            return false;
        }

        // Validate JWT token (only when JWT is properly initialized)
        $payload = $this->jwtService->validateToken($token);
        
        if (!$payload) {
            return false;
        }

        // Verify token exists in database and is active
        $tokenHash = hash('sha256', $token);
        $tokenRecord = $this->apiTokenModel->validateTokenHash($tokenHash);
        
        if (!$tokenRecord) {
            return false;
        }

        // Return driver ID from token payload
        return $payload['data']['driver_id'] ?? false;
    }

    /**
     * Refresh JWT token endpoint
     * POST /api/auth/refresh
     */
    public function refreshToken()
    {
        if (!$this->request->is('post')) {
            return $this->jsonError('Method not allowed', null, 405);
        }

        $refreshToken = $this->input('refresh_token');
        if (!$refreshToken) {
            return $this->jsonError('Refresh token is required', null, 400);
        }

        // Temporary fallback when JWT/Token storage is disabled
        // If jwtService or apiTokenModel are not initialized, generate simple tokens
        if (empty($this->jwtService) || empty($this->apiTokenModel)) {
            $newAccessToken = bin2hex(random_bytes(32));
            $newRefreshToken = bin2hex(random_bytes(32));
            $accessTtl = 3600;

            return $this->jsonSuccess('Token refreshed successfully', [
                'access_token' => $newAccessToken,
                'refresh_token' => $newRefreshToken,
                'token_type' => 'Bearer',
                'expires_in' => $accessTtl,
            ]);
        }

        // Validate refresh token
        $refreshTokenHash = hash('sha256', $refreshToken);
        $tokenRecord = $this->apiTokenModel->where('refresh_token_hash', $refreshTokenHash)
                                          ->where('is_active', 1)
                                          ->where('refresh_expires_at >', date('Y-m-d H:i:s'))
                                          ->first();

        if (!$tokenRecord) {
            return $this->jsonError('Invalid or expired refresh token', null, 401);
        }

        // Get driver data
        $driver = $this->driverModel->find($tokenRecord['driver_id']);
        if (!$driver) {
            return $this->jsonError('Driver not found', null, 404);
        }

        // Generate new tokens
        $newJwtToken = $this->jwtService->generateToken($driver);
        $newRefreshToken = $this->jwtService->generateRefreshToken($driver['id']);

        // Update token record
        $accessTtl = (int) (getenv('JWT_ACCESS_TTL') ?: 3600);
        $this->apiTokenModel->update($tokenRecord['id'], [
            'token_hash' => hash('sha256', $newJwtToken),
            'refresh_token_hash' => hash('sha256', $newRefreshToken),
            'expires_at' => date('Y-m-d H:i:s', time() + $accessTtl),
            'refresh_expires_at' => date('Y-m-d H:i:s', time() + 7776000),
            'last_used_at' => date('Y-m-d H:i:s')
        ]);

        return $this->jsonSuccess('Token refreshed successfully', [
            'access_token' => $newJwtToken,
            'refresh_token' => $newRefreshToken,
            'token_type' => 'Bearer',
            'expires_in' => $accessTtl
        ]);
    }

    /**
     * Logout endpoint - revoke token
     * POST /api/auth/logout
     */
    public function logout()
    {
        if (!$this->request->is('post')) {
            return $this->jsonError('Method not allowed', null, 405);
        }

        $driverId = $this->authenticateDriver();
        if (!$driverId) {
            return $this->jsonError('Invalid token', null, 401);
        }

        // Revoke all tokens for this driver
        $this->apiTokenModel->revokeAllDriverTokens($driverId);

        return $this->jsonSuccess('Logged out successfully');
    }

    // ==================== CURRENCY ENDPOINTS ====================

    /**
     * Get supported currencies
     * GET /api/currencies
     */
    public function getSupportedCurrencies()
    {
        $currencies = $this->currencyModel->getAllActive(); // Show all active currencies including African currencies

        return $this->jsonSuccess(null, $currencies);
    }

    /**
     * Get exchange rate between two currencies
     * GET /api/currency/exchange-rate
     */
    public function getExchangeRate()
    {
        $fromCurrency = $this->request->getGet('from');
        $toCurrency = $this->request->getGet('to');

        if (!$fromCurrency || !$toCurrency) {
            return $this->jsonError('Both from and to currencies are required', null, 400);
        }

        $rate = $this->currencyService->getExchangeRate($fromCurrency, $toCurrency);

        if ($rate === null) {
            return $this->jsonError('Exchange rate not available for the specified currencies', null, 404);
        }

        return $this->jsonSuccess(null, [
            'from_currency' => strtoupper($fromCurrency),
            'to_currency' => strtoupper($toCurrency),
            'exchange_rate' => $rate,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Convert amount between currencies
     * POST /api/currency/convert
     */
    public function convertCurrency()
    {
        if (!$this->request->is('post')) {
            return $this->jsonError('Method not allowed', null, 405);
        }

        $rules = [
            'amount' => 'required|decimal|greater_than[0]',
            'from_currency' => 'required|alpha|exact_length[3]',
            'to_currency' => 'required|alpha|exact_length[3]'
        ];

        if (!$this->validate($rules)) {
            return $this->jsonError('Validation failed', $this->validator->getErrors(), 400);
        }

        $amount = (float) $this->input('amount');
        $fromCurrency = strtoupper($this->input('from_currency'));
        $toCurrency = strtoupper($this->input('to_currency'));

        $convertedAmount = $this->currencyService->convertAmount($amount, $fromCurrency, $toCurrency);

        if ($convertedAmount === null) {
            return $this->jsonError('Currency conversion failed. Exchange rate not available.', null, 404);
        }

        $rate = $this->currencyService->getExchangeRate($fromCurrency, $toCurrency);

        return $this->jsonSuccess('Currency converted successfully', [
            'original_amount' => $amount,
            'from_currency' => $fromCurrency,
            'converted_amount' => $convertedAmount,
            'to_currency' => $toCurrency,
            'exchange_rate' => $rate,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Update driver's currency preference
     * POST /api/driver/currency/update
     */
    public function updateDriverCurrencyPreference()
    {
        if (!$this->request->is('post')) {
            return $this->jsonError('Method not allowed', null, 405);
        }

        $driverId = $this->getAuthenticatedDriverId();
        if (!$driverId) {
            return $this->jsonError('Unauthorized', null, 401);
        }

        $rules = [
            'preferred_currency' => 'required|alpha|exact_length[3]'
        ];

        if (!$this->validate($rules)) {
            return $this->jsonError('Validation failed', $this->validator->getErrors(), 400);
        }

        $currencyCode = strtoupper($this->input('preferred_currency'));

        // Validate currency code
        if (!$this->currencyModel->isValidCurrency($currencyCode)) {
            return $this->jsonError('Invalid currency code', null, 400);
        }

        // Update driver's currency preference
        if ($this->driverModel->update($driverId, ['preferred_currency' => $currencyCode])) {
            $driver = $this->driverModel->find($driverId);
            unset($driver['password_hash']);

            return $this->jsonSuccess('Currency preference updated successfully', [
                'user' => $driver,
                'preferred_currency' => $currencyCode
            ]);
        } else {
            return $this->jsonError('Failed to update currency preference', null, 500);
        }
    }

    /**
     * Get currency information
     * GET /api/currency/info/{currency_code}
     */
    public function getCurrencyInfo($currencyCode)
    {
        $currencyCode = strtoupper($currencyCode);
        $currencyInfo = $this->currencyService->getCurrencyInfo($currencyCode);

        if (!$currencyInfo) {
            return $this->jsonError('Currency not found', null, 404);
        }

        return $this->jsonSuccess(null, $currencyInfo);
    }

    // ==================== ROUTE MANAGEMENT ENDPOINTS ====================

    /**
     * Toggle saved status of a route
     * POST /api/routes/{id}/toggle-saved
     */
    public function toggleRouteSaved($routeId)
    {
        if (!$this->request->is('post')) {
            return $this->jsonError('Method not allowed', null, 405);
        }

        $driverId = $this->getAuthenticatedDriverId();
        if (!$driverId) {
            return $this->jsonError('Unauthorized', null, 401);
        }

        $success = $this->routeModel->toggleSaved($routeId, $driverId);

        if ($success) {
            // Get updated route to return current status
            $route = $this->routeModel->where('truck_driver_id', $driverId)->find($routeId);
            if (!$route) {
                return $this->jsonError('Route not found', null, 404);
            }

            $message = $route['is_saved'] ? 'Route saved successfully!' : 'Route removed from saved routes';

            return $this->jsonSuccess($message, [
                'route_id' => (int)$routeId,
                'is_saved' => (bool)$route['is_saved']
            ]);
        } else {
            return $this->jsonError('Failed to toggle route saved status', null, 500);
        }
    }

    /**
     * Get recent routes for dashboard
     * GET /api/driver/recent-routes
     */
    public function getRecentRoutes()
    {
        $driverId = $this->getAuthenticatedDriverId();
        if (!$driverId) {
            return $this->jsonError('Unauthorized', null, 401);
        }

        $limit = $this->request->getGet('limit', 5);

        $routes = $this->routeModel->findByDriverId($driverId);
        $recentRoutes = array_slice($routes, 0, $limit);

        return $this->jsonSuccess(null, $recentRoutes);
    }

    // ==================== LOCATION HISTORY ENDPOINTS ====================

    /**
     * Get driver's location history
     * GET /api/driver/location/history
     */
    public function getLocationHistory()
    {
        $driverId = $this->getAuthenticatedDriverId();
        if (!$driverId) {
            return $this->jsonError('Unauthorized', null, 401);
        }

        $limit = $this->request->getGet('limit', 100);
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');

        if ($startDate && $endDate) {
            $locations = $this->driverLocationHistoryModel->getLocationsBetweenDates($driverId, $startDate, $endDate);
        } else {
            $locations = $this->driverLocationHistoryModel->getDriverLocationHistory($driverId, $limit);
        }

        return $this->jsonSuccess(null, $locations, [
            'total' => count($locations),
            'limit' => $limit
        ]);
    }

    // ==================== DRIVING SESSION ENDPOINTS ====================

    /**
     * Start a new driving session
     * POST /api/driver/session/start
     */
    public function startDrivingSession()
    {
        if (!$this->request->is('post')) {
            return $this->jsonError('Method not allowed', null, 405);
        }

        $driverId = $this->getAuthenticatedDriverId();
        if (!$driverId) {
            return $this->jsonError('Unauthorized', null, 401);
        }

        // Check if there's already an active session
        $activeSession = $this->drivingSessionModel->findActiveByDriverId($driverId);
        if ($activeSession) {
            return $this->jsonError('Driver already has an active session', [
                'active_session' => $activeSession
            ], 400);
        }

        $sessionId = $this->drivingSessionModel->start($driverId);

        if ($sessionId) {
            $session = $this->drivingSessionModel->find($sessionId);
            return $this->jsonSuccess('Driving session started successfully', $session);
        } else {
            return $this->jsonError('Failed to start driving session', null, 500);
        }
    }

    /**
     * Get current active driving session
     * GET /api/driver/session/current
     */
    public function getCurrentDrivingSession()
    {
        $driverId = $this->getAuthenticatedDriverId();
        if (!$driverId) {
            return $this->jsonError('Unauthorized', null, 401);
        }

        $activeSession = $this->drivingSessionModel->findActiveByDriverId($driverId);

        if ($activeSession) {
            return $this->jsonSuccess(null, $activeSession);
        } else {
            return $this->jsonError('No active driving session found', null, 404);
        }
    }

    /**
     * Update driving session status
     * POST /api/driver/session/{id}/status
     */
    public function updateDrivingSessionStatus($sessionId)
    {
        if (!$this->request->is('post')) {
            return $this->jsonError('Method not allowed', null, 405);
        }

        $driverId = $this->getAuthenticatedDriverId();
        if (!$driverId) {
            return $this->jsonError('Unauthorized', null, 401);
        }

        $rules = [
            'status' => 'required|in_list[paused,completed]'
        ];

        if (!$this->validate($rules)) {
            return $this->jsonError('Validation failed', $this->validator->getErrors(), 400);
        }

        $status = $this->input('status');

        // Verify session belongs to driver
        $session = $this->drivingSessionModel->find($sessionId);
        if (!$session || $session['truck_driver_id'] != $driverId) {
            return $this->jsonError('Session not found', null, 404);
        }

        $success = $this->drivingSessionModel->updateStatus($sessionId, $status);

        if ($success) {
            $updatedSession = $this->drivingSessionModel->find($sessionId);
            $message = $status === 'completed' ? 'Driving session completed' : 'Driving session paused';
            return $this->jsonSuccess($message, $updatedSession);
        } else {
            return $this->jsonError('Failed to update session status', null, 500);
        }
    }

    // ==================== DASHBOARD ENDPOINTS ====================

    /**
     * Get driver dashboard statistics
     * GET /api/driver/dashboard/stats
     */
    public function getDashboardStats()
    {
        $driverId = $this->getAuthenticatedDriverId();
        if (!$driverId) {
            return $this->jsonError('Unauthorized', null, 401);
        }

        // Get various statistics
        $totalRoutes = $this->routeModel->where('truck_driver_id', $driverId)->countAllResults();
        $savedRoutes = $this->routeModel->where('truck_driver_id', $driverId)
                                       ->where('is_saved', 1)
                                       ->countAllResults();

        $totalOrders = $this->masterOrderModel->where('driver_id', $driverId)->countAllResults();
        $pendingOrders = $this->masterOrderModel->where('driver_id', $driverId)
                                                ->where('order_status', 'pending')
                                                ->countAllResults();

        $unreadNotifications = $this->notificationModel->where('truck_driver_id', $driverId)
                                                      ->where('is_read', 0)
                                                      ->countAllResults();

        // Get active session info
        $activeSession = $this->drivingSessionModel->findActiveByDriverId($driverId);

        return $this->jsonSuccess(null, [
            'routes' => [
                'total' => $totalRoutes,
                'saved' => $savedRoutes
            ],
            'orders' => [
                'total' => $totalOrders,
                'pending' => $pendingOrders
            ],
            'notifications' => [
                'unread' => $unreadNotifications
            ],
            'active_session' => $activeSession
        ]);
    }
}
