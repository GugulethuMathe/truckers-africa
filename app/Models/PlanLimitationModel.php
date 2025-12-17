<?php

namespace App\Models;

use CodeIgniter\Model;

class PlanLimitationModel extends Model
{
    protected $table = 'plan_limitations';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'plan_id',
        'limitation_type',
        'limit_value'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'plan_id' => 'required|integer',
        'limitation_type' => 'required|in_list[max_locations,max_listings,max_categories,max_gallery_images]',
        'limit_value' => 'required|integer'
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Available limitation types
    const LIMIT_LOCATIONS = 'max_locations';
    const LIMIT_LISTINGS = 'max_listings';
    const LIMIT_CATEGORIES = 'max_categories';
    const LIMIT_GALLERY_IMAGES = 'max_gallery_images';

    // Unlimited value
    const UNLIMITED = -1;

    /**
     * Get all limitations for a specific plan
     *
     * @param int $planId
     * @return array
     */
    public function getPlanLimitations(int $planId): array
    {
        return $this->where('plan_id', $planId)->findAll();
    }

    /**
     * Get specific limit value for a plan
     *
     * @param int $planId
     * @param string $limitationType
     * @return int (-1 = unlimited, 0 = not allowed, positive = limit)
     */
    public function getPlanLimit(int $planId, string $limitationType): int
    {
        $limit = $this->where('plan_id', $planId)
                     ->where('limitation_type', $limitationType)
                     ->first();

        return $limit ? (int)$limit['limit_value'] : 0;
    }

    /**
     * Get merchant's limit based on their current subscription
     *
     * @param int $merchantId
     * @param string $limitationType
     * @return int
     */
    public function getMerchantLimit(int $merchantId, string $limitationType): int
    {
        $subscriptionModel = new SubscriptionModel();
        $subscription = $subscriptionModel->findByMerchantId($merchantId);

        if (!$subscription || !isset($subscription['plan_id'])) {
            // No subscription - return minimum limits (free tier equivalent)
            return $this->getDefaultLimit($limitationType);
        }

        return $this->getPlanLimit($subscription['plan_id'], $limitationType);
    }

    /**
     * Get default limits when no subscription exists
     *
     * @param string $limitationType
     * @return int
     */
    private function getDefaultLimit(string $limitationType): int
    {
        $defaults = [
            self::LIMIT_LOCATIONS => 1,
            self::LIMIT_LISTINGS => 3,
            self::LIMIT_CATEGORIES => 2,
            self::LIMIT_GALLERY_IMAGES => 3
        ];

        return $defaults[$limitationType] ?? 0;
    }

    /**
     * Check if merchant can perform an action based on their plan limits
     *
     * @param int $merchantId
     * @param string $limitationType
     * @param int $currentCount
     * @return array ['allowed' => bool, 'limit' => int, 'current' => int, 'message' => string]
     */
    public function checkLimit(int $merchantId, string $limitationType, int $currentCount): array
    {
        $limit = $this->getMerchantLimit($merchantId, $limitationType);

        // -1 means unlimited
        if ($limit === self::UNLIMITED) {
            return [
                'allowed' => true,
                'limit' => 'Unlimited',
                'current' => $currentCount,
                'message' => 'No limit on your current plan'
            ];
        }

        $allowed = $currentCount < $limit;
        $remaining = max(0, $limit - $currentCount);

        $limitName = $this->getLimitationName($limitationType);

        if ($allowed) {
            $message = "You can add {$remaining} more {$limitName}.";
        } else {
            $message = "You've reached your plan limit of {$limit} {$limitName}. Please upgrade your plan to add more.";
        }

        return [
            'allowed' => $allowed,
            'limit' => $limit,
            'current' => $currentCount,
            'remaining' => $remaining,
            'message' => $message
        ];
    }

    /**
     * Get human-readable name for limitation type
     *
     * @param string $limitationType
     * @return string
     */
    private function getLimitationName(string $limitationType): string
    {
        $names = [
            self::LIMIT_LOCATIONS => 'locations',
            self::LIMIT_LISTINGS => 'service listings',
            self::LIMIT_CATEGORIES => 'categories',
            self::LIMIT_GALLERY_IMAGES => 'gallery images'
        ];

        return $names[$limitationType] ?? 'items';
    }

