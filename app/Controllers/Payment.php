<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PlanModel;
use App\Models\SubscriptionModel;
use App\Models\MerchantModel;
use App\Models\PaymentTransactionModel;
use App\Helpers\EmailService;

/**
 * @property \CodeIgniter\HTTP\IncomingRequest $request
 */
class Payment extends BaseController
{
    public function process($planId = null)
    {
        if (!session()->has('merchant_id')) {
            return redirect()->to('login')->with('error', 'You must be logged in to make a payment.');
        }

        $merchantId = session('merchant_id');

        // Handle POST request from onboarding form
        if ($this->request->is('post')) {
            $planId = $this->request->getPost('plan_id');
            $returnUrl = $this->request->getPost('return_url');
            $cancelUrl = $this->request->getPost('cancel_url');
        } else {
            // Default URLs for self-onboarding flow
            $returnUrl = site_url('payment/success');
            $cancelUrl = site_url('payment/cancel');
        }

        $planModel = new PlanModel();
        $plan = $planModel->find($planId);

        if (!$plan) {
            return redirect()->to('auth/packages')->with('error', 'Invalid plan selected.');
        }

        $merchantModel = new MerchantModel();
        $merchant = $merchantModel->find($merchantId);

        if (!$merchant) {
            return redirect()->to('login')->with('error', 'Could not find your merchant profile.');
        }

        // Data for PayFast
        // Determine initial amount based on trial and plan price
        $hasTrial = ($plan['trial_days'] > 0);
        $isFree = ($plan['price'] == 0);

        // Convert USD price to ZAR for PayFast (PayFast only accepts ZAR)
        $currencyService = new \App\Services\CurrencyService();
        $planPriceUSD = $plan['price']; // Plan price is stored in USD
        $planPriceZAR = $currencyService->convertAmount($planPriceUSD, 'USD', 'ZAR');

        // If conversion fails, use a fallback rate (1 USD = 18.50 ZAR approximately)
        if ($planPriceZAR === null) {
            $planPriceZAR = $planPriceUSD * 18.50;
            log_message('warning', 'Currency conversion failed, using fallback rate for PayFast payment');
        }

        // Initial amount logic:
        // - If has trial (regardless of price): R0.00 initially
        // - If no trial and paid plan: charge full price initially
        // - If free plan: R0.00 initially
        $initialAmount = ($hasTrial || $isFree) ? 0.00 : $planPriceZAR;

        // Calculate billing date (when first charge occurs)
        $trialDays = $hasTrial ? $plan['trial_days'] : 0;
        $billingDate = date('Y-m-d', strtotime('+' . $trialDays . ' days'));

        $data = [];
        $data['merchant_id'] = getenv('payfast.merchantId');
        $data['merchant_key'] = getenv('payfast.merchantKey');
        $data['return_url'] = $returnUrl ?? site_url('payment/success');
        $data['cancel_url'] = $cancelUrl ?? site_url('payment/cancel');
        $data['notify_url'] = site_url('payment/notify');

        // Buyer details
        $ownerNameParts = explode(' ', $merchant['owner_name'], 2);
        $data['name_first'] = trim($ownerNameParts[0]);
        $data['name_last'] = isset($ownerNameParts[1]) && trim($ownerNameParts[1]) !== '' ? trim($ownerNameParts[1]) : 'Unknown';
        $data['email_address'] = trim($merchant['email']);

        // Transaction details
        $data['m_payment_id'] = 'PLAN-' . $planId . '-MERCHANT-' . $merchantId . '-' . time(); // Unique payment ID
        $data['amount'] = number_format($initialAmount, 2, '.', '');
        $data['item_name'] = trim($plan['name'] . ' Subscription');
        $data['item_description'] = trim($plan['description']);

        // Subscription details
        $data['subscription_type'] = '1'; // 1 for subscription (as string)
        $data['billing_date'] = $billingDate;
        $data['recurring_amount'] = number_format($planPriceZAR, 2, '.', ''); // Recurring amount in ZAR
        $data['frequency'] = '3'; // 3 for monthly (as string)
        $data['cycles'] = '0'; // 0 for indefinite (as string)

        // Generate signature using helper function
        helper('payfast');
        $passphrase = getenv('payfast.passphrase');
        // Only use passphrase if it's actually set (not empty string)
        $passphraseToUse = (!empty($passphrase) && $passphrase !== '') ? $passphrase : null;
        $data['signature'] = generatePayFastSignature($data, $passphraseToUse);

        // Log debug info for troubleshooting
        logPayFastDebug('Payment signature generated', [
            'merchant_id' => $data['merchant_id'],
            'amount' => $data['amount'],
            'item_name' => $data['item_name'],
            'passphrase_used' => $passphraseToUse ? 'YES' : 'NO',
            'signature' => $data['signature'],
            'all_data' => $data
        ]);

        $viewData = [
            'payfast_data' => $data,
            'payfast_url' => getenv('payfast.processUrl'),
            'page_title' => 'Complete Your Payment',
            'page_class' => 'bg-gray-900 text-slate-200',
        ];

        return view('templates/home-header', $viewData)
             . view('payment/process', $viewData)
             . view('templates/home-footer');
    }

