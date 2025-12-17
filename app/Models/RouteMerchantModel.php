<?php

namespace App\Models;

use CodeIgniter\Model;

class RouteMerchantModel extends Model
{
    protected $table = 'route_merchants';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'route_id',
        'merchant_id',
        'distance_from_route',
        'order_in_route'
    ];

    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';

    protected $validationRules = [
        'route_id' => 'permit_empty|integer',
        'merchant_id' => 'permit_empty|integer',
        'distance_from_route' => 'permit_empty|decimal',
        'order_in_route' => 'permit_empty|integer'
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Custom methods
    public function getRouteMerchants($routeId)
    {
        return $this->where('route_id', $routeId)
                    ->orderBy('order_in_route', 'ASC')
                    ->findAll();
    }

    public function getRouteMerchantsWithDetails($routeId)
    {
        return $this->select('route_merchants.*, 
                             merchants.business_name,
                             merchants.owner_name,
                             merchants.business_contact_number,
                             merchants.physical_address,
                             merchants.latitude,
                             merchants.longitude,
                             merchants.business_description')
                    ->join('merchants', 'merchants.id = route_merchants.merchant_id')
                    ->where('route_merchants.route_id', $routeId)
                    ->where('merchants.status', 'approved')
                    ->orderBy('route_merchants.order_in_route', 'ASC')
                    ->findAll();
    }

    public function getMerchantRoutes($merchantId)
    {
        return $this->select('route_merchants.*, 
                             planned_routes.origin_address,
                             planned_routes.destination_address,
                             planned_routes.created_at as route_created')
                    ->join('planned_routes', 'planned_routes.id = route_merchants.route_id', 'left')
                    ->where('route_merchants.merchant_id', $merchantId)
                    ->orderBy('planned_routes.created_at', 'DESC')
                    ->findAll();
    }

    public function addMerchantToRoute($routeId, $merchantId, $distanceFromRoute = null, $orderInRoute = null)
    {
        // If order not specified, get the next order number
        if ($orderInRoute === null) {
            $lastOrder = $this->where('route_id', $routeId)
                              ->orderBy('order_in_route', 'DESC')
                              ->first();
            $orderInRoute = $lastOrder ? $lastOrder['order_in_route'] + 1 : 1;
        }
        
        $data = [
            'route_id' => $routeId,
            'merchant_id' => $merchantId,
            'distance_from_route' => $distanceFromRoute,
            'order_in_route' => $orderInRoute
        ];
        
        return $this->insert($data);
    }

    public function removeMerchantFromRoute($routeId, $merchantId)
    {
        return $this->where('route_id', $routeId)
                    ->where('merchant_id', $merchantId)
                    ->delete();
    }

    public function updateMerchantOrder($routeId, $merchantId, $newOrder)
    {
        return $this->where('route_id', $routeId)
                    ->where('merchant_id', $merchantId)
                    ->set(['order_in_route' => $newOrder])
                    ->update();
    }

    public function reorderMerchants($routeId, $merchantOrders)
    {
        // $merchantOrders should be an array like [merchantId => order, ...]
        $success = true;
        
        foreach ($merchantOrders as $merchantId => $order) {
            $result = $this->updateMerchantOrder($routeId, $merchantId, $order);
            if (!$result) {
                $success = false;
            }
        }
        
        return $success;
    }

    public function findNearbyMerchants($routeId, $maxDistance = 10.0)
    {
        return $this->where('route_id', $routeId)
                    ->where('distance_from_route <=', $maxDistance)
                    ->orderBy('distance_from_route', 'ASC')
                    ->findAll();
    }

    public function getMerchantsInDistanceRange($routeId, $minDistance, $maxDistance)
    {
        return $this->where('route_id', $routeId)
                    ->where('distance_from_route >=', $minDistance)
                    ->where('distance_from_route <=', $maxDistance)
                    ->orderBy('distance_from_route', 'ASC')
                    ->findAll();
    }

    public function clearRouteMerchants($routeId)
    {
        return $this->where('route_id', $routeId)->delete();
    }

    public function getRouteStatistics($routeId)
    {
        $totalMerchants = $this->where('route_id', $routeId)->countAllResults();
        
        $avgDistance = $this->select('AVG(distance_from_route) as avg_distance')
                            ->where('route_id', $routeId)
                            ->where('distance_from_route IS NOT NULL')
                            ->first();
        
        return [
            'total_merchants' => $totalMerchants,
            'average_distance' => $avgDistance['avg_distance'] ?? 0
        ];
    }
}
