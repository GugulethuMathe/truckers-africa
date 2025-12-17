<?php

namespace App\Models;

use CodeIgniter\Model;

class RouteModel extends Model
{
    protected $table = 'routes';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';

    protected $allowedFields = [
        'truck_driver_id',
        'route_name',
        'start_address',
        'start_lat',
        'start_lng',
        'end_address',
        'end_lat',
        'end_lng',
        'total_distance_km',
        'estimated_duration_minutes',
        'estimated_fuel_liters',
        'estimated_fuel_cost_zar',
        'route_polyline',
        'is_saved'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Find all routes for a specific driver.
     * @param int $driverId
     * @return array
     */
    public function findByDriverId(int $driverId): array
    {
        return $this->where('truck_driver_id', $driverId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get route with associated merchants
     * @param int $routeId
     * @return array
     */
    public function getRouteWithMerchants(int $routeId): array
    {
        $route = $this->find($routeId);
        if (!$route) {
            return [];
        }

        $db = \Config\Database::connect();
        $builder = $db->table('route_merchants rm');
        $builder->select('rm.*, m.owner_name, m.business_name, m.business_contact_number, m.physical_address, m.latitude, m.longitude, m.profile_image_url');
        $builder->join('merchants m', 'm.id = rm.merchant_id');
        $builder->where('rm.route_id', $routeId);
        $builder->orderBy('rm.order_in_route', 'ASC');
        
        $merchants = $builder->get()->getResultArray();
        $route['merchants'] = $merchants;
        
        return $route;
    }

    /**
     * Save route with merchants
     * @param array $routeData
     * @param array $merchants
     * @return int|false
     */
    public function saveRouteWithMerchants(array $routeData, array $merchants = [])
    {
        $routeId = $this->insert($routeData);
        
        if ($routeId && !empty($merchants)) {
            $db = \Config\Database::connect();
            $builder = $db->table('route_merchants');
            
            foreach ($merchants as $index => $merchant) {
                $builder->insert([
                    'route_id' => $routeId,
                    'merchant_id' => $merchant['id'],
                    'distance_from_route' => $merchant['distance'],
                    'order_in_route' => $index + 1
                ]);
            }
        }
        
        return $routeId;
    }

    /**
     * Toggle saved status of a route
     * @param int $routeId
     * @param int $driverId
     * @return bool
     */
    public function toggleSaved(int $routeId, int $driverId): bool
    {
        $route = $this->where('truck_driver_id', $driverId)
                     ->find($routeId);
        if (!$route) {
            return false;
        }

        $newSavedStatus = !$route['is_saved'];
        return $this->update($routeId, ['is_saved' => $newSavedStatus]);
    }

    /**
     * Get saved routes for a driver
     * @param int $driverId
     * @return array
     */
    public function getSavedRoutes(int $driverId): array
    {
        return $this->where('truck_driver_id', $driverId)
                    ->where('is_saved', 1)
                    ->orderBy('updated_at', 'DESC')
                    ->findAll();
    }
}