    public function success()
    {
        // Check if merchant has completed onboarding
        $merchantId = session('merchant_id');
        if ($merchantId) {
            $merchantModel = new MerchantModel();
            $merchant = $merchantModel->find($merchantId);

            // TEMPORARY FIX: Manually activate subscription on success page
            // This is needed because PayFast sandbox ITN cannot reach localhost
            $subscriptionModel = new SubscriptionModel();
            $subscription = $subscriptionModel->getCurrentSubscription($merchantId);

            if ($subscription && in_array($subscription['status'], ['new', 'trial_pending'])) {
                $updateData = [];

                if ($subscription['status'] === 'trial_pending') {
                    // Trial plan - start trial period
                    $updateData['status'] = 'trial';
                    $updateData['current_period_starts_at'] = date('Y-m-d H:i:s');
                    $updateData['current_period_ends_at'] = date('Y-m-d H:i:s', strtotime('+1 month'));
                    // trial_ends_at should already be set
                } else {
                    // Paid plan - activate immediately
                    $updateData['status'] = 'active';
                    $updateData['trial_ends_at'] = null;
                    $updateData['current_period_starts_at'] = date('Y-m-d H:i:s');
                    $updateData['current_period_ends_at'] = date('Y-m-d H:i:s', strtotime('+1 month'));
                }

                $subscriptionModel->update($subscription['id'], $updateData);
                log_message('info', 'Subscription manually activated on success page for merchant ID: ' . $merchantId);
            }

            // If onboarding not completed, mark it as complete
            if ($merchant && $merchant['onboarding_completed'] == 0) {
                $merchantModel->update($merchantId, ['onboarding_completed' => 1]);
                return redirect()->to('merchant/dashboard')->with('message', 'Welcome to Truckers Africa! Your payment was successful and your account is now active.');
            }

            // Check if this is a reactivation and merchant has inactive branches
            if (session()->get('pending_branch_activation')) {
                session()->remove('pending_branch_activation');

                // Check if merchant has any inactive branches
                $locationModel = new \App\Models\MerchantLocationModel();
                $inactiveBranches = $locationModel->where('merchant_id', $merchantId)
                                                  ->where('is_primary', 0)
                                                  ->where('is_active', 0)
                                                  ->countAllResults();

                if ($inactiveBranches > 0) {
                    return redirect()->to('merchant/subscription/select-branches')
                        ->with('success', 'Payment successful! Your subscription is now active. Please select which branches you\'d like to activate.');
                }
            }
        }

        // Check if this was a prorata plan change payment
        $planChangePreview = session()->get('plan_change_preview');
        if ($planChangePreview) {
            // Clear the plan change session
            session()->remove('plan_change_preview');

            // Redirect to subscription page with upgrade message
            return redirect()->to('merchant/subscription')->with('success', 'Payment successful! Your plan has been upgraded. Please refresh the page if you don\'t see the changes.');
        }

        return redirect()->to('merchant/dashboard')->with('message', 'Payment successful! Your subscription is now active.');
    }

    public function cancel()
    {
        // Check if this is from onboarding
        $merchantId = session('merchant_id');
        if ($merchantId) {
            $merchantModel = new MerchantModel();
            $merchant = $merchantModel->find($merchantId);

            // If onboarding not completed, redirect back to onboarding payment
            if ($merchant && $merchant['onboarding_completed'] == 0) {
                return redirect()->to('merchant/onboarding/payment')->with('error', 'Payment was cancelled. Please complete your payment to activate your account.');
            }
        }

        return redirect()->to('auth/packages')->with('error', 'Payment was cancelled. Please choose a plan to continue.');
    }

