<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SubscriptionModel;
use App\Models\SubscriptionPlanModel;
use App\Models\MerchantModel;
use App\Models\PaymentTransactionModel;

class Subscription extends BaseController
{
    protected $subscriptionModel;
    protected $planModel;
    protected $merchantModel;

    public function __construct()
    {
        $this->subscriptionModel = new SubscriptionModel();
        $this->planModel = new SubscriptionPlanModel();
        $this->merchantModel = new MerchantModel();
    }

    /**
     * Display subscription dashboard for merchant
     */
    public function index()
    {
        $merchantId = session()->get('merchant_id');
        if (!$merchantId) {
            return redirect()->to('/login');
        }

        // Force fresh query - don't use any cached data
        $currentSubscription = $this->subscriptionModel
            ->select('subscriptions.*, plans.name as plan_name, plans.price, plans.description, plans.has_trial, plans.trial_days')
            ->join('plans', 'plans.id = subscriptions.plan_id', 'left')
            ->where('subscriptions.merchant_id', $merchantId)
            ->whereIn('subscriptions.status', ['trial', 'active', 'past_due', 'new', 'trial_pending'])
            ->orderBy('subscriptions.updated_at', 'DESC')
            ->first();

        // Check if subscription has expired and update status
        if ($currentSubscription) {
            $wasExpired = $this->subscriptionModel->checkAndUpdateExpiredStatus($currentSubscription['id']);

            // If status changed, reload subscription data
            if ($wasExpired) {
                $currentSubscription = $this->subscriptionModel->getCurrentSubscription($merchantId);

                // Show appropriate message based on new status
                if ($currentSubscription['status'] === 'past_due') {
                    session()->setFlashdata('error', 'Your subscription billing period has ended. Please renew your subscription to continue accessing premium features.');
                } elseif ($currentSubscription['status'] === 'expired') {
                    session()->setFlashdata('error', 'Your trial period has ended. Please subscribe to a plan to continue.');
                } elseif ($currentSubscription['status'] === 'new') {
                    session()->setFlashdata('error', 'Please complete your payment to activate your subscription.');
                } elseif ($currentSubscription['status'] === 'trial_pending') {
                    session()->setFlashdata('error', 'Please provide your payment method to start your free trial.');
                }
            }
        }

        $availablePlans = $this->planModel->getPlansForComparison();
        $subscriptionHistory = $this->subscriptionModel->getSubscriptionHistory($merchantId);

        // Get plan features and limitations if subscription exists
        $planFeatures = [];
        $planLimitations = [];
        if ($currentSubscription && isset($currentSubscription['plan_id'])) {
            // Get plan features
            $db = \Config\Database::connect();
            $builder = $db->table('plan_features');
            $planFeatures = $builder->select('features.name as feature_name, features.description, plan_features.sort_order')
                                   ->join('features', 'features.id = plan_features.feature_id')
                                   ->where('plan_features.plan_id', $currentSubscription['plan_id'])
                                   ->orderBy('plan_features.sort_order', 'ASC')
                                   ->get()
                                   ->getResultArray();

            // Get plan limitations
            $planLimitModel = new \App\Models\PlanLimitationModel();
            $planLimitations = $planLimitModel->getPlanLimitationsFormatted($currentSubscription['plan_id']);
        }

        $data = [
            'page_title' => 'Subscription Management',
            'current_subscription' => $currentSubscription,
            'available_plans' => $availablePlans,
            'subscription_history' => $subscriptionHistory,
            'plan_features' => $planFeatures,
            'plan_limitations' => $planLimitations
        ];

        return view('merchant/subscription/index', $data);
    }

    /**
     * Display available subscription plans
     */
    public function showPlans()
    {
        $merchantId = session()->get('merchant_id');
        if (!$merchantId) {
            return redirect()->to('/login');
        }

        $currentSubscription = $this->subscriptionModel->getCurrentSubscription($merchantId);
        $availablePlans = $this->planModel->getPlansForComparison();

        // Check if merchant has EVER had a subscription (including cancelled/expired)
        // Free trials are ONLY for brand new merchants who have never subscribed
        $hasSubscriptionHistory = $this->subscriptionModel
            ->where('merchant_id', $merchantId)
            ->countAllResults() > 0;

        $data = [
            'page_title' => 'Choose Your Plan',
            'current_subscription' => $currentSubscription,
            'available_plans' => $availablePlans,
            'has_subscription_history' => $hasSubscriptionHistory
        ];

        return view('merchant/subscription/plans', $data);
    }

