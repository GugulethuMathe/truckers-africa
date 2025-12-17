<?php

namespace App\Models;

use CodeIgniter\Model;

class DriverLocationHistoryArchiveModel extends Model
{
    protected $table = 'driver_location_history_archive';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'truck_driver_id',
        'latitude',
        'longitude',
        'accuracy',
        'speed',
        'heading',
        'recorded_at'
    ];

    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';

    protected $validationRules = [
        'truck_driver_id' => 'required|integer',
        'latitude' => 'required|decimal',
        'longitude' => 'required|decimal',
        'accuracy' => 'permit_empty|decimal',
        'speed' => 'permit_empty|decimal',
        'heading' => 'permit_empty|decimal'
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Custom methods
    public function getArchivedLocationHistory($driverId, $startDate = null, $endDate = null, $limit = 1000)
    {
        $builder = $this->where('truck_driver_id', $driverId);
        
        if ($startDate) {
            $builder->where('recorded_at >=', $startDate);
        }
        
        if ($endDate) {
            $builder->where('recorded_at <=', $endDate);
        }
        
        return $builder->orderBy('recorded_at', 'DESC')
                      ->limit($limit)
                      ->findAll();
    }

    public function archiveLocationData($data)
    {
        if (is_array($data) && isset($data[0])) {
            // Batch insert for multiple records
            return $this->insertBatch($data);
        } else {
            // Single record insert
            return $this->insert($data);
        }
    }

    public function getDriverRouteHistory($driverId, $startDate, $endDate)
    {
        return $this->where('truck_driver_id', $driverId)
                    ->where('recorded_at >=', $startDate)
                    ->where('recorded_at <=', $endDate)
                    ->orderBy('recorded_at', 'ASC')
                    ->findAll();
    }
}