    /**
     * Set or update a plan limitation
     *
     * @param int $planId
     * @param string $limitationType
     * @param int $limitValue
     * @return bool
     */
    public function setPlanLimit(int $planId, string $limitationType, int $limitValue): bool
    {
        $existing = $this->where('plan_id', $planId)
                        ->where('limitation_type', $limitationType)
                        ->first();

        if ($existing) {
            return $this->update($existing['id'], ['limit_value' => $limitValue]);
        } else {
            $data = [
                'plan_id' => $planId,
                'limitation_type' => $limitationType,
                'limit_value' => $limitValue
            ];
            return (bool)$this->insert($data);
        }
    }

    /**
     * Get all limitations formatted for display
     *
     * @param int $planId
     * @return array
     */
    public function getPlanLimitationsFormatted(int $planId): array
    {
        $limitations = $this->getPlanLimitations($planId);
        $formatted = [];

        foreach ($limitations as $limit) {
            $value = $limit['limit_value'] == self::UNLIMITED ? 'Unlimited' : $limit['limit_value'];
            $formatted[$limit['limitation_type']] = [
                'value' => $limit['limit_value'],
                'display' => $value,
                'name' => $this->getLimitationName($limit['limitation_type'])
            ];
        }

        return $formatted;
    }

    /**
     * Get merchant's current usage vs limits
     *
     * @param int $merchantId
     * @return array
     */
    public function getMerchantUsageStats(int $merchantId): array
    {
        $locationModel = new MerchantLocationModel();
        $listingModel = new \App\Models\MerchantListingModel();

        // Get current counts
        $locationsCount = $locationModel->getActiveLocationsCount($merchantId);
        $listingsCount = $listingModel->where('merchant_id', $merchantId)
                                      ->where('status', 'approved')
                                      ->countAllResults();

        // Get limits
        $maxLocations = $this->getMerchantLimit($merchantId, self::LIMIT_LOCATIONS);
        $maxListings = $this->getMerchantLimit($merchantId, self::LIMIT_LISTINGS);
        $maxCategories = $this->getMerchantLimit($merchantId, self::LIMIT_CATEGORIES);
        $maxGalleryImages = $this->getMerchantLimit($merchantId, self::LIMIT_GALLERY_IMAGES);

        return [
            'locations' => [
                'current' => $locationsCount,
                'limit' => $maxLocations,
                'display_limit' => $maxLocations === self::UNLIMITED ? 'Unlimited' : $maxLocations,
                'percentage' => $maxLocations > 0 ? min(100, ($locationsCount / $maxLocations) * 100) : 0,
                'can_add' => $maxLocations === self::UNLIMITED || $locationsCount < $maxLocations
            ],
            'listings' => [
                'current' => $listingsCount,
                'limit' => $maxListings,
                'display_limit' => $maxListings === self::UNLIMITED ? 'Unlimited' : $maxListings,
                'percentage' => $maxListings > 0 ? min(100, ($listingsCount / $maxListings) * 100) : 0,
                'can_add' => $maxListings === self::UNLIMITED || $listingsCount < $maxListings
            ],
            'categories' => [
                'limit' => $maxCategories,
                'display_limit' => $maxCategories === self::UNLIMITED ? 'Unlimited' : $maxCategories
            ],
            'gallery_images' => [
                'limit' => $maxGalleryImages,
                'display_limit' => $maxGalleryImages === self::UNLIMITED ? 'Unlimited' : $maxGalleryImages
            ]
        ];
    }

    /**
     * Compare limits between two plans
     *
     * @param int $currentPlanId
     * @param int $newPlanId
     * @return array
     */
    public function comparePlans(int $currentPlanId, int $newPlanId): array
    {
        $currentLimits = $this->getPlanLimitationsFormatted($currentPlanId);
        $newLimits = $this->getPlanLimitationsFormatted($newPlanId);

        $comparison = [];

        foreach ([self::LIMIT_LOCATIONS, self::LIMIT_LISTINGS, self::LIMIT_CATEGORIES, self::LIMIT_GALLERY_IMAGES] as $type) {
            $currentValue = $currentLimits[$type]['value'] ?? 0;
            $newValue = $newLimits[$type]['value'] ?? 0;

            $comparison[$type] = [
                'name' => $this->getLimitationName($type),
                'current' => $currentLimits[$type]['display'] ?? '0',
                'new' => $newLimits[$type]['display'] ?? '0',
                'is_upgrade' => ($newValue === self::UNLIMITED) || ($newValue > $currentValue),
                'is_downgrade' => ($newValue !== self::UNLIMITED) && ($newValue < $currentValue),
                'difference' => $newValue - $currentValue
            ];
        }

        return $comparison;
    }
}