    /**
     * Start a trial subscription for a merchant
     * ONLY available for brand new merchants with no subscription history
     */
    public function startTrial()
    {
        $merchantId = session()->get('merchant_id');
        if (!$merchantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please log in to continue'
            ]);
        }

        $planId = service('request')->getPost('plan_id');
        if (!$planId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please select a plan'
            ]);
        }

        // IMPORTANT: Free trials are ONLY for brand new merchants
        // Check if merchant has EVER had a subscription (including cancelled/expired)
        $hasSubscriptionHistory = $this->subscriptionModel
            ->where('merchant_id', $merchantId)
            ->countAllResults() > 0;

        if ($hasSubscriptionHistory) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Free trials are only available for new merchants. Please select a paid plan to continue.'
            ]);
        }

        // Check if merchant already has an active subscription
        if ($this->subscriptionModel->hasActiveSubscription($merchantId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You already have an active subscription'
            ]);
        }

        // Get plan details
        $plan = $this->planModel->find($planId);
        if (!$plan) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid plan selected'
            ]);
        }

        // Verify plan has trial
        if (!$plan['has_trial'] || $plan['trial_days'] <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'This plan does not offer a free trial'
            ]);
        }

        // Start trial
        $trialDays = $plan['trial_days'];
        $success = $this->subscriptionModel->startTrial($merchantId, $planId, $trialDays);

        if ($success) {
            return $this->response->setJSON([
                'success' => true,
                'message' => "Trial started successfully! You have {$trialDays} days to explore all features."
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to start trial. Please try again.'
            ]);
        }
    }

    /**
     * Show prorata breakdown before plan change
     */
    public function changePlanPreview()
    {
        $merchantId = session()->get('merchant_id');
        if (!$merchantId) {
            return redirect()->to('/login');
        }

        $newPlanId = service('request')->getPost('plan_id');
        if (!$newPlanId) {
            session()->setFlashdata('error', 'Please select a plan');
            return redirect()->back();
        }

        // Get current subscription
        $currentSubscription = $this->subscriptionModel->getCurrentSubscription($merchantId);
        if (!$currentSubscription) {
            session()->setFlashdata('error', 'No active subscription found');
            return redirect()->back();
        }

        // Get new plan details
        $newPlan = $this->planModel->find($newPlanId);
        if (!$newPlan) {
            session()->setFlashdata('error', 'Invalid plan selected');
            return redirect()->back();
        }

        // Check if trying to switch to same plan
        if ($currentSubscription['plan_id'] == $newPlanId) {
            session()->setFlashdata('error', 'You are already subscribed to this plan');
            return redirect()->back();
        }

        // If subscription is 'new' or 'trial_pending', update plan and redirect to payment
        if (in_array($currentSubscription['status'], ['new', 'trial_pending'])) {
            // Update the subscription with new plan
            $this->subscriptionModel->update($currentSubscription['id'], [
                'plan_id' => $newPlanId
            ]);

            session()->setFlashdata('info', 'Plan updated. Please complete payment to activate your subscription.');

            // Redirect to payment
            return redirect()->to('payment/process/' . $newPlanId);
        }

        // Calculate prorata amount to check if this is a downgrade
        $prorataService = new \App\Services\ProrataBillingService();
        $breakdown = $prorataService->getProrataBreakdown($currentSubscription['id'], $newPlanId);

        if (!$breakdown) {
            session()->setFlashdata('error', 'Unable to calculate prorata amount. Please try again.');
            return redirect()->back();
        }

        // Block all downgrades - users must contact support
        if (!$breakdown['usd']['is_upgrade']) {
            $currentPlan = $this->planModel->find($currentSubscription['plan_id']);
            $message = 'Plan downgrades require assistance from our support team to ensure a smooth transition. ' .
                       'Please <a href="' . site_url('merchant/help') . '" class="underline font-semibold text-blue-600 hover:text-blue-800">contact support</a> to downgrade from ' .
                       esc($currentPlan['name']) . ' to ' . esc($newPlan['name']) . '.';
            session()->setFlashdata('error', $message);
            return redirect()->back();
        }

        // Check if downgrading with more locations/branches than new plan allows
        $planLimitationModel = new \App\Models\PlanLimitationModel();
        $locationModel = new \App\Models\MerchantLocationModel();

        // Get current number of locations/branches
        $currentLocationsCount = $locationModel->where('merchant_id', $merchantId)
                                               ->where('is_active', 1)
                                               ->countAllResults();

        // Get new plan's location limit
        $newPlanLocationLimit = $planLimitationModel->getPlanLimit($newPlanId, \App\Models\PlanLimitationModel::LIMIT_LOCATIONS);

        // Check if upgrade exceeds location limits (shouldn't happen for upgrades, but safety check)
        if ($newPlanLocationLimit !== \App\Models\PlanLimitationModel::UNLIMITED &&
            $currentLocationsCount > $newPlanLocationLimit) {
            $message = 'You cannot switch to this plan because you currently have ' . $currentLocationsCount . ' branch location(s), ' .
                       'but the selected plan only allows ' . $newPlanLocationLimit . '. ' .
                       'Please <a href="' . site_url('merchant/help') . '" class="underline font-semibold text-blue-600 hover:text-blue-800">contact support</a> for assistance.';
            session()->setFlashdata('error', $message);
            return redirect()->back();
        }

        // Store plan change details in session for confirmation
        session()->set('plan_change_preview', [
            'new_plan_id' => $newPlanId,
            'subscription_id' => $currentSubscription['id'],
            'breakdown' => $breakdown
        ]);

        $data = [
            'page_title' => 'Confirm Plan Change',
            'current_subscription' => $currentSubscription,
            'new_plan' => $newPlan,
            'breakdown' => $breakdown
        ];

        return view('merchant/templates/header', $data)
             . view('merchant/subscription/change_plan_preview', $data)
             . view('merchant/templates/footer');
    }

    /**
     * Change subscription plan (with prorata billing)
     */
    public function changePlan()
    {
        $merchantId = session()->get('merchant_id');
        if (!$merchantId) {
            return redirect()->to('/login');
        }

        // Get plan change preview from session
        $preview = session()->get('plan_change_preview');
        if (!$preview) {
            session()->setFlashdata('error', 'Invalid plan change request');
            return redirect()->to('/merchant/subscription');
        }

        $newPlanId = $preview['new_plan_id'];
        $subscriptionId = $preview['subscription_id'];
        $breakdown = $preview['breakdown'];

        // Get current subscription
        $currentSubscription = $this->subscriptionModel->find($subscriptionId);
        if (!$currentSubscription || $currentSubscription['merchant_id'] != $merchantId) {
            session()->setFlashdata('error', 'Invalid subscription');
            return redirect()->to('/merchant/subscription');
        }

        // Get new plan details
        $newPlan = $this->planModel->find($newPlanId);
        if (!$newPlan) {
            session()->setFlashdata('error', 'Invalid plan selected');
            return redirect()->to('/merchant/subscription');
        }

        $prorataService = new \App\Services\ProrataBillingService();

        // Check if immediate payment is required (upgrade)
        if ($breakdown['usd']['prorata_amount'] > 0) {
            // Redirect to payment page for prorata charge
            return redirect()->to('/merchant/subscription/prorata-payment/' . $subscriptionId . '/' . $newPlanId);
        } else {
            // Downgrade or same price - apply immediately
            $success = $prorataService->applyPlanChange($subscriptionId, $newPlanId);

            if ($success) {
                // Clear session
                session()->remove('plan_change_preview');

                $message = 'Plan changed successfully to ' . $newPlan['name'];
                if ($breakdown['usd']['prorata_amount'] < 0) {
                    $credit = abs($breakdown['usd']['prorata_amount']);
                    $message .= '. A credit of $' . number_format($credit, 2) . ' will be applied to your next billing.';
                }
                session()->setFlashdata('success', $message);
            } else {
                session()->setFlashdata('error', 'Failed to change plan. Please try again.');
            }

            return redirect()->to('/merchant/subscription');
        }
    }

    /**
     * Prorata payment page for plan upgrades
     */
    public function prorataPayment($subscriptionId, $newPlanId)
    {
        $merchantId = session()->get('merchant_id');
        if (!$merchantId) {
            return redirect()->to('/login');
        }

        // Get subscription
        $subscription = $this->subscriptionModel->find($subscriptionId);
        if (!$subscription || $subscription['merchant_id'] != $merchantId) {
            session()->setFlashdata('error', 'Invalid subscription');
            return redirect()->to('/merchant/subscription');
        }

        // Get merchant details
        $merchantModel = new \App\Models\MerchantModel();
        $merchant = $merchantModel->find($merchantId);
        if (!$merchant) {
            session()->setFlashdata('error', 'Merchant not found');
            return redirect()->to('/merchant/subscription');
        }

        // Get new plan
        $newPlan = $this->planModel->find($newPlanId);
        if (!$newPlan) {
            session()->setFlashdata('error', 'Invalid plan');
            return redirect()->to('/merchant/subscription');
        }

        // Calculate prorata
        $prorataService = new \App\Services\ProrataBillingService();
        $breakdown = $prorataService->getProrataBreakdown($subscriptionId, $newPlanId);

        if (!$breakdown || $breakdown['usd']['prorata_amount'] <= 0) {
            session()->setFlashdata('error', 'No payment required for this plan change');
            return redirect()->to('/merchant/subscription');
        }

        // Convert to ZAR for PayFast
        $prorataAmountZAR = $breakdown['zar']['prorata_amount'];

        // Prepare PayFast data
        $data = [];
        $data['merchant_id'] = getenv('payfast.merchantId');
        $data['merchant_key'] = getenv('payfast.merchantKey');
        $data['return_url'] = site_url('merchant/subscription/prorata-success') . '?subscription_id=' . $subscriptionId . '&plan_id=' . $newPlanId;
        $data['cancel_url'] = site_url('merchant/subscription');
        $data['notify_url'] = site_url('payment/notify');

        // Buyer details (REQUIRED by PayFast)
        $ownerNameParts = explode(' ', $merchant['owner_name'], 2);
        $data['name_first'] = $ownerNameParts[0];
        $data['name_last'] = $ownerNameParts[1] ?? 'unknown'; // Handle cases where there's no last name
        $data['email_address'] = $merchant['email'];

        // Transaction details
        $data['m_payment_id'] = 'PRORATA-' . $subscriptionId . '-' . $newPlanId . '-' . time();
        $data['amount'] = number_format($prorataAmountZAR, 2, '.', '');
        $data['item_name'] = 'Plan Upgrade: ' . $newPlan['name'];
        $data['item_description'] = 'Prorata charge for upgrading to ' . $newPlan['name'] . ' (' . round($breakdown['usd']['days_remaining']) . ' days remaining)';

        // Custom fields to identify this as a prorata payment
        // IMPORTANT: custom_int fields MUST come before custom_str fields (PayFast requirement)
        $data['custom_int1'] = (int)$subscriptionId;
        $data['custom_int2'] = (int)$newPlanId;
        $data['custom_str1'] = 'prorata_upgrade';
        $data['custom_str2'] = (string)$subscriptionId . '-' . (string)$newPlanId;

        // Generate signature using helper function
        helper('payfast');
        $passphrase = getenv('payfast.passphrase');
        // Only use passphrase if it's actually set (not empty string)
        $passphraseToUse = (!empty($passphrase) && $passphrase !== '') ? $passphrase : null;

        // Build signature string for debug output
        $signatureString = '';
        foreach ($data as $key => $val) {
            if ($val !== '' && $val !== null) {
                $signatureString .= $key . '=' . urlencode(trim($val)) . '&';
            }
        }
        $signatureString = rtrim($signatureString, '&');
        if ($passphraseToUse !== null) {
            $signatureString .= '&passphrase=' . urlencode(trim($passphraseToUse));
        }

        $data['signature'] = generatePayFastSignature($data, $passphraseToUse);

        // Log debug info for troubleshooting
        logPayFastDebug('Prorata payment signature generated', [
            'merchant_id' => $data['merchant_id'],
            'amount' => $data['amount'],
            'subscription_id' => $subscriptionId,
            'new_plan_id' => $newPlanId,
            'passphrase_used' => $passphraseToUse ? 'YES' : 'NO'
        ]);

        $viewData = [
            'payfast_data' => $data,
            'payfast_url' => getenv('payfast.processUrl'),
            'page_title' => 'Plan Upgrade Payment',
            'breakdown' => $breakdown,
            'new_plan' => $newPlan
        ];

        return view('merchant/templates/header', $viewData)
             . view('merchant/subscription/prorata_payment', $viewData)
             . view('merchant/templates/footer');
    }

    /**
     * Handle successful prorata payment
     */
    public function prorataSuccess()
    {
        $merchantId = session()->get('merchant_id');
        if (!$merchantId) {
            return redirect()->to('/login');
        }

        // Get plan change details from query parameters (passed by PayFast return)
        $subscriptionId = $this->request->getGet('subscription_id');
        $newPlanId = $this->request->getGet('plan_id');

        // If we don't have the IDs from query params, try to get from session
        if (!$subscriptionId || !$newPlanId) {
            $preview = session()->get('plan_change_preview');
            if ($preview) {
                $subscriptionId = $preview['subscription_id'];
                $newPlanId = $preview['new_plan_id'];
            }
        }

        // Apply the plan change as a fallback (in case ITN hasn't been received yet)
        if ($subscriptionId && $newPlanId) {
            $subscription = $this->subscriptionModel->find($subscriptionId);

            // Only apply if subscription belongs to this merchant and plan hasn't changed yet
            if ($subscription && $subscription['merchant_id'] == $merchantId && $subscription['plan_id'] != $newPlanId) {
                $prorataService = new \App\Services\ProrataBillingService();
                $success = $prorataService->applyPlanChange($subscriptionId, $newPlanId);

                if ($success) {
                    log_message('info', 'Prorata plan change applied immediately after payment success (fallback): Subscription ' . $subscriptionId . ' to plan ' . $newPlanId);

                    // Clear session data
                    session()->remove('plan_change_preview');

                    session()->setFlashdata('success', 'Payment successful! Your plan has been upgraded to ' . $this->planModel->find($newPlanId)['name'] . '.');
                } else {
                    log_message('error', 'Failed to apply prorata plan change after payment success: Subscription ' . $subscriptionId . ' to plan ' . $newPlanId);
                    session()->setFlashdata('success', 'Payment successful! Your plan upgrade will be processed shortly.');
                }
            } else {
                // Plan already changed (ITN was faster) or invalid subscription
                session()->remove('plan_change_preview');
                session()->setFlashdata('success', 'Payment successful! Your plan has been upgraded.');
            }
        } else {
            // No plan change details available
            session()->setFlashdata('success', 'Payment successful! Your plan has been upgraded.');
        }

        return redirect()->to('merchant/subscription');
    }



    /**
     * Cancel subscription
     */
    public function cancel()
    {
        $merchantId = session()->get('merchant_id');
        if (!$merchantId) {
            return redirect()->to('/login');
        }

        // Validate required fields
        $cancellationReason = service('request')->getPost('cancellation_reason');
        $cancellationComments = service('request')->getPost('cancellation_comments');
        $confirmCancel = service('request')->getPost('confirm_cancel');

        if (!$cancellationReason) {
            session()->setFlashdata('error', 'Please select a reason for cancellation');
            return redirect()->back();
        }

        if (!$confirmCancel) {
            session()->setFlashdata('error', 'Please confirm that you understand the cancellation terms');
            return redirect()->back();
        }

        // Get current subscription
        $currentSubscription = $this->subscriptionModel->getCurrentSubscription($merchantId);
        if (!$currentSubscription) {
            session()->setFlashdata('error', 'No active subscription found');
            return redirect()->back();
        }

        // Store cancellation feedback
        $db = \Config\Database::connect();
        try {
            $db->table('subscription_cancellations')->insert([
                'subscription_id' => $currentSubscription['id'],
                'merchant_id' => $merchantId,
                'plan_id' => $currentSubscription['plan_id'],
                'cancellation_reason' => $cancellationReason,
                'cancellation_comments' => $cancellationComments,
                'cancelled_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            // Log error but don't block cancellation
            log_message('error', 'Failed to store cancellation feedback: ' . $e->getMessage());
        }

        // Cancel subscription
        $success = $this->subscriptionModel->cancelSubscription($currentSubscription['id']);

        if ($success) {
            // NOTE: Branches will be deactivated automatically when subscription expires
            // This is handled by the scheduled task (ProcessExpiredSubscriptions command)

            // Send cancellation email (optional)
            try {
                $this->sendCancellationEmail($merchantId, $currentSubscription, $cancellationReason);
            } catch (\Exception $e) {
                log_message('error', 'Failed to send cancellation email: ' . $e->getMessage());
            }

            $expiryDate = date('F j, Y', strtotime($currentSubscription['current_period_ends_at']));
            session()->setFlashdata('success',
                'Your subscription has been cancelled. You will continue to have full access (including all branch locations) until ' .
                $expiryDate . '. After this date, all branch locations will be deactivated. We\'re sorry to see you go!');
        } else {
            session()->setFlashdata('error', 'Failed to cancel subscription. Please try again or contact support.');
        }

        return redirect()->to('/merchant/subscription');
    }

    /**
     * Send cancellation confirmation email
     */
    private function sendCancellationEmail($merchantId, $subscription, $reason)
    {
        $merchantModel = new \App\Models\MerchantModel();
        $merchant = $merchantModel->find($merchantId);

        if (!$merchant || !$merchant['email']) {
            return;
        }

        $planModel = new \App\Models\SubscriptionPlanModel();
        $plan = $planModel->find($subscription['plan_id']);

        $email = \Config\Services::email();
        $email->setFrom('noreply@truckersafrica.com', 'Truckers Africa');
        $email->setTo($merchant['email']);
        $email->setSubject('Subscription Cancelled - Truckers Africa');

        $reasonLabels = [
            'too_expensive' => 'Too expensive',
            'not_enough_orders' => 'Not getting enough orders',
            'missing_features' => 'Missing features',
            'technical_issues' => 'Technical issues',
            'switching_service' => 'Switching to another service',
            'business_closed' => 'Business closed/paused',
            'other' => 'Other reason'
        ];

        $message = view('emails/subscription_cancelled', [
            'merchant_name' => $merchant['business_name'],
            'plan_name' => $plan['name'],
            'access_until' => date('F j, Y', strtotime($subscription['current_period_ends_at'])),
            'cancellation_reason' => $reasonLabels[$reason] ?? $reason
        ]);

        $email->setMessage($message);
        $email->send();
    }

    /**
     * Process payment for new subscription (redirect to PayFast)
     */
    public function processPayment()
    {
        $merchantId = session()->get('merchant_id');
        if (!$merchantId) {
            return redirect()->to('/login');
        }

        // Get plan ID from POST or from current subscription
        $planId = $this->request->getPost('plan_id');

        if (!$planId) {
            // Try to get from current subscription
            $currentSubscription = $this->subscriptionModel->getCurrentSubscription($merchantId);
            if ($currentSubscription) {
                $planId = $currentSubscription['plan_id'];
            }
        }

        if (!$planId) {
            session()->setFlashdata('error', 'No plan selected. Please choose a plan first.');
            return redirect()->to('merchant/subscription/plans');
        }

        // Check if merchant has a cancelled or expired subscription
        // If so, create a new subscription record before payment
        $currentSubscription = $this->subscriptionModel->getCurrentSubscription($merchantId);
        $isReactivation = false;

        if (!$currentSubscription) {
            // Check if they have a cancelled/expired subscription
            $oldSubscription = $this->subscriptionModel
                ->where('merchant_id', $merchantId)
                ->whereIn('status', ['cancelled', 'expired'])
                ->orderBy('updated_at', 'DESC')
                ->first();

            if ($oldSubscription) {
                $isReactivation = true;

                // Create new subscription record with 'new' status
                $plan = $this->planModel->find($planId);

                if ($plan && $plan['has_trial'] && $plan['trial_days'] > 0) {
                    // Plan has trial - create with trial_pending status
                    $trialEnds = date('Y-m-d H:i:s', strtotime("+{$plan['trial_days']} days"));
                    $periodStart = date('Y-m-d H:i:s');
                    $periodEnd = date('Y-m-d H:i:s', strtotime("+1 month"));

                    $this->subscriptionModel->insert([
                        'merchant_id' => $merchantId,
                        'plan_id' => $planId,
                        'status' => 'trial_pending',
                        'trial_ends_at' => $trialEnds,
                        'current_period_starts_at' => $periodStart,
                        'current_period_ends_at' => $periodEnd
                    ]);
                } else {
                    // No trial - create with 'new' status
                    $this->subscriptionModel->insert([
                        'merchant_id' => $merchantId,
                        'plan_id' => $planId,
                        'status' => 'new'
                    ]);
                }

                log_message('info', "Created new subscription for merchant {$merchantId} after cancellation/expiry");

                // Store reactivation flag in session to redirect to branch selection after payment
                session()->set('pending_branch_activation', true);
            }
        }

        // Redirect to Payment controller which handles PayFast integration
        return redirect()->to('payment/process/' . $planId);
    }

    public function updatePaymentMethod()
    {
        $merchantId = session()->get('merchant_id');
        if (!$merchantId) {
            return redirect()->to('/login');
        }

        $currentSubscription = $this->subscriptionModel->getCurrentSubscription($merchantId);
        $merchant = $this->merchantModel->find($merchantId);

        $data = [
            'page_title' => 'Update Payment Method',
            'current_subscription' => $currentSubscription,
            'merchant' => $merchant
        ];

        return view('merchant/subscription/payment_method', $data);
    }

    public function savePaymentMethod()
    {
        $merchantId = session()->get('merchant_id');
        if (!$merchantId) {
            return redirect()->to('/login')->with('error', 'Please log in to continue');
        }

        // Get current subscription
        $currentSubscription = $this->subscriptionModel->getCurrentSubscription($merchantId);
        if (!$currentSubscription) {
            return redirect()->to('/merchant/subscription')->with('error', 'No active subscription found');
        }

        // Get merchant details
        $merchant = $this->merchantModel->find($merchantId);
        if (!$merchant) {
            return redirect()->to('/merchant/subscription')->with('error', 'Merchant profile not found');
        }

        // Get plan details
        $plan = $this->planModel->find($currentSubscription['plan_id']);
        if (!$plan) {
            return redirect()->to('/merchant/subscription')->with('error', 'Plan not found');
        }

        // Prepare PayFast data for updating payment method
        $data = [];
        $data['merchant_id'] = getenv('payfast.merchantId');
        $data['merchant_key'] = getenv('payfast.merchantKey');
        $data['return_url'] = site_url('merchant/subscription/payment-update-success');
        $data['cancel_url'] = site_url('merchant/subscription/payment-update-cancel');
        $data['notify_url'] = site_url('payment/notify');

        // Buyer details
        $ownerNameParts = explode(' ', $merchant['owner_name'], 2);
        $data['name_first'] = $ownerNameParts[0];
        $data['name_last'] = $ownerNameParts[1] ?? 'unknown';
        $data['email_address'] = $merchant['email'];

        // Convert USD price to ZAR for PayFast
        $currencyService = new \App\Services\CurrencyService();
        $planPriceUSD = $plan['price']; // Plan price is stored in USD
        $planPriceZAR = $currencyService->convertAmount($planPriceUSD, 'USD', 'ZAR');

        // If conversion fails, use a fallback rate (1 USD = 18.50 ZAR approximately)
        if ($planPriceZAR === null) {
            $planPriceZAR = $planPriceUSD * 18.50;
            log_message('warning', 'Currency conversion failed, using fallback rate for payment method update');
        }

        // Transaction details - use a unique ID for payment method update
        $data['m_payment_id'] = 'UPDATE-PLAN-' . $currentSubscription['plan_id'] . '-MERCHANT-' . $merchantId . '-' . time();

        // IMPORTANT: Set amount to 0.00 to update payment method without charging
        $data['amount'] = '0.00';

        $data['item_name'] = $plan['name'] . ' Subscription - Payment Method Update';
        $data['item_description'] = 'Update payment method for ' . $plan['name'] . ' subscription (no charge)';

        // Subscription details
        $data['subscription_type'] = 1; // 1 for subscription
        $data['billing_date'] = date('Y-m-d', strtotime($currentSubscription['current_period_ends_at']));
        $data['recurring_amount'] = number_format($planPriceZAR, 2, '.', ''); // Recurring amount in ZAR
        $data['frequency'] = 3; // 3 for monthly
        $data['cycles'] = 0; // 0 for indefinite

        // Generate signature using helper function
        helper('payfast');
        $passphrase = getenv('payfast.passphrase');
        // Only use passphrase if it's actually set (not empty string)
        $passphraseToUse = (!empty($passphrase) && $passphrase !== '') ? $passphrase : null;
        $data['signature'] = generatePayFastSignature($data, $passphraseToUse);

        // Log debug info for troubleshooting
        logPayFastDebug('Payment method update signature generated', [
            'merchant_id' => $data['merchant_id'],
            'subscription_type' => $data['subscription_type'],
            'recurring_amount' => $data['recurring_amount'],
            'passphrase_used' => $passphraseToUse ? 'YES' : 'NO'
        ]);

        $viewData = [
            'payfast_data' => $data,
            'payfast_url' => getenv('payfast.processUrl'),
            'page_title' => 'Update Payment Method',
            'page_class' => 'bg-gray-900 text-slate-200',
        ];

        return view('templates/home-header', $viewData)
             . view('payment/update_payment', $viewData)
             . view('templates/home-footer');
    }

    /**
     * Handle successful payment method update
     */
    public function paymentUpdateSuccess()
    {
        return redirect()->to('merchant/subscription/payment-method')
            ->with('success', 'Payment method updated successfully! Your new payment details will be used for future billing.');
    }

    /**
     * Handle cancelled payment method update
     */
    public function paymentUpdateCancel()
    {
        return redirect()->to('merchant/subscription/payment-method')
            ->with('info', 'Payment method update was cancelled. Your current payment details remain unchanged.');
    }

    /**
     * Display transaction history
     */
    public function transactionHistory()
    {
        $merchantId = session()->get('merchant_id');
        if (!$merchantId) {
            return redirect()->to('/login');
        }

        $transactionModel = new PaymentTransactionModel();
        $transactions = $transactionModel->getMerchantTransactions($merchantId);
        $currentSubscription = $this->subscriptionModel->getCurrentSubscription($merchantId);

        $data = [
            'page_title' => 'Transaction History',
            'transactions' => $transactions,
            'current_subscription' => $currentSubscription
        ];

        return view('merchant/subscription/transaction_history', $data);
    }

    /**
     * Renew expired subscription
     */
    public function renew($subscriptionId = null)
    {
        $merchantId = session()->get('merchant_id');
        if (!$merchantId) {
            return redirect()->to('/login');
        }

        // Get subscription
        $subscription = $this->subscriptionModel->find($subscriptionId);
        if (!$subscription || $subscription['merchant_id'] != $merchantId) {
            session()->setFlashdata('error', 'Invalid subscription');
            return redirect()->to('/merchant/subscription');
        }

        // Only allow renewal for past_due or expired subscriptions
        if (!in_array($subscription['status'], ['past_due', 'expired'])) {
            session()->setFlashdata('error', 'This subscription does not need renewal');
            return redirect()->to('/merchant/subscription');
        }

        // Get merchant details
        $merchant = $this->merchantModel->find($merchantId);
        if (!$merchant) {
            session()->setFlashdata('error', 'Merchant not found');
            return redirect()->to('/merchant/subscription');
        }

        // Get plan details
        $plan = $this->planModel->find($subscription['plan_id']);
        if (!$plan) {
            session()->setFlashdata('error', 'Plan not found');
            return redirect()->to('/merchant/subscription');
        }

        // Convert USD price to ZAR for PayFast
        $currencyService = new \App\Services\CurrencyService();
        $planPriceUSD = $plan['price'];
        $planPriceZAR = $currencyService->convertAmount($planPriceUSD, 'USD', 'ZAR');

        // If conversion fails, use fallback rate
        if ($planPriceZAR === null) {
            $planPriceZAR = $planPriceUSD * 18.50;
            log_message('warning', 'Currency conversion failed, using fallback rate for subscription renewal');
        }

        // Prepare PayFast data
        $data = [];
        $data['merchant_id'] = getenv('payfast.merchantId');
        $data['merchant_key'] = getenv('payfast.merchantKey');
        $data['return_url'] = site_url('merchant/subscription/renewal-success');
        $data['cancel_url'] = site_url('merchant/subscription');
        $data['notify_url'] = site_url('payment/notify');

        // Buyer details
        $ownerNameParts = explode(' ', $merchant['owner_name'], 2);
        $data['name_first'] = $ownerNameParts[0];
        $data['name_last'] = $ownerNameParts[1] ?? 'unknown';
        $data['email_address'] = $merchant['email'];

        // Transaction details
        $data['m_payment_id'] = 'RENEWAL-' . $subscriptionId . '-' . time();
        $data['amount'] = number_format($planPriceZAR, 2, '.', '');
        $data['item_name'] = $plan['name'] . ' Subscription Renewal';
        $data['item_description'] = 'Renewal of ' . $plan['name'] . ' subscription for 1 month';

        // Custom fields
        $data['custom_int1'] = (int)$subscriptionId;
        $data['custom_str1'] = 'subscription_renewal';

        // Generate signature
        helper('payfast');
        $passphrase = getenv('payfast.passphrase');
        $passphraseToUse = (!empty($passphrase) && $passphrase !== '') ? $passphrase : null;
        $data['signature'] = generatePayFastSignature($data, $passphraseToUse);

        // Log debug info
        logPayFastDebug('Subscription renewal payment generated', [
            'merchant_id' => $data['merchant_id'],
            'amount' => $data['amount'],
            'subscription_id' => $subscriptionId,
            'passphrase_used' => $passphraseToUse ? 'YES' : 'NO'
        ]);

        $viewData = [
            'payfast_data' => $data,
            'payfast_url' => getenv('payfast.processUrl'),
            'page_title' => 'Renew Subscription',
            'subscription' => $subscription,
            'plan' => $plan,
            'amount_zar' => $planPriceZAR,
            'amount_usd' => $planPriceUSD
        ];

        return view('merchant/templates/header', $viewData)
             . view('merchant/subscription/renewal_payment', $viewData)
             . view('merchant/templates/footer');
    }

    /**
     * Handle successful renewal payment
     */
    public function renewalSuccess()
    {
        // In sandbox mode, PayFast may not send ITN immediately
        // So we'll manually trigger the renewal here as a fallback
        $merchantId = session()->get('merchant_id');

        if ($merchantId) {
            $subscription = $this->subscriptionModel->getCurrentSubscription($merchantId);

            if ($subscription && in_array($subscription['status'], ['past_due', 'expired'])) {
                // Manually renew since ITN may not have been received yet
                $this->subscriptionModel->renewSubscription($subscription['id']);
                log_message('info', 'Manual renewal triggered for subscription ' . $subscription['id'] . ' after payment success');
            }
        }

        session()->setFlashdata('success', 'Subscription renewed successfully! Thank you for your payment.');
        return redirect()->to('merchant/subscription');
    }

    /**
     * TEST ONLY: Manually trigger renewal without payment
     * Remove in production or secure with password
     */
    public function testRenewal($subscriptionId = null)
    {
        $merchantId = session()->get('merchant_id');
        if (!$merchantId) {
            return redirect()->to('/login');
        }

        if (!$subscriptionId) {
            $subscription = $this->subscriptionModel->getCurrentSubscription($merchantId);
            $subscriptionId = $subscription['id'] ?? null;
        }

        if (!$subscriptionId) {
            session()->setFlashdata('error', 'No subscription found');
            return redirect()->to('/merchant/subscription');
        }

        // Verify subscription belongs to merchant
        $subscription = $this->subscriptionModel->find($subscriptionId);
        if (!$subscription || $subscription['merchant_id'] != $merchantId) {
            session()->setFlashdata('error', 'Invalid subscription');
            return redirect()->to('/merchant/subscription');
        }

        // Renew the subscription
        $success = $this->subscriptionModel->renewSubscription($subscriptionId);

        if ($success) {
            session()->setFlashdata('success', '✅ TEST: Subscription renewed successfully! (No payment required in test mode)');
        } else {
            session()->setFlashdata('error', '❌ TEST: Failed to renew subscription');
        }

        return redirect()->to('/merchant/subscription');
    }

    /**
     * TEST ONLY: Simulate subscription expiry
     * This sets a cancelled subscription's end date to the past and runs the expiry process
     */
    public function testExpiry()
    {
        $merchantId = session()->get('merchant_id');
        if (!$merchantId) {
            return redirect()->to('/login');
        }

        $subscription = $this->subscriptionModel->getCurrentSubscription($merchantId);

        if (!$subscription) {
            session()->setFlashdata('error', '❌ TEST: No subscription found');
            return redirect()->to('/merchant/subscription');
        }

        if ($subscription['status'] !== 'cancelled') {
            session()->setFlashdata('error', '❌ TEST: Subscription must be cancelled first. Current status: ' . $subscription['status']);
            return redirect()->to('/merchant/subscription');
        }

        // Set the end date to yesterday to simulate expiry
        $yesterday = date('Y-m-d H:i:s', strtotime('-1 day'));
        $this->subscriptionModel->update($subscription['id'], [
            'current_period_ends_at' => $yesterday
        ]);

        // Run the expiry process
        $branchUserModel = new \App\Models\BranchUserModel();
        $locationModel = new \App\Models\MerchantLocationModel();

        // Count before deactivation
        $branchCount = $branchUserModel->where('merchant_id', $merchantId)
                                       ->where('is_active', 1)
                                       ->countAllResults();

        $locationCount = $locationModel->where('merchant_id', $merchantId)
                                       ->where('is_primary', 0)
                                       ->where('is_active', 1)
                                       ->countAllResults();

        // Process the expired subscription
        $merchantIds = $this->subscriptionModel->processCancelledSubscriptions();

        if (in_array($merchantId, $merchantIds)) {
            // Deactivate branches
            $branchUserModel->deactivateAllForMerchant($merchantId);

            // Deactivate locations
            $locationModel->where('merchant_id', $merchantId)
                         ->where('is_primary', 0)
                         ->set(['is_active' => 0])
                         ->update();

            session()->setFlashdata('success',
                "✅ TEST: Subscription expired successfully! " .
                "Deactivated {$branchCount} branch user(s) and {$locationCount} location(s). " .
                "Status changed from 'cancelled' to 'expired'.");
        } else {
            session()->setFlashdata('error', '❌ TEST: Failed to process expiry');
        }

        return redirect()->to('/merchant/subscription');
    }

    /**
     * Deactivate all branch users when subscription is cancelled
     */
    private function deactivateAllBranches(int $merchantId): void
    {
        $branchUserModel = new \App\Models\BranchUserModel();
        $locationModel = new \App\Models\MerchantLocationModel();

        // Deactivate all branch users for this merchant
        $branchUserModel->deactivateAllForMerchant($merchantId);

        // Also deactivate all non-primary locations
        $locationModel->where('merchant_id', $merchantId)
                      ->where('is_primary', 0)
                      ->set(['is_active' => 0])
                      ->update();

        log_message('info', "Deactivated all branches for merchant {$merchantId} due to subscription cancellation");
    }

    /**
     * Show branch activation selection page after reactivation
     */
    public function selectBranches()
    {
        $merchantId = session()->get('merchant_id');
        if (!$merchantId) {
            return redirect()->to('/login');
        }

        // Get current subscription
        $currentSubscription = $this->subscriptionModel->getCurrentSubscription($merchantId);
        if (!$currentSubscription) {
            return redirect()->to('merchant/subscription')->with('error', 'No active subscription found');
        }

        // Get plan limitations
        $planLimitationModel = new \App\Models\PlanLimitationModel();
        $locationLimit = $planLimitationModel->getPlanLimitation($currentSubscription['plan_id'], 'max_locations');

        // -1 means unlimited
        $maxLocations = ($locationLimit && $locationLimit['limit_value'] != -1) ? $locationLimit['limit_value'] : 999;

        // Get all locations with their branch users
        $locationModel = new \App\Models\MerchantLocationModel();
        $branchUserModel = new \App\Models\BranchUserModel();

        $locations = $locationModel->where('merchant_id', $merchantId)
                                   ->where('is_primary', 0) // Exclude primary location
                                   ->findAll();

        // Get branch users for each location
        foreach ($locations as &$location) {
            $branchUser = $branchUserModel->where('location_id', $location['id'])->first();
            $location['branch_user'] = $branchUser;
        }

        // Count currently active branches (excluding primary)
        $activeCount = $locationModel->where('merchant_id', $merchantId)
                                     ->where('is_active', 1)
                                     ->where('is_primary', 0)
                                     ->countAllResults();

        $data = [
            'page_title' => 'Activate Branches',
            'locations' => $locations,
            'max_locations' => $maxLocations,
            'active_count' => $activeCount,
            'plan_name' => $currentSubscription['plan_name'] ?? 'Current Plan'
        ];

        return view('merchant/templates/header', $data)
             . view('merchant/subscription/select_branches', $data)
             . view('merchant/templates/footer');
    }

    /**
     * Process branch activation selection
     */
    public function activateBranches()
    {
        $merchantId = session()->get('merchant_id');
        if (!$merchantId) {
            return redirect()->to('/login');
        }

        $selectedBranches = $this->request->getPost('branches') ?? [];

        if (empty($selectedBranches)) {
            return redirect()->back()->with('error', 'Please select at least one branch to activate');
        }

        // Get current subscription and plan limits
        $currentSubscription = $this->subscriptionModel->getCurrentSubscription($merchantId);
        if (!$currentSubscription) {
            return redirect()->to('merchant/subscription')->with('error', 'No active subscription found');
        }

        $planLimitationModel = new \App\Models\PlanLimitationModel();
        $locationLimit = $planLimitationModel->getPlanLimitation($currentSubscription['plan_id'], 'max_locations');
        $maxLocations = ($locationLimit && $locationLimit['limit_value'] != -1) ? $locationLimit['limit_value'] : 999;

        // Validate selection doesn't exceed plan limit
        if (count($selectedBranches) > $maxLocations) {
            return redirect()->back()->with('error', "You can only activate up to {$maxLocations} branch(es) with your current plan");
        }

        // Activate selected branches
        $branchUserModel = new \App\Models\BranchUserModel();
        $locationModel = new \App\Models\MerchantLocationModel();

        foreach ($selectedBranches as $locationId) {
            // Verify location belongs to merchant
            $location = $locationModel->where('id', $locationId)
                                      ->where('merchant_id', $merchantId)
                                      ->first();

            if ($location) {
                // Activate the branch user
                $branchUser = $branchUserModel->where('location_id', $locationId)->first();
                if ($branchUser) {
                    $branchUserModel->update($branchUser['id'], ['is_active' => 1]);
                }

                // Activate the location
                $locationModel->update($locationId, ['is_active' => 1]);
            }
        }

        // Update merchant's location count
        $locationModel->updateMerchantLocationCount($merchantId);

        $count = count($selectedBranches);
        session()->setFlashdata('success', "Successfully activated {$count} branch(es)!");

        return redirect()->to('merchant/locations');
    }
}
