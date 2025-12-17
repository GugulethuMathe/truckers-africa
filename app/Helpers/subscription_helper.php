<?php

/**
 * Subscription Helper Functions
 *
 * Helper functions for checking subscription status and access control
 */

if (!function_exists('has_active_subscription')) {
    /**
     * Check if merchant has active subscription
     *
     * @param int|null $merchantId
     * @return bool
     */
    function has_active_subscription(?int $merchantId = null): bool
    {
        if ($merchantId === null) {
            $merchantId = session()->get('merchant_id');
        }

        if (!$merchantId) {
            return false;
        }

        $subscriptionModel = new \App\Models\SubscriptionModel();
        $subscription = $subscriptionModel->getCurrentSubscription($merchantId);

        if (!$subscription) {
            return false;
        }

        // Only trial and active subscriptions are considered "active"
        return in_array($subscription['status'], ['trial', 'active']);
    }
}

if (!function_exists('get_subscription_status')) {
    /**
     * Get merchant subscription status
     *
     * @param int|null $merchantId
     * @return array|null Returns subscription data or null
     */
    function get_subscription_status(?int $merchantId = null): ?array
    {
        if ($merchantId === null) {
            $merchantId = session()->get('merchant_id');
        }

        if (!$merchantId) {
            return null;
        }

        $subscriptionModel = new \App\Models\SubscriptionModel();
        return $subscriptionModel->getCurrentSubscription($merchantId);
    }
}

if (!function_exists('get_subscription_warning')) {
    /**
     * Get subscription warning message for display
     *
     * @param int|null $merchantId
     * @return array|null ['type' => 'warning|error', 'message' => 'text']
     */
    function get_subscription_warning(?int $merchantId = null): ?array
    {
        $subscription = get_subscription_status($merchantId);

        if (!$subscription) {
            return [
                'type' => 'error',
                'message' => 'No active subscription. <a href="' . site_url('merchant/subscription/plans') . '" class="underline font-semibold">Choose a plan</a> to continue.'
            ];
        }

        switch ($subscription['status']) {
            case 'trial':
                // Check days remaining
                if ($subscription['trial_ends_at']) {
                    $daysLeft = max(0, ceil((strtotime($subscription['trial_ends_at']) - time()) / (24 * 60 * 60)));

                    if ($daysLeft <= 3) {
                        return [
                            'type' => 'warning',
                            'message' => "Your trial expires in {$daysLeft} days. <a href=\"" . site_url('merchant/subscription/plans') . "\" class=\"underline font-semibold\">Subscribe now</a> to continue without interruption."
                        ];
                    }
                }
                break;

            case 'past_due':
                return [
                    'type' => 'error',
                    'message' => 'Your subscription payment is overdue. <a href="' . site_url('merchant/subscription/renew/' . $subscription['id']) . '" class="underline font-semibold">Renew now</a> to restore full access.'
                ];

            case 'expired':
                return [
                    'type' => 'error',
                    'message' => 'Your subscription has expired. <a href="' . site_url('merchant/subscription/plans') . '" class="underline font-semibold">Choose a plan</a> to continue.'
                ];

            case 'cancelled':
                return [
                    'type' => 'error',
                    'message' => 'Your subscription has been cancelled. <a href="' . site_url('merchant/subscription/plans') . '" class="underline font-semibold">Reactivate</a> to continue using the platform.'
                ];

            case 'new':
                return [
                    'type' => 'error',
                    'message' => 'Please complete your payment to activate your subscription. <a href="' . site_url('merchant/subscription') . '" class="underline font-semibold">Complete Payment</a>'
                ];
        }

        return null; // No warning needed
    }
}

if (!function_exists('check_subscription_access')) {
    /**
     * Check if merchant can access feature and redirect if not
     *
     * @param string $feature Feature name for logging
     * @param int|null $merchantId
     * @return bool
     */
    function check_subscription_access(string $feature = 'this feature', ?int $merchantId = null): bool
    {
        if (!has_active_subscription($merchantId)) {
            $subscription = get_subscription_status($merchantId);

            $message = $subscription && in_array($subscription['status'], ['past_due', 'expired', 'cancelled', 'new'])
                ? "Your subscription is {$subscription['status']}. Please renew to access {$feature}."
                : "You need an active subscription to access {$feature}.";

            session()->setFlashdata('error', $message);
            return false;
        }

        return true;
    }
}

if (!function_exists('get_branch_subscription_warning')) {
    /**
     * Get subscription warning for branch users
     *
     * @param int|null $branchLocationId
     * @return array|null ['type' => 'warning|error', 'message' => 'text']
     */
    function get_branch_subscription_warning(?int $branchLocationId = null): ?array
    {
        if ($branchLocationId === null) {
            $branchLocationId = session()->get('branch_location_id');
        }

        if (!$branchLocationId) {
            return null;
        }

        // Get parent merchant subscription
        $locationModel = new \App\Models\MerchantLocationModel();
        $location = $locationModel->find($branchLocationId);

        if (!$location) {
            return null;
        }

        $merchantId = $location['merchant_id'];
        $subscription = get_subscription_status($merchantId);

        if (!$subscription) {
            return [
                'type' => 'error',
                'message' => 'Your merchant account does not have an active subscription. Please contact your merchant to renew.'
            ];
        }

        switch ($subscription['status']) {
            case 'past_due':
                return [
                    'type' => 'error',
                    'message' => 'Your merchant account subscription payment is overdue. Please contact your merchant to renew.'
                ];

            case 'expired':
                return [
                    'type' => 'error',
                    'message' => 'Your merchant account subscription has expired. Please contact your merchant to renew.'
                ];

            case 'cancelled':
                return [
                    'type' => 'error',
                    'message' => 'Your merchant account subscription has been cancelled. Please contact your merchant to reactivate.'
                ];
        }

        return null;
    }
}
