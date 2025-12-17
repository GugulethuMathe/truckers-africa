<?php

namespace App\Models;

use CodeIgniter\Model;

class ServiceModel extends Model
{
    protected $table = 'services';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = ['category_id', 'name'];

    /**
     * Fetch all services, grouped by category.
     * @return array
     */
    public function findAllGroupedByCategory(): array
    {
        $services = $this->orderBy('category_id', 'ASC')
                         ->orderBy('name', 'ASC')
                         ->findAll();

        $grouped = [];
        foreach ($services as $service) {
            $grouped[$service['category_id']][] = $service;
        }
        return $grouped;
    }

    /**
     * Fetch all services for a specific category.
     * @param int $categoryId
     * @return array
     */
    public function findByCategoryId(int $categoryId): array
    {
        return $this->where('category_id', $categoryId)
                    ->orderBy('name', 'ASC')
                    ->findAll();
    }
    public function getHomepageServices(int $limit = 12): array
    {
        // For now, we get the first 12. Later, this could be
        // modified to get the most popular or featured services.
        return $this->orderBy('id', 'ASC')->findAll($limit);
    }
}
