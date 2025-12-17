<?php

namespace App\Models;

use CodeIgniter\Model;

class MerchantAnalyticsModel extends Model
{
    protected $table = 'merchant_analytics';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'merchant_id',
        'date',
        'profile_views',
        'booking_requests',
        'conversion_rate'
    ];

    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';

    protected $validationRules = [
        'merchant_id' => 'required|integer',
        'date' => 'required|valid_date',
        'profile_views' => 'permit_empty|integer',
        'booking_requests' => 'permit_empty|integer',
        'conversion_rate' => 'permit_empty|decimal'
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Custom methods
    public function getMerchantAnalytics($merchantId, $startDate = null, $endDate = null)
    {
        $builder = $this->where('merchant_id', $merchantId);
        
        if ($startDate) {
            $builder->where('date >=', $startDate);
        }
        
        if ($endDate) {
            $builder->where('date <=', $endDate);
        }
        
        return $builder->orderBy('date', 'DESC')->findAll();
    }

    public function incrementProfileViews($merchantId, $date = null)
    {
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        $existing = $this->where('merchant_id', $merchantId)
                         ->where('date', $date)
                         ->first();
        
        if ($existing) {
            return $this->update($existing['id'], [
                'profile_views' => $existing['profile_views'] + 1
            ]);
        } else {
            return $this->insert([
                'merchant_id' => $merchantId,
                'date' => $date,
                'profile_views' => 1,
                'booking_requests' => 0
            ]);
        }
    }

    public function incrementBookingRequests($merchantId, $date = null)
    {
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        $existing = $this->where('merchant_id', $merchantId)
                         ->where('date', $date)
                         ->first();
        
        if ($existing) {
            $newBookingRequests = $existing['booking_requests'] + 1;
            $conversionRate = $existing['profile_views'] > 0 ? 
                ($newBookingRequests / $existing['profile_views']) * 100 : 0;
            
            return $this->update($existing['id'], [
                'booking_requests' => $newBookingRequests,
                'conversion_rate' => round($conversionRate, 2)
            ]);
        } else {
            return $this->insert([
                'merchant_id' => $merchantId,
                'date' => $date,
                'profile_views' => 0,
                'booking_requests' => 1,
                'conversion_rate' => 0
            ]);
        }
    }

    public function getAnalyticsSummary($merchantId, $days = 30)
    {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));
        
        return $this->select('
                SUM(profile_views) as total_views,
                SUM(booking_requests) as total_requests,
                AVG(conversion_rate) as avg_conversion_rate
            ')
            ->where('merchant_id', $merchantId)
            ->where('date >=', $startDate)
            ->first();
    }
}
