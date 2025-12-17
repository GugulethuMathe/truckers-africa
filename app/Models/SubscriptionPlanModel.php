<?php

namespace App\Models;

use CodeIgniter\Model;

class SubscriptionPlanModel extends Model
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

    // Validation rules
    protected $validationRules = [
        'name' => 'required|max_length[255]',
        'price' => 'required|decimal',
        'billing_interval' => 'required|in_list[monthly,yearly]',
        'has_trial' => 'in_list[0,1]',
        'trial_days' => 'integer'
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Plan name is required',
            'max_length' => 'Plan name cannot exceed 255 characters'
        ],
        'price' => [
            'required' => 'Plan price is required',
            'decimal' => 'Plan price must be a valid decimal number'
        ]
    ];

    /**
     * Get all subscription plans ordered by price
     * @return array
     */
    public function getAllPlans(): array
    {
        return $this->orderBy('price', 'ASC')
                   ->findAll();
    }

    /**
     * Get a plan by ID with features
     * @param int $planId
     * @return array|null
     */
    public function getPlanWithFeatures(int $planId): ?array
    {
        $plan = $this->find($planId);
        if ($plan && is_array($plan)) {
            // Get plan features from plan_features table
            $db = \Config\Database::connect();
            $builder = $db->table('plan_features');
            $features = $builder->where('plan_id', $planId)->get()->getResultArray();
            $plan['features'] = $features;
        }
        return is_array($plan) ? $plan : null;
    }

    /**
     * Get the cheapest plan
     * @return array|null
     */
    public function getCheapestPlan(): ?array
    {
        return $this->orderBy('price', 'ASC')
                   ->first();
    }

    /**
     * Get plans for comparison display
     * @return array
     */
    public function getPlansForComparison(): array
    {
        $plans = $this->getAllPlans();

        foreach ($plans as &$plan) {
            // Get plan features with proper join to features table
            $db = \Config\Database::connect();
            $builder = $db->table('plan_features');
            $features = $builder->select('features.name as feature_name, features.description, plan_features.sort_order')
                               ->join('features', 'features.id = plan_features.feature_id')
                               ->where('plan_features.plan_id', $plan['id'])
                               ->orderBy('plan_features.sort_order', 'ASC')
                               ->get()
                               ->getResultArray();
            $plan['features'] = $features;

            // Format price for display (USD)
            $plan['formatted_price'] = '$' . number_format($plan['price'], 2);

            // Calculate yearly price if monthly
            if ($plan['billing_interval'] === 'monthly') {
                $plan['yearly_price'] = $plan['price'] * 12;
                $plan['formatted_yearly_price'] = '$' . number_format($plan['yearly_price'], 2);
            }

            // Add trial information
            $plan['has_free_trial'] = $plan['has_trial'] == 1;
            $plan['trial_period'] = $plan['trial_days'] . ' days';
        }

        return $plans;
    }
}
