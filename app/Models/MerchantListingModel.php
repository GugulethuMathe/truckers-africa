<?php

namespace App\Models;

use CodeIgniter\Model;

class MerchantListingModel extends Model
{
    protected $table            = 'merchant_listings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['merchant_id', 'location_id', 'title', 'description', 'price', 'currency_code', 'price_numeric', 'main_image_path', 'status'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Find all listings for a given merchant.
     * @param int $merchantId
     * @return array
     */
    public function findByMerchantId(int $merchantId): array
    {
        return $this->where('merchant_id', $merchantId)->findAll();
    }

    /**
     * Find all approved listings.
     * @return array
     */
    public function findApproved(): array
    {
        return $this->select('merchant_listings.*, merchants.business_name')
                    ->join('merchants', 'merchants.id = merchant_listings.merchant_id')
                    ->where('merchant_listings.status', 'approved')
                    ->orderBy('merchant_listings.updated_at', 'DESC')
                    ->findAll();
    }

    /**
     * Find all pending listings.
     * @return array
     */
    public function findPending(): array
    {
        return $this->where('status', 'pending')
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Find all listings for a given location.
     * @param int $locationId
     * @return array
     */
    public function findByLocationId(int $locationId): array
    {
        return $this->where('location_id', $locationId)
                    ->orderBy('status', 'DESC')
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Count approved listings for a location.
     * @param int $locationId
     * @return int
     */
    public function countApprovedByLocation(int $locationId): int
    {
        return $this->where('location_id', $locationId)
                    ->where('status', 'approved')
                    ->countAllResults();
    }
}
