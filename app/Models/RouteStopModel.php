<?php

namespace App\Models;

use CodeIgniter\Model;

class RouteStopModel extends Model
{
    protected $table = 'route_stops';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';

    protected $allowedFields = [
        'route_id',
        'merchant_id',
        'stop_order',
        'stop_type',
        'address',
        'lat',
        'lng'
    ];

    /**
     * Add multiple stops for a route using a batch insert.
     * @param int $routeId
     * @param array $stops
     * @return bool
     */
    public function addStopsForRoute(int $routeId, array $stops): bool
    {
        if (empty($stops)) {
            return true;
        }

        $insertData = [];
        foreach ($stops as $stop) {
            $insertData[] = [
                'route_id'   => $routeId,
                'merchant_id' => $stop['merchant_id'] ?? null,
                'stop_order' => $stop['stop_order'],
                'stop_type'  => $stop['stop_type'] ?? 'waypoint',
                'address'    => $stop['address'] ?? null,
                'lat'        => $stop['lat'],
                'lng'        => $stop['lng'],
            ];
        }

        return $this->insertBatch($insertData) !== false;
    }

    /**
     * Fetch all stops for a given route, ordered by stop_order.
     * @param int $routeId
     * @return array
     */
    public function findByRouteId(int $routeId): array
    {
        return $this->where('route_id', $routeId)
                    ->orderBy('stop_order', 'ASC')
                    ->findAll();
    }

    /**
     * Delete all stops for a given route.
     * @param int $routeId
     * @return bool
     */
    public function deleteByRouteId(int $routeId): bool
    {
        return $this->where('route_id', $routeId)->delete() !== false;
    }
}
