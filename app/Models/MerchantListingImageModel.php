<?php

namespace App\Models;

use CodeIgniter\Model;

class MerchantListingImageModel extends Model
{
    protected $table            = 'merchant_listing_images';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = ['listing_id', 'image_path'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Find all images for a given listing.
     * @param int $listingId
     * @return array
     */
    public function findByListingId(int $listingId): array
    {
        return $this->where('listing_id', $listingId)->findAll();
    }
}
