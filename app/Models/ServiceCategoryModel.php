<?php

namespace App\Models;

use CodeIgniter\Model;

class ServiceCategoryModel extends Model
{
    protected $table = 'service_categories';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = ['name', 'description'];

    /**
     * Fetch all service categories, ordered by name.
     * @return array
     */
    public function getAllCategories(): array
    {
        return $this->orderBy('name', 'ASC')->findAll();
    }
}
