<?php

namespace App\Services;

use App\Models\SubscriptionModel;
use App\Models\PlanModel;

/**
 * ProrataBillingService
 * 
 * Handles prorata billing calculations for subscription plan upgrades and downgrades
 */
class ProrataBillingService
{
    protected $subscriptionModel;
    protected $planModel;
    protected $currencyService;

    public function __construct()
    {
        $this->subscriptionModel = new SubscriptionModel();
        $this->planModel = new PlanModel();
        $this->currencyService = new CurrencyService();
    }

    /**
     * Calculate prorata amount for plan change
     * 
     * @param int $subscriptionId
     * @param int $newPlanId
     * @return array|null Returns calculation details or null if error
     */
    public function calculateProrataAmount(int $subscriptionId, int $newPlanId): ?array
    {
        // Get current subscription
        $subscription = $this->subscriptionModel->find($subscriptionId);
        if (!$subscription) {
            log_message('error', 'Subscription not found: ' . $subscriptionId);
            return null;
        }

        // Get current plan
        $currentPlan = $this->planModel->find($subscription['plan_id']);
        if (!$currentPlan) {
            log_message('error', 'Current plan not found: ' . $subscription['plan_id']);
            return null;
        }

        // Get new plan
        $newPlan = $this->planModel->find($newPlanId);
        if (!$newPlan) {
            log_message('error', 'New plan not found: ' . $newPlanId);
            return null;
        }

        // Calculate time remaining in current billing cycle
        $now = time();
        $periodStart = strtotime($subscription['current_period_starts_at']);
        $periodEnd = strtotime($subscription['current_period_ends_at']);

        // Validate dates
        if ($periodEnd <= $now) {
            log_message('warning', 'Subscription period has ended. No prorata needed.');
            return [
                'prorata_amount' => 0,
                'unused_credit' => 0,
                'new_plan_prorata' => 0,
                'days_remaining' => 0,
                'days_used' => 0,
                'total_days' => 0,
                'current_plan_price' => $currentPlan['price'],
                'new_plan_price' => $newPlan['price'],
                'current_plan_name' => $currentPlan['name'],
                'new_plan_name' => $newPlan['name'],
                'is_upgrade' => $newPlan['price'] > $currentPlan['price'],
                'period_start' => $subscription['current_period_starts_at'],
                'period_end' => $subscription['current_period_ends_at'],
                'next_billing_date' => $subscription['current_period_ends_at'],
                'message' => 'Billing period has ended. Full price will be charged.'
            ];
        }

        $totalDays = ($periodEnd - $periodStart) / 86400; // Total days in billing cycle
        $daysUsed = ($now - $periodStart) / 86400; // Days already used
        $daysRemaining = ($periodEnd - $now) / 86400; // Days remaining

        // Ensure we don't have negative days
        if ($daysRemaining < 0) {
            $daysRemaining = 0;
        }

        // Calculate unused credit from current plan (USD)
        $unusedCredit = $currentPlan['price'] * ($daysRemaining / $totalDays);

        // Calculate prorated cost of new plan for remaining period (USD)
        $newPlanProrata = $newPlan['price'] * ($daysRemaining / $totalDays);

        // Calculate amount to charge/credit (USD)
        $prorataAmount = $newPlanProrata - $unusedCredit;

        // Determine if this is an upgrade or downgrade
        $isUpgrade = $newPlan['price'] > $currentPlan['price'];

        return [
            'prorata_amount' => round($prorataAmount, 2), // Amount to charge (positive) or credit (negative)
            'unused_credit' => round($unusedCredit, 2), // Credit from current plan
            'new_plan_prorata' => round($newPlanProrata, 2), // Prorated cost of new plan
            'days_remaining' => round($daysRemaining, 1), // Days left in billing cycle
            'days_used' => round($daysUsed, 1), // Days already used
            'total_days' => round($totalDays, 1), // Total days in billing cycle
            'current_plan_price' => $currentPlan['price'], // Current plan monthly price (USD)
            'new_plan_price' => $newPlan['price'], // New plan monthly price (USD)
            'current_plan_name' => $currentPlan['name'],
            'new_plan_name' => $newPlan['name'],
            'is_upgrade' => $isUpgrade,
            'period_start' => $subscription['current_period_starts_at'],
            'period_end' => $subscription['current_period_ends_at'],
            'next_billing_date' => $subscription['current_period_ends_at'], // Next full billing
            'message' => $this->generateProrataMessage($prorataAmount, $isUpgrade, $daysRemaining)
        ];
    }

    /**
     * Convert prorata amount to ZAR for PayFast
     * 
     * @param float $amountUSD
     * @return float
     */
    public function convertToZAR(float $amountUSD): float
    {
        $amountZAR = $this->currencyService->convertAmount($amountUSD, 'USD', 'ZAR');

        // If conversion fails, use fallback rate
        if ($amountZAR === null) {
            $amountZAR = $amountUSD * 18.50;
            log_message('warning', 'Currency conversion failed, using fallback rate for prorata billing');
        }

        return round($amountZAR, 2);
    }

