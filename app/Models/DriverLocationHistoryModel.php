<?php

namespace App\Models;

use CodeIgniter\Model;

class DriverLocationHistoryModel extends Model
{
    protected $table = 'driver_location_history';
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
    public function getDriverLocationHistory($driverId, $limit = 100)
    {
        return $this->where('truck_driver_id', $driverId)
                    ->orderBy('recorded_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    public function getLocationsBetweenDates($driverId, $startDate, $endDate)
    {
        return $this->where('truck_driver_id', $driverId)
                    ->where('recorded_at >=', $startDate)
                    ->where('recorded_at <=', $endDate)
                    ->orderBy('recorded_at', 'ASC')
                    ->findAll();
    }

    public function getLatestLocation($driverId)
    {
        return $this->where('truck_driver_id', $driverId)
                    ->orderBy('recorded_at', 'DESC')
                    ->first();
    }

    public function cleanOldRecords($daysToKeep = 30)
    {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$daysToKeep} days"));
        return $this->where('recorded_at <', $cutoffDate)->delete();
    }
}
