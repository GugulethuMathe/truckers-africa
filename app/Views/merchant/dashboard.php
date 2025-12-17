<?= view('merchant/templates/header', ['page_title' => $page_title]) ?>

<div class="px-4 lg:px-6 py-6 lg:py-8">
    <div class="max-w-4xl mx-auto">

        <?= view('merchant/components/notifications') ?>

        <!-- Subscription Warning -->
        <?= view('components/subscription_warning') ?>

        <!-- Subscription Payment Required Alert -->
        <?php
        // Only show payment alert if:
        // 1. Subscription status is 'new' or 'trial_pending' (payment not completed)
        // 2. AND merchant has completed onboarding (otherwise they haven't reached payment step yet)
        $showPaymentAlert = isset($subscription['status'])
            && in_array($subscription['status'], ['new', 'trial_pending'])
            && isset($merchant['onboarding_completed'])
            && $merchant['onboarding_completed'] == 1;
        ?>
        <?php if ($showPaymentAlert): ?>
        <div class="rounded-md bg-red-50 p-4 mb-6 border-2 border-red-400">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-sm font-semibold text-red-800 mb-2">
                        <?php if ($subscription['status'] === 'trial_pending'): ?>
                            🔒 Payment Method Required to Start Your Free Trial
                        <?php else: ?>
                            🔒 Payment Required to Activate Your Subscription
                        <?php endif; ?>
                    </h3>
                    <p class="text-sm text-red-700 mb-3">
                        <?php if ($subscription['status'] === 'trial_pending'): ?>
                            Provide your payment method to start your free trial and access all features. Your card will not be charged during the trial period.
                        <?php else: ?>
                            Complete your payment to activate your subscription and access premium features. You will be redirected to PayFast to complete the payment securely.
                        <?php endif; ?>
                    </p>
                    <div class="mt-4">
                        <form method="post" action="<?= site_url('merchant/subscription/process-payment') ?>" class="inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="plan_id" value="<?= $subscription['plan_id'] ?? '' ?>">
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 shadow-lg">
                                <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                                <?php if ($subscription['status'] === 'trial_pending'): ?>
                                    Complete Payment Setup →
                                <?php else: ?>
                                    Complete Payment Now →
                                <?php endif; ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Welcome Header -->
        <div class="mb-6">
            <?php
            // Get merchant name from session (first_name or business_name)
            $merchantName = session()->get('first_name') ?: session()->get('business_name') ?: 'Merchant';
            ?>
            <h1 class="text-xl lg:text-3xl font-bold text-gray-900">Welcome, <?= esc($merchantName) ?>!</h1>
            <p class="text-gray-600 text-xs lg:text-base">Here's your command center for managing your presence on Truckers Africa.</p>
        </div>

        <!-- Verification Status Alert -->
        <?php if ($showApprovalNotification): ?>
            <div class="rounded-md bg-green-50 p-4 mb-6 border border-green-300">
                <div class="flex">
                    <div class="flex-shrink-0"><svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg></div>
                    <div class="ml-3"><p class="text-sm font-medium text-green-800">Congratulations! Your profile has been approved and is now visible on our platform.</p></div>
                </div>
            </div>
        <?php elseif (isset($merchant['verification_status']) && $merchant['verification_status'] !== 'approved'): ?>
            <?php
                $status = $merchant['verification_status'];
                $statusConfig = [
                    'pending' => [
                        'message' => 'Your profile is currently under review. We will notify you once the verification process is complete.',
                        'bgColor' => 'bg-yellow-50',
                        'borderColor' => 'border-yellow-300',
                        'textColor' => 'text-yellow-800',
                        'iconColor' => 'text-yellow-400',
                        'iconPath' => 'M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z'
                    ],
                    'rejected' => [
                        'message' => 'We regret to inform you that your profile could not be approved at this time. Please contact support for more information.',
                        'bgColor' => 'bg-red-50',
                        'borderColor' => 'border-red-300',
                        'textColor' => 'text-red-800',
                        'iconColor' => 'text-red-400',
                        'iconPath' => 'M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z'
                    ]
                ];
                $config = $statusConfig[$status] ?? null;
            ?>
            <?php if ($config): ?>
            <div class="rounded-md <?= $config['bgColor'] ?> p-4 mb-6 border <?= $config['borderColor'] ?>">
                <div class="flex">
                    <div class="flex-shrink-0"><svg class="h-5 w-5 <?= $config['iconColor'] ?>" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="<?= $config['iconPath'] ?>" clip-rule="evenodd" /></svg></div>
                    <div class="ml-3"><p class="text-sm font-medium <?= $config['textColor'] ?>"><?= $config['message'] ?></p></div>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Flash Message for success -->
        <?php if (session()->get('message')) : ?>
            <div class="rounded-md bg-green-50 p-4 mb-6 border border-green-300">
                <div class="flex">
                    <div class="flex-shrink-0"><svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg></div>
                    <div class="ml-3"><p class="text-sm font-medium text-green-800"><?= session()->get('message') ?></p></div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Alert: Pending Listing Requests -->
        <?php if (isset($pendingRequestsCount) && $pendingRequestsCount > 0): ?>
        <div class="rounded-md bg-purple-50 p-4 mb-6 border border-purple-300">
            <div class="flex">
                <div class="flex-shrink-0"><svg class="h-5 w-5 text-purple-400" viewBox="0 0 20 20" fill="currentColor"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z" /></svg></div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-purple-800">
                        You have <?= $pendingRequestsCount ?> pending listing request<?= $pendingRequestsCount > 1 ? 's' : '' ?> from your branches
                    </h3>
                    <div class="mt-4"><a href="<?= site_url('merchant/listing-requests') ?>" class="font-medium text-purple-800 hover:text-purple-900">Review Requests →</a></div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Alert: Complete Your Profile -->
        <?php if ($profileIncomplete): ?>
        <div class="rounded-md bg-yellow-50 p-4 mb-6 border border-yellow-300">
            <div class="flex">
                <div class="flex-shrink-0"><svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg></div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Update your profile to be approved faster and appear on the listings</h3>
                    <div class="mt-4"><a href="<?= site_url('profile/merchant/edit') ?>" class="font-medium text-yellow-800 hover:text-yellow-900">Update Profile Now →</a></div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Subscription Status -->
        <div class="bg-white p-4 lg:p-6 rounded-lg shadow-md mb-6">
            <h3 class="text-base lg:text-lg font-semibold text-gray-900 mb-4">Subscription Status</h3>
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                <div>
                    <?php
                    $subscriptionStatus = $subscription['status'] ?? 'inactive';
                    $trialDays = $subscription['trial_days'] ?? 30;
                    $planName = $subscription['plan_name'] ?? 'No Plan';

                    // Show "Previous Plan" if subscription is cancelled or expired
                    $isPreviousPlan = in_array($subscriptionStatus, ['cancelled', 'expired']);
                    $planLabel = $isPreviousPlan ? 'Previous Plan' : 'Current Plan';
                    ?>
                    <p class="text-sm text-gray-500"><?= $planLabel ?></p>
                    <?php if ($subscriptionStatus === 'trial'): ?>
                        <p class="text-lg lg:text-xl font-bold text-gray-800"><?= $trialDays ?>-Day Free Trial</p>
                        <p class="text-sm text-gray-600">Then <?= esc($planName) ?> Plan</p>
                    <?php else: ?>
                        <p class="text-lg lg:text-xl font-bold text-gray-800"><?= esc($planName) ?></p>
                    <?php endif; ?>
                </div>
                <?php if ($subscriptionStatus === 'trial') : ?>
                <div class="text-left lg:text-right">
                    <?php
                    // Calculate trial end date using trial_days from plan instead of trial_ends_at
                    $createdAt = $subscription['created_at'] ?? date('Y-m-d H:i:s');
                    $trialEndDate = date('Y-m-d H:i:s', strtotime($createdAt . ' +' . $trialDays . ' days'));
                    $daysLeft = max(0, ceil((strtotime($trialEndDate) - time()) / (24 * 60 * 60)));
                    ?>
                    <p class="text-sm text-gray-500">Trial Ends On</p>
                    <p class="text-lg lg:text-xl font-bold text-green-600"><?= date('d M, Y', strtotime($trialEndDate)) ?></p>
                    <p class="text-sm text-gray-600"><?= $daysLeft ?> days left</p>
                </div>
                <?php endif; ?>
                <div class="flex-shrink-0">
                    <a href="<?= site_url('merchant/subscription') ?>" class="block w-full lg:w-auto text-center bg-brand-blue text-white font-semibold py-3 lg:py-2 px-6 lg:px-4 rounded-lg text-sm hover:bg-blue-700 transition-colors">Manage Subscription</a>
                </div>
            </div>
        </div>

        <!-- Document Verification Status -->
        <?php if (isset($verificationProgress)): ?>
        <div class="bg-white p-4 lg:p-6 rounded-lg shadow-md mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Get Verified to gain customers trust</h3>
                <span class="text-sm text-gray-600">
                    <?= \App\Models\VerificationRequirementModel::getBusinessTypeDisplayName($businessType) ?>
                </span>
            </div>

            <!-- Verification Progress -->
            <div class="mb-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">Verification Progress</span>
                    <span class="text-sm text-gray-600"><?= $verificationProgress['uploaded'] ?>/<?= $verificationProgress['total_required'] ?> documents uploaded</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: <?= $verificationProgress['completion_percentage'] ?>%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-1"><?= $verificationProgress['completion_percentage'] ?>% complete</p>
            </div>

            <!-- Verification Status -->
            <?php if ($verificationProgress['completion_percentage'] === 100): ?>
                <?php if ($merchant['is_verified'] === 'verified'): ?>
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <h4 class="text-sm font-medium text-green-800">✅ Verified Business</h4>
                                <p class="text-sm text-green-700">Your business has been verified and approved!</p>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-blue-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <h4 class="text-sm font-medium text-blue-800">📋 Under Review</h4>
                                <p class="text-sm text-blue-700">All documents submitted! Our team is reviewing your verification.</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php elseif ($verificationProgress['uploaded'] > 0): ?>
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-yellow-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <h4 class="text-sm font-medium text-yellow-800">📄 Incomplete Documents</h4>
                                <p class="text-sm text-yellow-700"><?= $verificationProgress['total_required'] - $verificationProgress['uploaded'] ?> more document(s) needed for verification.</p>
                            </div>
                        </div>
                        <a href="<?= site_url('merchant/verification') ?>" class="bg-yellow-600 text-white px-3 lg:px-4 py-2 rounded-md hover:bg-yellow-700 text-sm font-medium whitespace-nowrap">
                            Continue Upload
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-gray-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <h4 class="text-sm font-medium text-gray-800">📋 Verification Required</h4>
                                <p class="text-sm text-gray-600">Upload your documents to get verified and gain full platform access.</p>
                            </div>
                        </div>
                        <a href="<?= site_url('merchant/verification') ?>" class="bg-blue-600 text-white px-3 lg:px-4 py-2 rounded-md hover:bg-blue-700 text-sm font-medium whitespace-nowrap">
                            Start Verification
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Quick Document Status -->
            <?php if ($verificationProgress['uploaded'] > 0): ?>
                <div class="mt-4 grid grid-cols-2 lg:grid-cols-4 gap-2">
                    <?php foreach ($verificationProgress['documents'] as $docType => $docInfo): ?>
                        <div class="text-center p-2 bg-gray-50 rounded">
                            <div class="text-lg mb-1"><?= \App\Models\VerificationRequirementModel::getDocumentIcon($docType) ?></div>
                            <div class="text-xs text-gray-600 mb-1"><?= esc($docInfo['display_name']) ?></div>
                            <?php if (isset($docInfo['is_verified'])): ?>
                                <?php
                                $statusClass = '';
                                $statusText = '';
                                switch ($docInfo['is_verified']) {
                                    case 'approved':
                                        $statusClass = 'bg-green-100 text-green-800';
                                        $statusText = '✓ Approved';
                                        break;
                                    case 'pending':
                                        $statusClass = 'bg-yellow-100 text-yellow-800';
                                        $statusText = '⏳ Pending';
                                        break;
                                    case 'rejected':
                                        $statusClass = 'bg-red-100 text-red-800';
                                        $statusText = '✗ Rejected';
                                        break;
                                }
                                ?>
                                <span class="<?= $statusClass ?> px-1 py-0.5 rounded text-xs font-medium"><?= $statusText ?></span>
                            <?php else: ?>
                                <span class="bg-gray-100 text-gray-800 px-1 py-0.5 rounded text-xs font-medium">Not Uploaded</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Business Management -->
        <div class="bg-white p-4 lg:p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Business Management</h3>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

                <!-- Document Verification -->
                <a href="<?= site_url('merchant/verification') ?>" class="block p-4 bg-gradient-to-r from-blue-50 to-indigo-50 hover:from-blue-100 hover:to-indigo-100 rounded-lg border border-blue-200">
                    <div class="flex items-center mb-2">
                        <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                        </svg>
                        <h4 class="font-semibold text-blue-800">Document Verification</h4>
                    </div>
                    <p class="text-sm text-blue-700">Upload documents to get verified and unlock full platform features.</p>
                    <?php if (isset($verificationProgress)): ?>
                        <div class="mt-2">
                            <div class="w-full bg-blue-200 rounded-full h-1">
                                <div class="bg-blue-600 h-1 rounded-full" style="width: <?= $verificationProgress['completion_percentage'] ?>%"></div>
                            </div>
                            <p class="text-xs text-blue-600 mt-1"><?= $verificationProgress['completion_percentage'] ?>% complete</p>
                        </div>
                    <?php endif; ?>
                </a>

                <!-- Update Profile -->
                <a href="<?= site_url('profile/merchant/edit') ?>" class="block p-4 bg-gray-50 hover:bg-gray-100 rounded-lg">
                    <div class="flex items-center mb-2">
                        <svg class="w-5 h-5 text-gray-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                        </svg>
                        <h4 class="font-semibold text-gray-800">Update Business Profile</h4>
                    </div>
                    <p class="text-sm text-gray-600">Keep your contact and address information up to date.</p>
                </a>

                <!-- Manage Services -->
                <a href="<?= site_url('merchant/services') ?>" class="block p-4 bg-gray-50 hover:bg-gray-100 rounded-lg">
                    <div class="flex items-center mb-2">
                        <svg class="w-5 h-5 text-gray-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        <h4 class="font-semibold text-gray-800">Manage My Services</h4>
                    </div>
                    <p class="text-sm text-gray-600">Select the services you offer to appear in search results.</p>
                </a>

            </div>
        </div>
    </div>
</div>

<?= view('merchant/templates/footer') ?>