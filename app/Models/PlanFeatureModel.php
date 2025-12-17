<?php

namespace App\Models;

use CodeIgniter\Model;

class PlanFeatureModel extends Model
{
    protected $table = 'plan_features';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';

    protected $allowedFields = [
        'plan_id',
        'feature_id',
        'sort_order'
    ];

    // No timestamps for this pivot table
    protected $useTimestamps = false;

    /**
     * Update the sort order for features in a plan
     * @param int $planId
     * @param array $featureIds Array of feature IDs in the desired order
     * @return bool
     */
    public function updateFeatureOrder(int $planId, array $featureIds): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            foreach ($featureIds as $index => $featureId) {
                $sortOrder = $index + 1;
                $this->where('plan_id', $planId)
                     ->where('feature_id', $featureId)
                     ->set('sort_order', $sortOrder)
                     ->update();
            }

            $db->transComplete();
            return $db->transStatus();
        } catch (\Exception $e) {
            $db->transRollback();
            return false;
        }
    }

    /**
     * Get the next sort order for a plan
     * @param int $planId
     * @return int
     */
    public function getNextSortOrder(int $planId): int
    {
        $maxOrder = $this->selectMax('sort_order')
                         ->where('plan_id', $planId)
                         ->first();

        return ($maxOrder['sort_order'] ?? 0) + 1;
    }
}
