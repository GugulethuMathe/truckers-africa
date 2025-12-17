<?php

namespace App\Models;

use CodeIgniter\Model;

class MerchantListingServiceModel extends Model
{
    protected $table = 'merchant_listing_services';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = '';

    protected $allowedFields = [
        'listing_id',
        'service_id'
    ];

    protected $validationRules = [
        'listing_id' => 'required|integer',
        'service_id' => 'required|integer'
    ];

    /**
     * Get services for a specific listing
     */
    public function getServicesForListing($listingId)
    {
        return $this->select('services.id, services.name, service_categories.name as category_name')
                    ->join('services', 'services.id = merchant_listing_services.service_id')
                    ->join('service_categories', 'service_categories.id = services.category_id')
                    ->where('merchant_listing_services.listing_id', $listingId)
                    ->findAll();
    }

    /**
     * Get listings for a specific service
     */
    public function getListingsForService($serviceId)
    {
        return $this->select('merchant_listings.*')
                    ->join('merchant_listings', 'merchant_listings.id = merchant_listing_services.listing_id')
                    ->where('merchant_listing_services.service_id', $serviceId)
                    ->where('merchant_listings.status', 'approved')
                    ->where('merchant_listings.is_available', 1)
                    ->findAll();
    }

    /**
     * Remove all services for a listing
     */
    public function removeServicesForListing($listingId)
    {
        return $this->where('listing_id', $listingId)->delete();
    }

    /**
     * Add services to a listing
     */
    public function addServicesToListing($listingId, $serviceIds)
    {
        $data = [];
        foreach ($serviceIds as $serviceId) {
            $data[] = [
                'listing_id' => $listingId,
                'service_id' => $serviceId
            ];
        }
        
        if (!empty($data)) {
            return $this->insertBatch($data);
        }
        
        return true;
    }
}