    /**
     * TEST ONLY: Manually activate subscription for testing on localhost
     * This simulates what PayFast ITN would do in production
     * Remove or secure this endpoint before going to production!
     */
    public function testActivateSubscription($merchantId = null)
    {
        // Only allow in development environment
        if (ENVIRONMENT !== 'development') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'This endpoint is only available in development mode'
            ])->setStatusCode(403);
        }

        // Use session merchant_id if not provided
        if (!$merchantId) {
            $merchantId = session('merchant_id');
        }

        if (!$merchantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No merchant ID provided'
            ])->setStatusCode(400);
        }

        $subscriptionModel = new SubscriptionModel();
        $subscription = $subscriptionModel->getCurrentSubscription($merchantId);

        if (!$subscription) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No subscription found for merchant ID: ' . $merchantId
            ])->setStatusCode(404);
        }

        if (!in_array($subscription['status'], ['new', 'trial_pending'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Subscription is already active or not in pending state. Current status: ' . $subscription['status']
            ])->setStatusCode(400);
        }

        $updateData = [];

        if ($subscription['status'] === 'trial_pending') {
            // Trial plan - start trial period
            $updateData['status'] = 'trial';
            $updateData['current_period_starts_at'] = date('Y-m-d H:i:s');
            $updateData['current_period_ends_at'] = date('Y-m-d H:i:s', strtotime('+1 month'));
            $updateData['payfast_token'] = 'TEST-TOKEN-' . time(); // Fake token for testing
        } else {
            // Paid plan - activate immediately
            $updateData['status'] = 'active';
            $updateData['trial_ends_at'] = null;
            $updateData['current_period_starts_at'] = date('Y-m-d H:i:s');
            $updateData['current_period_ends_at'] = date('Y-m-d H:i:s', strtotime('+1 month'));
            $updateData['payfast_token'] = 'TEST-TOKEN-' . time(); // Fake token for testing
        }

        $subscriptionModel->update($subscription['id'], $updateData);

        // Mark onboarding as complete
        $merchantModel = new MerchantModel();
        $merchantModel->update($merchantId, ['onboarding_completed' => 1]);

        log_message('info', 'TEST: Subscription manually activated for merchant ID: ' . $merchantId);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Subscription activated successfully',
            'subscription' => [
                'id' => $subscription['id'],
                'old_status' => $subscription['status'],
                'new_status' => $updateData['status'],
                'merchant_id' => $merchantId
            ]
        ]);
    }

    public function notify()
    {
        log_message('info', 'PayFast ITN received.');

        $pfData = $this->request->getPost();
        log_message('info', 'PayFast ITN Data: ' . json_encode($pfData));

        // --- 1. Validate the ITN request with PayFast ---
        $ch = curl_init(getenv('payfast.validateUrl'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($pfData));
        $response = curl_exec($ch);
        curl_close($ch);

        log_message('info', 'PayFast ITN Validation Response: ' . $response);

        if ($response !== 'VALID') {
            log_message('error', 'PayFast ITN Validation Failed. Response: ' . $response);
            return $this->response->setStatusCode(400); // Bad Request
        }

        // --- 2. Check the payment status ---
        $paymentStatus = $pfData['payment_status'];

        // Handle failed payments - mark subscription as expired
        if (in_array($paymentStatus, ['FAILED', 'CANCELLED'])) {
            log_message('warning', 'PayFast ITN: Payment failed or cancelled. Status: ' . $paymentStatus);

            try {
                $subscriptionModel = new SubscriptionModel();
                $merchantModel = new MerchantModel();
                $token = $pfData['token'] ?? null;

                // Try to find subscription by token (for recurring payments)
                if ($token) {
                    $subscription = $subscriptionModel->where('payfast_token', $token)->first();

                    if ($subscription) {
                        // Update subscription status to expired
                        $oldStatus = $subscription['status'];
                        $subscriptionModel->update($subscription['id'], [
                            'status' => 'expired'
                        ]);

                        log_message('info', 'Subscription ID ' . $subscription['id'] . ' marked as expired due to payment failure. Merchant ID: ' . $subscription['merchant_id']);

                        // Record failed transaction
                        $transactionModel = new PaymentTransactionModel();
                        $transactionData = [
                            'merchant_id' => $subscription['merchant_id'],
                            'subscription_id' => $subscription['id'],
                            'transaction_id' => $pfData['pf_payment_id'] ?? $pfData['m_payment_id'] ?? 'FAILED-' . time(),
                            'amount' => (float) ($pfData['amount_gross'] ?? 0),
                            'currency' => 'ZAR',
                            'status' => 'failed',
                            'payment_method' => 'PayFast',
                            'payfast_payment_id' => $pfData['pf_payment_id'] ?? null,
                            'payfast_payment_status' => $paymentStatus,
                            'processed_at' => date('Y-m-d H:i:s')
                        ];

                        $transactionModel->insert($transactionData);
                        log_message('info', 'Failed transaction recorded for subscription ID: ' . $subscription['id']);

                        // Get merchant details for email
                        $merchant = $merchantModel->find($subscription['merchant_id']);

                        if ($merchant) {
                            // Send payment failed email notification
                            $emailService = new EmailService();
                            $emailService->sendPaymentFailedNotification(
                                [
                                    'business_name' => $merchant['business_name'],
                                    'email' => $merchant['email']
                                ],
                                [
                                    'amount' => $transactionData['amount'],
                                    'date' => $transactionData['processed_at'],
                                    'reason' => null // PayFast doesn't provide specific failure reason in ITN
                                ]
                            );
                            log_message('info', 'Payment failed email sent to merchant ID: ' . $subscription['merchant_id']);

                            // Send subscription status change email notification
                            $planModel = new PlanModel();
                            $plan = $planModel->find($subscription['plan_id']);

                            $emailService->sendSubscriptionStatusChange(
                                [
                                    'business_name' => $merchant['business_name'],
                                    'email' => $merchant['email']
                                ],
                                [
                                    'old_status' => $oldStatus,
                                    'new_status' => 'expired',
                                    'plan_name' => $plan['name'] ?? 'Unknown Plan',
                                    'expiry_date' => date('Y-m-d')
                                ]
                            );
                            log_message('info', 'Subscription status change email sent to merchant ID: ' . $subscription['merchant_id']);
                        }
                    } else {
                        log_message('warning', 'PayFast payment failure: Could not find subscription for token: ' . $token);
                    }
                } else {
                    log_message('warning', 'PayFast payment failure: No token provided in ITN data');
                }
            } catch (\Exception $e) {
                log_message('error', 'Error handling failed payment: ' . $e->getMessage());
            }

            return $this->response->setStatusCode(200);
        }

        // Only process successful payments
        if ($paymentStatus !== 'COMPLETE') {
            log_message('info', 'PayFast ITN: Payment status is not complete. Status: ' . $paymentStatus);
            return $this->response->setStatusCode(200);
        }

        // --- 3. Process the payment ---
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $subscriptionModel = new SubscriptionModel();
            $planModel = new PlanModel();
            $transactionModel = new PaymentTransactionModel();

            // Check if this is an automatic recurring payment from PayFast
            // PayFast recurring payments will have a token but may not have custom m_payment_id
            $token = $pfData['token'] ?? null;
            $isRecurringPayment = !empty($token) && !isset($pfData['m_payment_id']);

            // If it's a recurring payment, look up subscription by token
            if ($isRecurringPayment) {
                log_message('info', 'PayFast recurring payment detected with token: ' . $token);

                $subscription = $subscriptionModel->where('payfast_token', $token)->first();

                if (!$subscription) {
                    throw new \Exception('Subscription not found for token: ' . $token);
                }

                $merchantId = $subscription['merchant_id'];
                $planId = $subscription['plan_id'];

                // Renew the subscription for next billing period
                $success = $subscriptionModel->renewSubscription($subscription['id']);

                if (!$success) {
                    throw new \Exception('Failed to renew subscription ID: ' . $subscription['id']);
                }

                log_message('info', 'Automatic recurring payment processed: Subscription ' . $subscription['id'] . ' renewed for merchant ' . $merchantId);

            } else {
                // Extract IDs from m_payment_id for manual payments
                $paymentIdParts = explode('-', $pfData['m_payment_id']);

                // Check if this is a payment method update (starts with UPDATE)
                $isUpdate = ($paymentIdParts[0] ?? '') === 'UPDATE';

                // Check if this is a prorata payment (starts with PRORATA)
                $isProrata = ($paymentIdParts[0] ?? '') === 'PRORATA';

                // Check if this is a subscription renewal (starts with RENEWAL)
                $isRenewal = ($paymentIdParts[0] ?? '') === 'RENEWAL';

            if ($isProrata) {
                // Format: PRORATA-{subscriptionId}-{newPlanId}-{timestamp}
                // Use custom fields for more reliable data
                $subscriptionId = $pfData['custom_int1'] ?? $paymentIdParts[1] ?? null;
                $newPlanId = $pfData['custom_int2'] ?? $paymentIdParts[2] ?? null;

                if (!$subscriptionId || !$newPlanId) {
                    throw new \Exception('Could not parse subscription or plan ID from prorata payment: ' . $pfData['m_payment_id']);
                }

                // Get subscription
                $subscription = $subscriptionModel->find($subscriptionId);
                if (!$subscription) {
                    throw new \Exception('Subscription not found for ID: ' . $subscriptionId);
                }

                $merchantId = $subscription['merchant_id'];
                $planId = $newPlanId; // The new plan they're upgrading to

                // Apply the plan change
                $prorataService = new \App\Services\ProrataBillingService();

                log_message('info', 'Attempting to apply plan change: Subscription ' . $subscriptionId . ' to plan ' . $newPlanId);

                $success = $prorataService->applyPlanChange($subscriptionId, $newPlanId);

                if (!$success) {
                    log_message('error', 'Failed to apply plan change for subscription ID: ' . $subscriptionId . ' to plan: ' . $newPlanId);
                    throw new \Exception('Failed to apply plan change for subscription ID: ' . $subscriptionId);
                }

                log_message('info', 'Prorata plan change successfully applied: Subscription ' . $subscriptionId . ' upgraded to plan ' . $newPlanId);

                // Verify the change was applied
                $updatedSubscription = $subscriptionModel->find($subscriptionId);
                log_message('info', 'Subscription after update - Plan ID: ' . $updatedSubscription['plan_id'] . ', Status: ' . $updatedSubscription['status']);

            } elseif ($isRenewal) {
                // Format: RENEWAL-{subscriptionId}-{timestamp}
                $subscriptionId = $pfData['custom_int1'] ?? $paymentIdParts[1] ?? null;

                if (!$subscriptionId) {
                    throw new \Exception('Could not parse subscription ID from renewal payment: ' . $pfData['m_payment_id']);
                }

                // Get subscription
                $subscription = $subscriptionModel->find($subscriptionId);
                if (!$subscription) {
                    throw new \Exception('Subscription not found for ID: ' . $subscriptionId);
                }

                $merchantId = $subscription['merchant_id'];
                $planId = $subscription['plan_id'];

                // Renew the subscription for next billing period
                $success = $subscriptionModel->renewSubscription($subscriptionId);

                if (!$success) {
                    throw new \Exception('Failed to renew subscription ID: ' . $subscriptionId);
                }

                log_message('info', 'Subscription renewed: Subscription ' . $subscriptionId . ' for merchant ' . $merchantId);

            } elseif ($isUpdate) {
                // Format: UPDATE-PLAN-{planId}-MERCHANT-{merchantId}-{timestamp}
                $planId = $paymentIdParts[2] ?? null;
                $merchantId = $paymentIdParts[4] ?? null;
            } else {
                // Format: PLAN-{planId}-MERCHANT-{merchantId}{timestamp}
                $planId = $paymentIdParts[1] ?? null;
                $merchantId = $paymentIdParts[3] ?? null;
            }

                if (!$planId || !$merchantId) {
                    throw new \Exception('Could not parse plan or merchant ID from m_payment_id: ' . $pfData['m_payment_id']);
                }

                $plan = $planModel->find($planId);
                if (!$plan) {
                    throw new \Exception('Plan not found for ID: ' . $planId);
                }

                // --- 4. Get or find subscription ---
                if (!isset($subscription)) {
                    $subscription = $subscriptionModel->where('merchant_id', $merchantId)->where('plan_id', $planId)->first();

                    if (!$subscription) {
                        throw new \Exception('Subscription not found for merchant ID: ' . $merchantId . ' and plan ID: ' . $planId);
                    }
                }
            }

            // --- 5. Update the subscription with new token ---
            // Skip this for prorata, renewal, and recurring payments as they're already handled
            if (!$isRecurringPayment && !isset($isProrata) || !$isProrata) {
                if (!isset($isRenewal) || !$isRenewal) {
                    $updateData = [
                        'payfast_token' => $pfData['token'] ?? null, // Save/update the subscription token
                    ];

                    // Update status based on current state:
                    // - 'trial_pending' → 'trial' (payment method captured, trial starts)
                    // - 'new' → 'active' (paid plan, payment completed)
                    // - other non-active statuses → 'active'
                    if ($subscription['status'] !== 'active' || (isset($isUpdate) && !$isUpdate)) {
                        if ($subscription['status'] === 'trial_pending') {
                            // Trial plan - payment method captured, start trial period
                            $updateData['status'] = 'trial';
                            // trial_ends_at is already set, don't modify it
                            $updateData['current_period_starts_at'] = date('Y-m-d H:i:s');
                            $updateData['current_period_ends_at'] = date('Y-m-d H:i:s', strtotime('+1 month'));
                        } else {
                            // Paid plan or trial expired - activate immediately
                            $updateData['status'] = 'active';
                            $updateData['trial_ends_at'] = null; // Trial is over (if any)
                            $updateData['current_period_starts_at'] = date('Y-m-d H:i:s');
                            $updateData['current_period_ends_at'] = date('Y-m-d H:i:s', strtotime('+1 month'));
                        }
                    }

                    $subscriptionModel->update($subscription['id'], $updateData);

                    $logMessage = (isset($isUpdate) && $isUpdate)
                        ? 'Payment method updated for merchant ID: ' . $merchantId
                        : 'Subscription activated for merchant ID: ' . $merchantId;
                    log_message('info', $logMessage);
                }
            }

            // --- 5.5. Mark onboarding as complete if not already ---
            $merchantModel = new MerchantModel();
            $merchant = $merchantModel->find($merchantId);
            if ($merchant && $merchant['onboarding_completed'] == 0) {
                $merchantModel->update($merchantId, ['onboarding_completed' => 1]);
                log_message('info', 'Onboarding marked as complete for merchant ID: ' . $merchantId);
            }

            // --- 6. Create transaction record ---
            $transactionData = [
                'merchant_id' => $merchantId,
                'subscription_id' => $subscription['id'],
                'transaction_id' => $pfData['pf_payment_id'] ?? $pfData['m_payment_id'],
                'amount' => (float) $pfData['amount_gross'],
                'currency' => 'ZAR',
                'status' => 'completed',
                'payment_method' => 'PayFast',
                'payfast_payment_id' => $pfData['pf_payment_id'] ?? null,
                'payfast_payment_status' => $pfData['payment_status'],
                'processed_at' => date('Y-m-d H:i:s')
            ];
            
            $transactionModel->insert($transactionData);
            log_message('info', 'Transaction record created for payment ID: ' . $pfData['m_payment_id']);

            // Send email notifications
            try {
                $emailService = new EmailService();
                $plan = $planModel->find($planId);

                $merchantData = [
                    'email' => $merchant['email'],
                    'business_name' => $merchant['business_name']
                ];

                // Send payment confirmation
                $paymentData = [
                    'amount' => $pfData['amount_gross'],
                    'reference' => $pfData['pf_payment_id'] ?? $pfData['m_payment_id'],
                    'date' => date('Y-m-d H:i:s'),
                    'description' => 'Subscription Payment - ' . ($plan['name'] ?? 'Plan')
                ];
                $emailService->sendPaymentConfirmation($merchantData, $paymentData);

                // Send subscription activation email (if not renewal or update)
                if (!isset($isRenewal) || !$isRenewal) {
                    $subscriptionData = [
                        'plan_name' => $plan['name'] ?? 'Subscription Plan',
                        'start_date' => $subscription['start_date'],
                        'end_date' => $subscription['end_date'],
                        'amount' => $pfData['amount_gross']
                    ];
                    $emailService->sendSubscriptionActivation($merchantData, $subscriptionData);
                }

            } catch (\Exception $emailError) {
                // Log email error but don't fail the payment
                log_message('error', 'Failed to send payment emails: ' . $emailError->getMessage());
            }

            $db->transComplete();

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'PayFast ITN Processing Error: ' . $e->getMessage());
            return $this->response->setStatusCode(500); // Internal Server Error
        }

        return $this->response->setStatusCode(200);
    }
}
