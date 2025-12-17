<?php helper('currency'); ?>
<main class="min-h-screen <?= esc($page_class ?? '') ?>"
    <div class="container mx-auto px-4 py-10">
        <div class="mb-8 text-center">
            <h1 class="text-3xl md:text-4xl font-bold text-white">
                <?= esc($page_title ?? 'Search') ?>
            </h1>
            <?php if (!empty($search_query)): ?>
                <p class="mt-2 text-slate-300">Results for "<?= esc($search_query) ?>"</p>
            <?php endif; ?>
        </div>

        <!-- Category filter row -->
        <?php if (!empty($categories)): ?>
        <div class="bg-gray-800/50 rounded-lg p-3 mb-6">
            <div class="flex flex-wrap gap-2 justify-center">
                <a href="<?= site_url('search') ?>" class="category-filter bg-gray-700 text-white px-3 py-1 rounded-full text-xs font-medium <?= empty($search_query) && empty($_GET['category']) ? 'bg-primary' : 'hover:bg-gray-600' ?>">
                    All Services
                </a>
                <?php foreach ($categories as $category): ?>
                    <?php
                        $isActive = (isset($_GET['category']) && $_GET['category'] == $category['id']) ||
                                   (!isset($_GET['category']) && $search_query == $category['name']);
                        $categoryUrl = site_url('search?category=' . $category['id'] . ($search_query ? '&q=' . urlencode($search_query) : ''));
                    ?>
                    <a href="<?= $categoryUrl ?>" class="category-filter <?= $isActive ? 'bg-primary' : 'bg-gray-700 hover:bg-gray-600' ?> text-white px-3 py-1 rounded-full text-xs font-medium">
                        <?= esc($category['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (isset($merchants_with_listings)): ?>
            <?php if (!empty($merchants_with_listings)): ?>
                <div class="mb-4 text-center">
                    <p class="text-slate-300">Found <?= count($merchants_with_listings) ?> merchant(s) with <?= $total_results ?? 0 ?> service(s)</p>
                </div>
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <?php foreach ($merchants_with_listings as $merchant): ?>
                        <div class="bg-gray-800 rounded-lg overflow-hidden shadow-lg hover:-translate-y-1 transition-transform duration-200">
                            <div class="p-5">
                                <h3 class="text-xl font-semibold text-white mb-2"><?= esc($merchant['business_name'] ?? 'Merchant') ?></h3>
                                <?php if (!empty($merchant['physical_address'])): ?>
                                    <p class="text-gray-400 text-sm mb-3 truncate"><?= esc($merchant['physical_address']) ?></p>
                                <?php endif; ?>

                                <!-- Display merchant's listings -->
                                <div class="space-y-2 mb-4">
                                    <?php foreach (array_slice($merchant['listings'], 0, 3) as $listing): ?>
                                        <div class="bg-gray-700/50 rounded p-2">
                                            <h4 class="text-white font-medium text-sm"><?= esc($listing['title']) ?></h4>
                                            <?php if (!empty($listing['price'])): ?>
                                                <p class="text-green-400 text-xs"><?= display_listing_price($listing) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if (count($merchant['listings']) > 3): ?>
                                        <p class="text-gray-400 text-xs">+<?= count($merchant['listings']) - 3 ?> more services</p>
                                    <?php endif; ?>
                                </div>

                                <a href="<?= site_url('merchant/profile/' . $merchant['id']) ?>" class="inline-block bg-secondary text-gray-900 font-semibold py-2 px-4 rounded-full hover:bg-yellow-400 transition-colors text-sm">
                                    View Profile
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-16">
                    <div class="bg-gray-800/50 rounded-lg p-10 inline-block">
                        <i class="fas fa-search text-5xl text-gray-500 mb-4"></i>
                        <h3 class="text-2xl font-semibold text-white mb-2">No Results Found</h3>
                        <p class="text-slate-300">We couldn't find any services matching "<?= esc($search_query) ?>".</p>
                        <div class="mt-6 text-slate-400">
                            <p class="mb-2">Try:</p>
                            <ul class="space-y-1 text-sm">
                                <li>• Using different keywords</li>
                                <li>• Searching a broader term</li>
                                <li>• Checking your spelling</li>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php elseif (isset($merchants)): ?>
            <?php if (!empty($merchants)): ?>
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <?php foreach ($merchants as $merchant): ?>
                        <a href="<?= site_url('merchant/profile/' . $merchant['id']) ?>" class="flex flex-col bg-gray-800 rounded-lg overflow-hidden shadow-lg hover:-translate-y-1 transition-transform duration-200 group">
                            <?php
                                $imageUrl = !empty($merchant['business_image_url'])
                                    ? base_url($merchant['business_image_url'])
                                    : (!empty($merchant['profile_image_url'])
                                        ? base_url($merchant['profile_image_url'])
                                        : 'https://via.placeholder.com/600x400.png/2d3748/FFFFFF?text=' . urlencode($merchant['business_name'] ?? 'Service'));
                            ?>
                            <div class="w-full h-40 bg-cover bg-center" style="background-image: url('<?= $imageUrl ?>')"></div>
                            <div class="p-5">
                                <h3 class="text-xl font-semibold text-white truncate"><?= esc($merchant['business_name'] ?? 'Merchant') ?></h3>
                                <?php if (!empty($merchant['physical_address'])): ?>
                                    <p class="text-gray-400 mt-1 text-sm truncate"><?= esc($merchant['physical_address']) ?></p>
                                <?php endif; ?>
                                <div class="mt-4">
                                    <span class="inline-block bg-secondary text-gray-900 font-semibold py-2 px-4 rounded-full group-hover:bg-yellow-400 transition-colors">View Profile</span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-16">
                    <div class="bg-gray-800/50 rounded-lg p-10 inline-block">
                        <i class="fas fa-search text-5xl text-gray-500 mb-4"></i>
                        <h3 class="text-2xl font-semibold text-white mb-2">No Results Found</h3>
                        <p class="text-slate-300">We couldn't find any merchants matching "<?= esc($search_query) ?>".</p>
                        <div class="mt-6 text-slate-400">
                            <p class="mb-2">Try:</p>
                            <ul class="space-y-1 text-sm">
                                <li>• Using different keywords</li>
                                <li>• Searching a broader term</li>
                                <li>• Checking your spelling</li>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php elseif (isset($services) && !empty($services)): ?>
            <div class="mb-6 text-center">
                <p class="text-slate-300">Browse available services</p>
            </div>
            <div class="flex flex-wrap justify-center gap-3">
                <?php foreach ($services as $service): ?>
                    <a href="<?= site_url('search?q=' . urlencode($service['name'])) ?>" class="bg-gray-800/60 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <?= esc($service['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-16">
                <div class="bg-gray-800/50 rounded-lg p-10 inline-block">
                    <i class="fas fa-info-circle text-5xl text-gray-500 mb-4"></i>
                    <h3 class="text-2xl font-semibold text-white mb-2">Nothing to show yet</h3>
                    <p class="text-slate-300">Try searching for a service above.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>
