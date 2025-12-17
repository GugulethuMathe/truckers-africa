<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($page_title) ?> - Truckers Africa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .brand-color { color: #0e2140; }
        .brand-bg { background-color: #0e2140; }
        .brand-border { border-color: #0e2140; }
        .brand-hover:hover { background-color: #1a3a5f; }
        .progress-bar { background-color: #0e2140; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <div class="bg-white shadow-sm border-b-2 brand-border">
            <div class="max-w-6xl mx-auto px-4 py-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <img src="<?= site_url('assets/images/logo-icon-black.png') ?>" alt="Truckers Africa" class="h-12 w-auto">
                        <div>
                            <h1 class="text-2xl font-bold brand-color">Choose Your Subscription Plan</h1>
                            <p class="text-gray-600 mt-1">Select the plan that best fits your business needs</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Step <?= $step ?> of <?= $total_steps ?></p>
                        <div class="w-32 bg-gray-200 rounded-full h-2 mt-2">
                            <div class="progress-bar h-2 rounded-full" style="width: <?= ($step / $total_steps) * 100 ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 py-8">
            <div class="max-w-6xl mx-auto px-4">
                <!-- Success/Error Messages -->
                <?php if (session()->has('message')): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6">
                        <?= session('message') ?>
                    </div>
                <?php endif; ?>

                <?php if (session()->has('error')): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
                        <?= session('error') ?>
                    </div>
                <?php endif; ?>

                <!-- Pricing Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <?php foreach ($available_plans as $index => $plan): ?>
                        <?php $isPopular = $index === 1; // Make the middle plan popular ?>
                        
                        <div class="relative bg-white rounded-lg shadow-lg overflow-hidden <?= $isPopular ? 'ring-2 brand-border transform scale-105' : 'border border-gray-200' ?>">
                            <?php if ($isPopular): ?>
                                <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                                    <span class="brand-bg text-white px-4 py-1 rounded-full text-sm font-semibold">Most Popular</span>
                                </div>
                            <?php endif; ?>

                            <div class="p-6">
                                <!-- Plan Header -->
                                <div class="text-center mb-6 <?= $isPopular ? 'mt-4' : '' ?>">
                                    <h3 class="text-2xl font-bold text-gray-900"><?= esc($plan['name']) ?></h3>
                                    <div class="mt-4">
                                        <span class="text-4xl font-bold text-gray-900"><?= esc($plan['formatted_price']) ?></span>
                                        <span class="text-gray-600">/<?= esc($plan['billing_interval']) ?></span>
                                    </div>
                                    <?php if ($plan['has_trial']): ?>
                                        <p class="mt-2 text-sm text-green-600 font-semibold">
                                            <?= $plan['trial_days'] ?> days free trial
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <!-- Plan Description -->
                                <?php if (!empty($plan['description'])): ?>
                                    <p class="text-sm text-gray-600 text-center mb-6">
                                        <?= esc($plan['description']) ?>
                                    </p>
                                <?php endif; ?>

                                <!-- Plan Features -->
                                <div class="mb-6">
                                    <h4 class="text-sm font-medium text-gray-900 mb-3">What's included:</h4>
                                    <ul class="space-y-2">
                                        <?php if (!empty($plan['features'])): ?>
                                            <?php foreach ($plan['features'] as $feature): ?>
                                                <li class="flex items-center text-sm text-gray-600">
                                                    <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    <?= esc($feature['feature_name']) ?>
                                                </li>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <li class="flex items-center text-sm text-gray-600">
                                                <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                All basic features
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>

                                <!-- Select Plan Button -->
                                <form action="<?= site_url('merchant/onboarding/select-plan') ?>" method="post">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="plan_id" value="<?= esc($plan['id']) ?>">
                                    <button type="submit"
                                            class="w-full py-3 px-4 rounded-md font-semibold transition-colors <?= $isPopular ? 'brand-bg text-white brand-hover' : 'bg-gray-100 text-gray-900 hover:bg-gray-200' ?>">
                                        Choose Plan
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Additional Info -->
                <div class="mt-12 text-center">
                    <p class="text-gray-600 mb-4">All plans include:</p>
                    <div class="flex flex-wrap justify-center gap-6 text-sm text-gray-700">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            24/7 Platform Access
                        </div>
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Secure Payment Processing
                        </div>
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Customer Support
                        </div>
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Cancel Anytime
                        </div>
                    </div>
                </div>

                <!-- Back Button -->
                <div class="mt-8 text-center">
                    <a href="<?= site_url('merchant/onboarding') ?>" class="brand-color hover:underline font-medium">
                        ← Back to Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

