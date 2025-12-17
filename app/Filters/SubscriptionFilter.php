<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\SubscriptionModel;

/**
 * Subscription Filter
 *
 * Blocks merchants and branch users from accessing critical features
 * if their subscription is expired, past_due, or cancelled
 */
class SubscriptionFilter implements FilterInterface
{
    /**
     * Routes that should be allowed even without active subscription
     * (so merchants can view subscription page and renew)
     */
    protected $allowedRoutes = [
        'merchant/subscription',
        'merchant/subscription/plans',
        'merchant/subscription/renew',
        'merchant/subscription/renewal-success',
        'merchant/subscription/prorata-payment',
        'merchant/subscription/prorata-success',
        'merchant/subscription/update-payment-method',
        'merchant/subscription/payment-method',
        'merchant/dashboard', // Allow dashboard but show warning
        'merchant/profile',
        'branch/dashboard', // Allow dashboard but show warning
        'branch/profile',
        'logout',
        'payment/notify', // PayFast ITN
        'payment/success',
        'payment/cancel',
    ];

    public function before(RequestInterface $request, $arguments = null)
    {
        // Get current URI
        $uri = $request->getUri()->getPath();

        // Remove base path if present
        $uri = trim(str_replace(base_url(), '', $uri), '/');

        // Check if current route is in allowed list
        foreach ($this->allowedRoutes as $allowedRoute) {
            if (strpos($uri, $allowedRoute) !== false) {
                return $request; // Allow access
            }
        }

        // Check if user is a merchant
        $merchantId = session()->get('merchant_id');

        // Check if user is a branch manager
        $branchUserId = session()->get('branch_user_id');
        $branchLocationId = session()->get('branch_location_id');

        if ($merchantId) {
            // Check merchant subscription
            $subscriptionModel = new SubscriptionModel();
            $subscription = $subscriptionModel->getCurrentSubscription($merchantId);

            if (!$subscription) {
                // No subscription at all
                return redirect()
                    ->to('merchant/subscription/plans')
                    ->with('error', 'You need an active subscription to access this feature. Please choose a plan.');
            }

            // Check subscription status
            // Block access for merchants without active subscription or payment
            $blockedStatuses = ['expired', 'past_due', 'cancelled', 'new', 'trial_pending'];

            if (in_array($subscription['status'], $blockedStatuses)) {
                $message = $this->getSubscriptionMessage($subscription['status']);

                return redirect()
                    ->to('merchant/subscription')
                    ->with('error', $message);
            }

        } elseif ($branchUserId && $branchLocationId) {
            // Branch users inherit subscription status from merchant
            $locationModel = new \App\Models\MerchantLocationModel();
            $location = $locationModel->find($branchLocationId);

            if (!$location) {
                return redirect()
                    ->to('branch/login')
                    ->with('error', 'Location not found.');
            }

            $merchantId = $location['merchant_id'];

            // Check parent merchant subscription
            $subscriptionModel = new SubscriptionModel();
            $subscription = $subscriptionModel->getCurrentSubscription($merchantId);

            if (!$subscription) {
                return redirect()
                    ->to('branch/dashboard')
                    ->with('error', 'Your merchant account does not have an active subscription. Please contact your merchant to renew.');
            }

            $blockedStatuses = ['expired', 'past_due', 'cancelled', 'new', 'trial_pending'];

            if (in_array($subscription['status'], $blockedStatuses)) {
                $message = 'Your merchant account subscription is not active. Please contact your merchant to complete payment and activate the subscription.';

                return redirect()
                    ->to('branch/dashboard')
                    ->with('error', $message);
            }
        }

        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }

    /**
     * Get appropriate message based on subscription status
     */
    protected function getSubscriptionMessage(string $status): string
    {
        $messages = [
            'expired' => 'Your trial has expired. Please subscribe to a plan to continue accessing features.',
            'past_due' => 'Your subscription billing period has ended. Please renew your subscription to continue.',
            'cancelled' => 'Your subscription has been cancelled. Please subscribe to a plan to continue accessing features.',
            'new' => 'Payment required! Please complete your payment to activate your subscription and access premium features. Click "Complete Payment" below to proceed to PayFast.',
            'trial_pending' => 'Payment setup required! Please provide your payment method to start your free trial and access features. You will not be charged during the trial period.',
        ];

        return $messages[$status] ?? 'Your subscription is not active. Please renew to continue.';
    }
}
