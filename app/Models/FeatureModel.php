<?php

namespace App\Models;

use CodeIgniter\Model;

class FeatureModel extends Model
{
    protected $table = 'features';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';

    protected $allowedFields = [
        'name',
        'code',
        'description'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
