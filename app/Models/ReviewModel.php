<?php

namespace App\Models;

use CodeIgniter\Model;

class ReviewModel extends Model
{
    protected $table = 'reviews';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';

    protected $allowedFields = [
        'merchant_id',
        'truck_driver_id',
        'rating',
        'comment'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at'; // Assumes you have an updated_at field

    /**
     * Fetch all reviews for a specific merchant.
     * Joins with truck_drivers to get the driver's name.
     * @param int $merchantId
     * @return array
     */
    public function findByMerchantId(int $merchantId): array
    {
        return $this->select('reviews.id, reviews.rating, reviews.comment, reviews.created_at, truck_drivers.name AS driver_name')
                    ->join('truck_drivers', 'truck_drivers.id = reviews.truck_driver_id')
                    ->where('reviews.merchant_id', $merchantId)
                    ->orderBy('reviews.created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Calculate the average rating for a merchant.
     * @param int $merchantId
     * @return float
     */
    public function getAverageRating(int $merchantId): float
    {
        $result = $this->selectAvg('rating', 'average_rating')
                       ->where('merchant_id', $merchantId)
                       ->first();

        return $result ? (float)$result['average_rating'] : 0.0;
    }
}
