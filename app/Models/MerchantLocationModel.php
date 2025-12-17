<?php

namespace App\Models;

use CodeIgniter\Model;

class MerchantLocationModel extends Model
{
    protected $table = 'merchant_locations';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'merchant_id',
        'location_name',
        'physical_address',
        'latitude',
        'longitude',
        'contact_number',
        'whatsapp_number',
        'email',
        'operating_hours',
        'is_primary',
        'is_active'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'merchant_id' => 'required|integer',
        'location_name' => 'required|min_length[3]|max_length[255]',
        'physical_address' => 'required|min_length[10]',
        'contact_number' => 'required|min_length[8]|max_length[50]',
        'latitude' => 'permit_empty|decimal',
        'longitude' => 'permit_empty|decimal',
        'whatsapp_number' => 'permit_empty|min_length[8]|max_length[50]',
        'email' => 'permit_empty|valid_email',
        'operating_hours' => 'permit_empty',
        'is_primary' => 'permit_empty|in_list[0,1]',
        'is_active' => 'permit_empty|in_list[0,1]'
    ];

    protected $validationMessages = [
        'merchant_id' => [
            'required' => 'Merchant ID is required',
            'integer' => 'Merchant ID must be a valid number'
        ],
        'location_name' => [
            'required' => 'Location name is required',
            'min_length' => 'Location name must be at least 3 characters',
            'max_length' => 'Location name cannot exceed 255 characters'
        ],
        'physical_address' => [
            'required' => 'Physical address is required',
            'min_length' => 'Physical address must be at least 10 characters'
        ],
        'contact_number' => [
            'required' => 'Contact number is required',
            'min_length' => 'Contact number must be at least 8 characters'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Get all locations for a specific merchant
     *
     * @param int $merchantId
     * @param bool $activeOnly
     * @return array
     */
    public function getLocationsByMerchant(int $merchantId, bool $activeOnly = true): array
    {
        $builder = $this->where('merchant_id', $merchantId);

        if ($activeOnly) {
            $builder->where('is_active', 1);
        }

        return $builder->orderBy('is_primary', 'DESC')
                      ->orderBy('created_at', 'ASC')
                      ->findAll();
    }

    /**
     * Get primary location for a merchant
     *
     * @param int $merchantId
     * @return array|null
     */
    public function getPrimaryLocation(int $merchantId): ?array
    {
        return $this->where('merchant_id', $merchantId)
                    ->where('is_primary', 1)
                    ->where('is_active', 1)
                    ->first();
    }

    /**
     * Get active locations count for a merchant
     *
     * @param int $merchantId
     * @return int
     */
    public function getActiveLocationsCount(int $merchantId): int
    {
        return $this->where('merchant_id', $merchantId)
                    ->where('is_active', 1)
                    ->countAllResults();
    }

    /**
     * Check if merchant can add more locations based on their plan
     *
     * @param int $merchantId
     * @return array ['can_add' => bool, 'current_count' => int, 'max_allowed' => int, 'message' => string, 'is_max_plan' => bool]
     */
    public function canAddLocation(int $merchantId): array
    {
        $planLimitModel = new PlanLimitationModel();
        $currentCount = $this->getActiveLocationsCount($merchantId);
        $maxAllowed = $planLimitModel->getMerchantLimit($merchantId, 'max_locations');

        // -1 means unlimited
        if ($maxAllowed === -1) {
            return [
                'can_add' => true,
                'current_count' => $currentCount,
                'max_allowed' => 'Unlimited',
                'message' => 'You can add unlimited locations.',
                'is_max_plan' => true
            ];
        }

        $canAdd = $currentCount < $maxAllowed;

        // Check if merchant is on the highest-priced plan
        $isMaxPlan = $this->isOnMaximumPlan($merchantId);

        if ($canAdd) {
            $message = "You can add " . ($maxAllowed - $currentCount) . " more location(s).";
        } else {
            // If on maximum plan, show "Contact Support" instead of "Upgrade"
            if ($isMaxPlan) {
                $message = "You've reached your plan limit of {$maxAllowed} location(s). Contact Support to add more location(s).";
            } else {
                $message = "You've reached your plan limit of {$maxAllowed} location(s). Upgrade to add more.";
            }
        }

        return [
            'can_add' => $canAdd,
            'current_count' => $currentCount,
            'max_allowed' => $maxAllowed,
            'message' => $message,
            'is_max_plan' => $isMaxPlan
        ];
    }

    /**
     * Check if merchant is on the highest-priced plan
     *
     * @param int $merchantId
     * @return bool
     */
    private function isOnMaximumPlan(int $merchantId): bool
    {
        $subscriptionModel = new \App\Models\SubscriptionModel();
        $planModel = new \App\Models\SubscriptionPlanModel();

        // Get merchant's current subscription
        $subscription = $subscriptionModel->getCurrentSubscription($merchantId);

        if (!$subscription || !isset($subscription['plan_id'])) {
            return false;
        }

        // Get the highest-priced plan
        $highestPlan = $planModel->orderBy('price', 'DESC')->first();

        if (!$highestPlan) {
            return false;
        }

        // Check if merchant's plan is the highest-priced plan
        return (int)$subscription['plan_id'] === (int)$highestPlan['id'];
    }

    /**
     * Set a location as primary (and unset others)
     *
     * @param int $locationId
     * @param int $merchantId
     * @return bool
     */
    public function setPrimaryLocation(int $locationId, int $merchantId): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Unset all primary locations for this merchant
            $this->where('merchant_id', $merchantId)
                 ->set('is_primary', 0)
                 ->update();

            // Set the specified location as primary
            $this->update($locationId, ['is_primary' => 1]);

            $db->transComplete();

            return $db->transStatus();
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Failed to set primary location: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get location with listing count
     *
     * @param int $locationId
     * @return array|null
     */
    public function getLocationWithStats(int $locationId): ?array
    {
        $location = $this->find($locationId);

        if (!$location) {
            return null;
        }

        // Get listing count for this location
        $listingModel = new \App\Models\MerchantListingModel();
        $listingsCount = $listingModel->where('location_id', $locationId)
                                      ->where('status', 'approved')
                                      ->countAllResults();

        $location['listings_count'] = $listingsCount;

        return $location;
    }

    /**
     * Soft deactivate a location (doesn't delete, just marks as inactive)
     *
     * @param int $locationId
     * @param int $merchantId
     * @return bool
     */
    public function deactivateLocation(int $locationId, int $merchantId): bool
    {
        $location = $this->where('id', $locationId)
                        ->where('merchant_id', $merchantId)
                        ->first();

        if (!$location) {
            return false;
        }

        // Don't allow deactivating primary location if it's the only one
        if ($location['is_primary'] == 1) {
            $activeCount = $this->getActiveLocationsCount($merchantId);
            if ($activeCount <= 1) {
                return false; // Can't deactivate the only/primary location
            }
        }

        return $this->update($locationId, ['is_active' => 0]);
    }

    /**
     * Activate a location
     *
     * @param int $locationId
     * @param int $merchantId
     * @return bool
     */
    public function activateLocation(int $locationId, int $merchantId): bool
    {
        // Check if merchant can add more locations
        $canAdd = $this->canAddLocation($merchantId);

        if (!$canAdd['can_add']) {
            return false;
        }

        return $this->where('id', $locationId)
                    ->where('merchant_id', $merchantId)
                    ->set('is_active', 1)
                    ->update();
    }

    /**
     * Get locations with pagination
     *
     * @param int $merchantId
     * @param int $perPage
     * @return array
     */
    public function getLocationsPaginated(int $merchantId, int $perPage = 10): array
    {
        return $this->where('merchant_id', $merchantId)
                    ->orderBy('is_primary', 'DESC')
                    ->orderBy('created_at', 'DESC')
                    ->paginate($perPage);
    }

    /**
     * Update merchant's location count cache
     *
     * @param int $merchantId
     * @return bool
     */
    public function updateMerchantLocationCount(int $merchantId): bool
    {
        try {
            $count = $this->getActiveLocationsCount($merchantId);

            $merchantModel = new MerchantModel();
            return $merchantModel->update($merchantId, ['current_locations_count' => $count]);
        } catch (\Exception $e) {
            // If column doesn't exist yet (migration not run), silently continue
            log_message('warning', 'Could not update merchant location count: ' . $e->getMessage());
            return true;
        }
    }
}
