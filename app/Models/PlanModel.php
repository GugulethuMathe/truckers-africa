<?php

namespace App\Models;

use CodeIgniter\Model;

class PlanModel extends Model
{
    protected $table = 'plans';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';

    protected $allowedFields = [
        'name',
        'price',
        'billing_interval',
        'description',
        'has_trial',
        'trial_days'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get all features associated with a specific plan.
     * @param int $planId
     * @return array
     */
    public function getFeatures(int $planId): array
    {
        return $this->select('features.*, plan_features.sort_order')
            ->join('plan_features', 'plan_features.plan_id = plans.id')
            ->join('features', 'features.id = plan_features.feature_id')
            ->where('plans.id', $planId)
            ->orderBy('plan_features.sort_order', 'ASC')
            ->findAll();
    }
}
