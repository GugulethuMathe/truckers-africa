<?php

namespace App\Controllers;

use App\Models\RouteModel;
use App\Models\RouteMerchantModel;
use App\Models\RouteStopModel;
use App\Models\MerchantModel;
use App\Models\PlatformSettingModel;

class Routes extends BaseController
{
    protected $routeModel;
    protected $routeMerchantModel;
    protected $routeStopModel;
    protected $merchantModel;
    protected $platformSettingModel;

    public function __construct()
    {
        $this->routeModel = new RouteModel();
        $this->routeMerchantModel = new RouteMerchantModel();
        $this->routeStopModel = new RouteStopModel();
        $this->merchantModel = new MerchantModel();
        $this->platformSettingModel = new PlatformSettingModel();
    }

    /**
     * Render a route details page
     */
    public function view($routeId)
    {
        // Require logged-in driver
        if (!session()->get('is_logged_in') || session()->get('user_type') !== 'driver') {
            return redirect()->to('/login');
        }

        $driverId = session()->get('user_id');

        // Fetch route with merchants
        $route = $this->routeModel->getRouteWithMerchants((int) $routeId);
        if (!$route || (int) $route['truck_driver_id'] !== (int) $driverId) {
            return redirect()->to(base_url('driver/routes'));
        }

        // Fetch route stops
        $stops = $this->routeStopModel
            ->where('route_id', (int) $routeId)
            ->orderBy('stop_order', 'ASC')
            ->findAll();
        $route['stops'] = $stops;

        $data = [
            'title' => 'Route Details',
            'route' => $route,
        ];

        return view('driver/route_view', $data);
    }

    /**
     * Display the main routes page
     */
    public function index()
    {
        // Check if user is logged in as driver
        if (!session()->get('is_logged_in') || session()->get('user_type') !== 'driver') {
            return redirect()->to('/login');
        }

        $driverId = session()->get('user_id');

        // Get saved routes
        $savedRoutes = $this->routeModel->getSavedRoutes($driverId);

        // Get merchants with their services for the map (similar to dashboard)
        $merchants = $this->getMerchantsWithServices();

        // Get service categories for filtering
        $serviceCategoryModel = new \App\Models\ServiceCategoryModel();
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
        $driverLocationHistoryModel = new \App\Models\DriverLocationHistoryModel();
        $driverLocation = $driverLocationHistoryModel
            ->where('truck_driver_id', $driverId)
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

        // Get fuel calculation settings
        $fuelSettings = $this->getFuelCalculationSettings();

        $data = [
            'page_title' => 'Route Planning',
            'saved_routes' => $savedRoutes,
            'merchants' => $merchants,
            'categories' => $categories,
            'business_locations' => $businessLocations,
            'geoapify_api_key' => getenv('GEOAPIFY_API_KEY'),
            'fuel_settings' => $fuelSettings,
            'driver_location' => $driverLocation,
            'current_page' => 'routes'
        ];

        return view('driver/routes', $data);
    }

