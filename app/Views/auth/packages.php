<?= view('templates/home-header', ['page_title' => 'Choose Your Plan', 'page_class' => '']) ?>

<div class="container mx-auto px-4 py-8">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-white-800">Our Pricing Plans</h1>
        <p class="text-lg text-white-600 mt-2">Choose the plan that's right for your business.</p>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline"><?= session()->getFlashdata('error') ?></span>
        </div>
    <?php endif; ?>

    <div class="flex flex-wrap justify-center gap-8">
        <?php if (!empty($plans)): ?>
            <?php foreach ($plans as $plan): ?>
                <div class="w-full max-w-sm bg-white rounded-lg shadow-lg border border-gray-200 transform hover:scale-105 transition-transform duration-300">
                    <div class="p-6">
                        <h3 class="text-2xl font-bold text-center text-gray-800"><?= esc($plan['name']) ?></h3>
                        <p class="text-center text-gray-500 mt-2"><?= esc($plan['description']) ?></p>

                        <?php if ($plan['has_trial'] && $plan['trial_days'] > 0): ?>
                            <div class="text-center mt-4">
                                <span class="bg-green-100 text-green-800 text-sm font-medium mr-2 px-2.5 py-0.5 rounded-full"><?= esc($plan['trial_days']) ?> Days Free Trial</span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="text-center my-6">
                            <span class="font-extrabold text-gray-900" style="font-size: 2.3rem; line-height: 1;">R<?= esc(number_format($plan['price'], 2)) ?></span>
                            <span class="text-base lg:text-lg text-gray-500">/ <?= esc($plan['billing_interval']) ?></span>
                        </div>

                        <ul class="text-gray-600 mb-8 space-y-3">
                            <?php if (!empty($plan['features'])): ?>
                                <?php foreach ($plan['features'] as $feature): ?>
                                    <li class="flex items-center">
                                        <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor"><path d="M5 13l4 4L19 7"></path></svg>
                                        <span><?= esc($feature['name']) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="flex items-center text-gray-500">
                                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor"><path d="M6 18L18 6M6 6l12 12"></path></svg>
                                    <span>No features listed for this plan.</span>
                                </li>
                            <?php endif; ?>
                        </ul>

                        <a href="<?= site_url('auth/select-plan/' . $plan['id']) ?>" class="block w-full text-center bg-indigo-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-indigo-700 transition-colors duration-300">
                            Select Plan
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-10">
                <p class="text-gray-600 text-lg">No subscription plans are available at the moment. Please check back later.</p>
            </div>
        <?php endif; ?>
    </div>
</div>



<?= view('templates/home-footer') ?>
