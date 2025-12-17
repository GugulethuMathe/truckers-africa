<?php

namespace App\Models;

use CodeIgniter\Model;

class MerchantListingCategoryModel extends Model
{
    protected $table = 'merchant_listing_categories';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = '';

    protected $allowedFields = [
        'listing_id',
        'category_id'
    ];

    protected $validationRules = [
        'listing_id' => 'required|integer',
        'category_id' => 'required|integer'
    ];

    /**
     * Get categories for a specific listing
     */
    public function getCategoriesForListing($listingId)
    {
        return $this->select('service_categories.id, service_categories.name, service_categories.description')
                    ->join('service_categories', 'service_categories.id = merchant_listing_categories.category_id')
                    ->where('merchant_listing_categories.listing_id', $listingId)
                    ->findAll();
    }

    /**
     * Get listings for a specific category
     */
    public function getListingsForCategory($categoryId)
    {
        return $this->select('merchant_listings.*')
                    ->join('merchant_listings', 'merchant_listings.id = merchant_listing_categories.listing_id')
                    ->where('merchant_listing_categories.category_id', $categoryId)
                    ->where('merchant_listings.status', 'approved')
                    ->where('merchant_listings.is_available', 1)
                    ->findAll();
    }

    /**
     * Remove all categories for a listing
     */
    public function removeCategoriesForListing($listingId)
    {
        return $this->where('listing_id', $listingId)->delete();
    }

    /**
     * Add categories to a listing
     */
    public function addCategoriesToListing($listingId, $categoryIds)
    {
        $data = [];
        foreach ($categoryIds as $categoryId) {
            $data[] = [
                'listing_id' => $listingId,
                'category_id' => $categoryId
            ];
        }
        
        if (!empty($data)) {
            return $this->insertBatch($data);
        }
        
        return true;
    }
}
