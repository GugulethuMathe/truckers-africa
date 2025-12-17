<?php

namespace App\Models;

use CodeIgniter\Model;

class SubscriptionModel extends Model
{
    protected $table = 'subscriptions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';

    protected $allowedFields = [
        'merchant_id',
        'plan_id',
        'status',
        'trial_ends_at',
        'current_period_starts_at',
        'current_period_ends_at',
        'payfast_token'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation rules
    protected $validationRules = [
        'merchant_id' => 'required|integer',
        'plan_id' => 'permit_empty|integer',
        'status' => 'required|in_list[trial,active,past_due,cancelled,expired,new,trial_pending]'
    ];

    /**
     * Find a subscription by merchant ID with plan details.
     * @param int $merchantId
     * @return array|null
     */
    public function findByMerchantId(int $merchantId): ?array
    {
        return $this->select('subscriptions.*, plans.name as plan_name, plans.price, plans.description, plans.has_trial, plans.trial_days')
                   ->join('plans', 'plans.id = subscriptions.plan_id', 'left')
                   ->where('subscriptions.merchant_id', $merchantId)
                   ->first();
    }

    /**
     * Get current active subscription for merchant
     * Includes past_due subscriptions (which can be renewed)
     * @param int $merchantId
     * @return array|null
     */
    public function getCurrentSubscription(int $merchantId): ?array
    {
        return $this->select('subscriptions.*, plans.name as plan_name, plans.price, plans.description, plans.has_trial, plans.trial_days')
                   ->join('plans', 'plans.id = subscriptions.plan_id', 'left')
                   ->where('subscriptions.merchant_id', $merchantId)
                   ->whereIn('subscriptions.status', ['trial', 'active', 'past_due', 'new', 'trial_pending'])
                   ->first();
    }

    /**
     * Update a subscription's status and period dates.
     * @param int $subscriptionId
     * @param string $status
     * @param string|null $startsAt
     * @param string|null $endsAt
     * @return bool
     */
    public function updateStatus(int $subscriptionId, string $status, ?string $startsAt = null, ?string $endsAt = null): bool
    {
        $data = ['status' => $status];
        
        if ($startsAt !== null) {
            $data['current_period_starts_at'] = $startsAt;
        }
        
        if ($endsAt !== null) {
            $data['current_period_ends_at'] = $endsAt;
        }
        
        return $this->update($subscriptionId, $data);
    }

    /**
     * Start a trial subscription for a merchant
     * @param int $merchantId
     * @param int $planId
     * @param int $trialDays
     * @return bool
     */
    public function startTrial(int $merchantId, int $planId, int $trialDays = 7): bool
    {
        $trialEnds = date('Y-m-d H:i:s', strtotime("+{$trialDays} days"));
        $periodStart = date('Y-m-d H:i:s');
        $periodEnd = date('Y-m-d H:i:s', strtotime("+1 month"));
        
        return $this->insert([
            'merchant_id' => $merchantId,
            'plan_id' => $planId,
            'status' => 'trial',
            'trial_ends_at' => $trialEnds,
            'current_period_starts_at' => $periodStart,
            'current_period_ends_at' => $periodEnd
        ]);
    }

    /**
     * Cancel a subscription
     * @param int $subscriptionId
     * @return bool
     */
    public function cancelSubscription(int $subscriptionId): bool
    {
        return $this->update($subscriptionId, [
            'status' => 'cancelled'
        ]);
    }

    /**
     * Upgrade/Downgrade subscription plan (with prorata billing)
     * This method preserves the current billing period for prorata billing
     * @param int $subscriptionId
     * @param int $newPlanId
     * @return bool
     */
    public function changePlan(int $subscriptionId, int $newPlanId): bool
    {
        // Only update plan_id and ensure status is active
        // DO NOT reset billing period dates - this preserves prorata billing
        return $this->update($subscriptionId, [
            'plan_id' => $newPlanId,
            'status' => 'active',
            'trial_ends_at' => null
        ]);
    }

    /**
     * Check if merchant has active subscription
     * @param int $merchantId
     * @return bool
     */
    public function hasActiveSubscription(int $merchantId): bool
    {
        $subscription = $this->where('merchant_id', $merchantId)
                            ->whereIn('status', ['trial', 'active'])
                            ->first();
        
        return $subscription !== null;
    }

    /**
     * Get subscription history for merchant
     * @param int $merchantId
     * @return array
     */
    public function getSubscriptionHistory(int $merchantId): array
    {
        return $this->select('subscriptions.*, plans.name as plan_name, plans.trial_days')
                   ->join('plans', 'plans.id = subscriptions.plan_id', 'left')
                   ->where('subscriptions.merchant_id', $merchantId)
                   ->orderBy('subscriptions.created_at', 'DESC')
                   ->findAll();
    }

    /**
     * Check if subscription billing period has expired and update status
     * @param int $subscriptionId
     * @return bool Returns true if subscription was expired and updated
     */
    public function checkAndUpdateExpiredStatus(int $subscriptionId): bool
    {
        $subscription = $this->find($subscriptionId);

        if (!$subscription) {
            return false;
        }

        // Skip if already cancelled or expired
        if (in_array($subscription['status'], ['cancelled', 'expired'])) {
            return false;
        }

        $now = time();

        // Check if trial has expired
        if ($subscription['status'] === 'trial' && $subscription['trial_ends_at']) {
            $trialEnd = strtotime($subscription['trial_ends_at']);
            if ($trialEnd < $now) {
                // Trial expired - mark as expired
                $this->update($subscriptionId, ['status' => 'expired']);
                log_message('info', "Subscription {$subscriptionId}: Trial expired");
                return true;
            }
        }

        // Check if billing period has expired
        if ($subscription['current_period_ends_at']) {
            $periodEnd = strtotime($subscription['current_period_ends_at']);
            if ($periodEnd < $now) {
                // Billing period expired - mark as past_due (awaiting renewal payment)
                $this->update($subscriptionId, ['status' => 'past_due']);
                log_message('info', "Subscription {$subscriptionId}: Billing period expired, marked as past_due");
                return true;
            }
        }

        return false;
    }

    /**
     * Renew subscription for next billing period
     * Called after successful payment
     * @param int $subscriptionId
     * @return bool
     */
    public function renewSubscription(int $subscriptionId): bool
    {
        $subscription = $this->find($subscriptionId);

        if (!$subscription) {
            return false;
        }

        // Calculate new billing period (1 month from previous end date)
        $currentPeriodEnd = $subscription['current_period_ends_at']
            ? strtotime($subscription['current_period_ends_at'])
            : time();

        $newPeriodStart = date('Y-m-d H:i:s', $currentPeriodEnd);
        $newPeriodEnd = date('Y-m-d H:i:s', strtotime('+1 month', $currentPeriodEnd));

        return $this->update($subscriptionId, [
            'status' => 'active',
            'current_period_starts_at' => $newPeriodStart,
            'current_period_ends_at' => $newPeriodEnd,
            'trial_ends_at' => null
        ]);
    }

    /**
     * Process all expired subscriptions in the system
     * Should be called by a cron job daily
     * @return array ['expired' => count, 'past_due' => count]
     */
    public function processExpiredSubscriptions(): array
    {
        $expired = 0;
        $pastDue = 0;

        // Get all active or trial subscriptions
        $subscriptions = $this->whereIn('status', ['trial', 'active'])->findAll();

        foreach ($subscriptions as $subscription) {
            if ($this->checkAndUpdateExpiredStatus($subscription['id'])) {
                if ($subscription['status'] === 'trial') {
                    $expired++;
                } else {
                    $pastDue++;
                }
            }
        }

        return ['expired' => $expired, 'past_due' => $pastDue];
    }

    /**
     * Find all cancelled subscriptions that have passed their end date
     * @return array
     */
    public function findExpiredCancelledSubscriptions(): array
    {
        return $this->select('subscriptions.*, merchants.business_name')
                    ->join('merchants', 'merchants.id = subscriptions.merchant_id')
                    ->where('subscriptions.status', 'cancelled')
                    ->where('subscriptions.current_period_ends_at <', date('Y-m-d H:i:s'))
                    ->findAll();
    }

    /**
     * Mark subscription as expired and return merchant ID
     * @param int $subscriptionId
     * @return int|null Merchant ID if successful
     */
    public function expireSubscription(int $subscriptionId): ?int
    {
        $subscription = $this->find($subscriptionId);
        if (!$subscription) {
            return null;
        }

        $success = $this->update($subscriptionId, ['status' => 'expired']);

        if ($success) {
            log_message('info', "Subscription {$subscriptionId} expired for merchant {$subscription['merchant_id']}");
            return $subscription['merchant_id'];
        }

        return null;
    }

    /**
     * Process cancelled subscriptions that have reached their end date
     * Returns array of merchant IDs whose subscriptions were expired
     * @return array
     */
    public function processCancelledSubscriptions(): array
    {
        $expiredSubscriptions = $this->findExpiredCancelledSubscriptions();
        $merchantIds = [];

        foreach ($expiredSubscriptions as $subscription) {
            $merchantId = $this->expireSubscription($subscription['id']);
            if ($merchantId) {
                $merchantIds[] = $merchantId;
            }
        }

        return $merchantIds;
    }
}