    /**
     * Generate user-friendly message about prorata charge
     * 
     * @param float $prorataAmount
     * @param bool $isUpgrade
     * @param float $daysRemaining
     * @return string
     */
    protected function generateProrataMessage(float $prorataAmount, bool $isUpgrade, float $daysRemaining): string
    {
        $days = round($daysRemaining);

        if ($prorataAmount > 0) {
            // Upgrade - charge immediately
            return "You'll be charged $" . number_format($prorataAmount, 2) . " today for the remaining {$days} days of this billing cycle. Your next full billing will occur at the end of your current cycle.";
        } elseif ($prorataAmount < 0) {
            // Downgrade - credit to account
            $credit = abs($prorataAmount);
            return "You'll receive a credit of $" . number_format($credit, 2) . " which will be applied to your next billing. The new plan will take effect immediately.";
        } else {
            // Same price or no charge
            return "No additional charge. Your plan will be updated immediately.";
        }
    }

    /**
     * Check if plan change requires immediate payment
     * 
     * @param int $subscriptionId
     * @param int $newPlanId
     * @return bool
     */
    public function requiresImmediatePayment(int $subscriptionId, int $newPlanId): bool
    {
        $calculation = $this->calculateProrataAmount($subscriptionId, $newPlanId);
        
        if (!$calculation) {
            return false;
        }

        // Requires payment if prorata amount is positive (upgrade)
        return $calculation['prorata_amount'] > 0;
    }

    /**
     * Get formatted prorata breakdown for display
     * 
     * @param int $subscriptionId
     * @param int $newPlanId
     * @return array|null
     */
    public function getProrataBreakdown(int $subscriptionId, int $newPlanId): ?array
    {
        $calculation = $this->calculateProrataAmount($subscriptionId, $newPlanId);
        
        if (!$calculation) {
            return null;
        }

        // Convert amounts to ZAR for display
        $prorataZAR = $this->convertToZAR($calculation['prorata_amount']);
        $unusedCreditZAR = $this->convertToZAR($calculation['unused_credit']);
        $newPlanProrataZAR = $this->convertToZAR($calculation['new_plan_prorata']);
        $currentPlanPriceZAR = $this->convertToZAR($calculation['current_plan_price']);
        $newPlanPriceZAR = $this->convertToZAR($calculation['new_plan_price']);

        return [
            'usd' => $calculation,
            'zar' => [
                'prorata_amount' => $prorataZAR,
                'unused_credit' => $unusedCreditZAR,
                'new_plan_prorata' => $newPlanProrataZAR,
                'current_plan_price' => $currentPlanPriceZAR,
                'new_plan_price' => $newPlanPriceZAR,
            ],
            'formatted' => [
                'prorata_amount_usd' => '$' . number_format($calculation['prorata_amount'], 2),
                'prorata_amount_zar' => 'R ' . number_format($prorataZAR, 2),
                'unused_credit_usd' => '$' . number_format($calculation['unused_credit'], 2),
                'unused_credit_zar' => 'R ' . number_format($unusedCreditZAR, 2),
                'new_plan_prorata_usd' => '$' . number_format($calculation['new_plan_prorata'], 2),
                'new_plan_prorata_zar' => 'R ' . number_format($newPlanProrataZAR, 2),
                'current_plan_price_usd' => '$' . number_format($calculation['current_plan_price'], 2),
                'current_plan_price_zar' => 'R ' . number_format($currentPlanPriceZAR, 2),
                'new_plan_price_usd' => '$' . number_format($calculation['new_plan_price'], 2),
                'new_plan_price_zar' => 'R ' . number_format($newPlanPriceZAR, 2),
                'days_remaining' => round($calculation['days_remaining']) . ' days',
                'next_billing_date' => date('F j, Y', strtotime($calculation['next_billing_date'])),
            ]
        ];
    }

    /**
     * Apply plan change with prorata billing
     * This should be called AFTER payment is confirmed
     * 
     * @param int $subscriptionId
     * @param int $newPlanId
     * @return bool
     */
    public function applyPlanChange(int $subscriptionId, int $newPlanId): bool
    {
        $subscription = $this->subscriptionModel->find($subscriptionId);
        if (!$subscription) {
            return false;
        }

        // Update subscription with new plan
        // Keep the same billing period end date (prorata billing)
        $updateData = [
            'plan_id' => $newPlanId,
            'status' => 'active',
            'trial_ends_at' => null, // Clear any trial
        ];

        // DO NOT reset current_period_starts_at and current_period_ends_at
        // This preserves the prorata billing cycle

        return $this->subscriptionModel->update($subscriptionId, $updateData);
    }
}

