<?php

namespace App\Models;

use CodeIgniter\Model;

class PlannedRouteModel extends Model
{
    protected $table = 'planned_routes';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'driver_id',
        'origin_lat',
        'origin_lng',
        'destination_lat',
        'destination_lng',
        'origin_address',
        'destination_address',
        'route_data'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = null;

    protected $validationRules = [
        'driver_id' => 'permit_empty|integer',
        'origin_lat' => 'permit_empty|decimal',
        'origin_lng' => 'permit_empty|decimal',
        'destination_lat' => 'permit_empty|decimal',
        'destination_lng' => 'permit_empty|decimal',
        'origin_address' => 'permit_empty|max_length[255]',
        'destination_address' => 'permit_empty|max_length[255]'
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Custom methods
    public function getDriverRoutes($driverId, $limit = 20)
    {
        return $this->where('driver_id', $driverId)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    public function createPlannedRoute($data)
    {
        $routeData = [
            'driver_id' => $data['driver_id'] ?? null,
            'origin_lat' => $data['origin_lat'] ?? null,
            'origin_lng' => $data['origin_lng'] ?? null,
            'destination_lat' => $data['destination_lat'] ?? null,
            'destination_lng' => $data['destination_lng'] ?? null,
            'origin_address' => $data['origin_address'] ?? null,
            'destination_address' => $data['destination_address'] ?? null,
            'route_data' => isset($data['route_data']) ? json_encode($data['route_data']) : null
        ];
        
        return $this->insert($routeData);
    }

    public function getRouteWithData($routeId)
    {
        $route = $this->find($routeId);
        
        if ($route && $route['route_data']) {
            $route['route_data'] = json_decode($route['route_data'], true);
        }
        
        return $route;
    }

    public function updateRouteData($routeId, $routeData)
    {
        return $this->update($routeId, [
            'route_data' => json_encode($routeData)
        ]);
    }

    public function getRecentRoutes($limit = 50)
    {
        return $this->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    public function getRoutesByArea($minLat, $maxLat, $minLng, $maxLng)
    {
        return $this->where('origin_lat >=', $minLat)
                    ->where('origin_lat <=', $maxLat)
                    ->where('origin_lng >=', $minLng)
                    ->where('origin_lng <=', $maxLng)
                    ->orGroupStart()
                        ->where('destination_lat >=', $minLat)
                        ->where('destination_lat <=', $maxLat)
                        ->where('destination_lng >=', $minLng)
                        ->where('destination_lng <=', $maxLng)
                    ->groupEnd()
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    public function searchRoutes($searchTerm)
    {
        return $this->groupStart()
                        ->like('origin_address', $searchTerm)
                        ->orLike('destination_address', $searchTerm)
                    ->groupEnd()
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    public function deleteOldRoutes($daysToKeep = 90)
    {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$daysToKeep} days"));
        return $this->where('created_at <', $cutoffDate)->delete();
    }
}