    /**
     * Create a new route
     */
    public function create()
    {
        if (!session()->get('is_logged_in') || session()->get('user_type') !== 'driver') {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $driverId = session()->get('user_id');
        
        // Get JSON input
        $jsonInput = $this->request->getBody();
        $json = json_decode($jsonInput, true);
        
        if (!$json || json_last_error() !== JSON_ERROR_NONE) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid JSON data']);
        }
        
        // Validate required fields
        if (empty($json['start_address']) || empty($json['end_address']) || 
            !isset($json['start_lat']) || !isset($json['start_lng']) ||
            !isset($json['end_lat']) || !isset($json['end_lng'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Missing required route data']);
        }
        
        // Calculate fuel consumption, cost, and estimated duration if distance is available
        $fuelLiters = null;
        $fuelCostZar = null;
        $estimatedDurationMinutes = null;

        if (isset($json['total_distance_km']) && $json['total_distance_km'] > 0) {
            $fuelSettings = $this->getFuelCalculationSettings();
            $fuelLiters = (float) $json['total_distance_km'] / $fuelSettings['km_per_liter'];
            $fuelCostZar = $fuelLiters * $fuelSettings['fuel_cost_per_liter_zar'];

            // Calculate estimated duration based on average speed (65 km/h)
            $estimatedDurationMinutes = ((float) $json['total_distance_km'] / $fuelSettings['average_speed_kmh']) * 60;
        }

        $routeData = [
            'truck_driver_id' => $driverId,
            'route_name' => $json['route_name'] ?? ($json['start_address'] . ' to ' . $json['end_address']),
            'start_address' => $json['start_address'],
            'start_lat' => (float) $json['start_lat'],
            'start_lng' => (float) $json['start_lng'],
            'end_address' => $json['end_address'],
            'end_lat' => (float) $json['end_lat'],
            'end_lng' => (float) $json['end_lng'],
            'total_distance_km' => isset($json['total_distance_km']) ? (float) $json['total_distance_km'] : null,
            'estimated_duration_minutes' => $estimatedDurationMinutes ? (int) round($estimatedDurationMinutes) : (isset($json['estimated_duration_minutes']) ? (int) $json['estimated_duration_minutes'] : null),
            'estimated_fuel_liters' => $fuelLiters,
            'estimated_fuel_cost_zar' => $fuelCostZar,
            'route_polyline' => $json['route_polyline'] ?? null,
            // Check if is_saved is explicitly set in request, otherwise default to 0
            'is_saved' => isset($json['is_saved']) ? (int) $json['is_saved'] : 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        try {
            $routeId = $this->routeModel->insert($routeData);

            if ($routeId) {
                // Save route stops if provided
                if (isset($json['stops']) && is_array($json['stops']) && !empty($json['stops'])) {
                    foreach ($json['stops'] as $index => $stop) {
                        if (!empty($stop['address']) && isset($stop['lat']) && isset($stop['lng'])) {
                            $stopData = [
                                'route_id' => $routeId,
                                'stop_order' => $index + 1,
                                'stop_type' => $stop['type'] ?? 'waypoint',
                                'address' => $stop['address'],
                                'lat' => (float) $stop['lat'],
                                'lng' => (float) $stop['lng'],
                                'created_at' => date('Y-m-d H:i:s')
                            ];
                            
                            if (isset($stop['merchant_id'])) {
                                $stopData['merchant_id'] = (int) $stop['merchant_id'];
                            }
                            
                            $this->routeStopModel->insert($stopData);
                        }
                    }
                }

                return $this->response->setJSON([
                    'success' => true, 
                    'message' => 'Route saved successfully',
                    'route_id' => $routeId
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Route creation failed: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Database error occurred']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to create route']);
    }

    /**
     * Get merchants along a route
     */
    public function getMerchantsAlongRoute()
    {
        if (!session()->get('is_logged_in') || session()->get('user_type') !== 'driver') {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $request = \Config\Services::request();
        
        $startLat = $request->getPost('start_lat');
        $startLng = $request->getPost('start_lng');
        $endLat = $request->getPost('end_lat');
        $endLng = $request->getPost('end_lng');
        $radius = $request->getPost('radius') ?? 50; // 50km radius

        // Get merchants within radius of the route
        $merchants = $this->merchantModel
            ->select('*, 
                (6371 * acos(cos(radians(' . $startLat . ')) 
                * cos(radians(latitude)) 
                * cos(radians(longitude) - radians(' . $startLng . ')) 
                + sin(radians(' . $startLat . ')) 
                * sin(radians(latitude)))) AS distance_from_start')
            ->where('status', 'approved')
            ->where('latitude IS NOT NULL')
            ->where('longitude IS NOT NULL')
            ->having('distance_from_start <=', $radius)
            ->orderBy('distance_from_start', 'ASC')
            ->findAll();

        return $this->response->setJSON([
            'success' => true,
            'merchants' => $merchants
        ]);
    }

    /**
     * Delete a route
     */
    public function delete($routeId)
    {
        if (!session()->get('is_logged_in') || session()->get('user_type') !== 'driver') {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $driverId = session()->get('user_id');
        
        // Verify route belongs to current driver
        $route = $this->routeModel->where('truck_driver_id', $driverId)->find($routeId);
        if (!$route) {
            return $this->response->setJSON(['success' => false, 'message' => 'Route not found']);
        }

        // Delete route stops first
        $this->routeStopModel->where('route_id', $routeId)->delete();
        
        // Delete route merchants
        $this->routeMerchantModel->where('route_id', $routeId)->delete();
        
        // Delete the route
        $deleted = $this->routeModel->delete($routeId);

        if ($deleted) {
            return $this->response->setJSON(['success' => true, 'message' => 'Route deleted successfully']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete route']);
    }

    /**
     * Get route details
     */
    public function getRoute($routeId)
    {
        if (!session()->get('is_logged_in') || session()->get('user_type') !== 'driver') {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $driverId = session()->get('user_id');
        
        // Get route with merchants
        $route = $this->routeModel->getRouteWithMerchants($routeId);
        
        if (!$route || $route['truck_driver_id'] != $driverId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Route not found']);
        }

        // Get route stops
        $stops = $this->routeStopModel->where('route_id', $routeId)
                                    ->orderBy('stop_order', 'ASC')
                                    ->findAll();
        
        $route['stops'] = $stops;

        return $this->response->setJSON([
            'success' => true,
            'route' => $route
        ]);
    }

    /**
     * Toggle saved status of a route
     */
    public function toggleSaved($routeId)
    {
        if (!session()->get('is_logged_in') || session()->get('user_type') !== 'driver') {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $driverId = session()->get('user_id');
        
        $success = $this->routeModel->toggleSaved($routeId, $driverId);
        
        if ($success) {
            // Get updated route to return current status
            $route = $this->routeModel->where('truck_driver_id', $driverId)->find($routeId);
            $message = $route['is_saved'] ? 'Route saved successfully!' : 'Route removed from saved routes';
            
            return $this->response->setJSON([
                'success' => true, 
                'message' => $message,
                'is_saved' => (bool)$route['is_saved']
            ]);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Route not found or access denied']);
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
     * Get fuel calculation settings from platform settings
     */
    private function getFuelCalculationSettings()
    {
        $fuelCostPerLiter = $this->platformSettingModel->getSetting('fuel_cost_per_liter_zar') ?: '23.50';
        $kmPerLiter = $this->platformSettingModel->getSetting('truck_fuel_consumption_km_per_liter') ?: '2.0';
        $averageSpeedKmh = $this->platformSettingModel->getSetting('average_driving_speed_kmh') ?: '65';

        return [
            'fuel_cost_per_liter_zar' => (float) $fuelCostPerLiter,
            'km_per_liter' => (float) $kmPerLiter,
            'liters_per_100km' => 100 / (float) $kmPerLiter, // Calculate liters per 100km for easier calculation
            'average_speed_kmh' => (float) $averageSpeedKmh // Average driving speed for time estimation
        ];
    }
}
