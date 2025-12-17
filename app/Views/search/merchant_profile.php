<main>
    <div class="container mx-auto p-4 py-20">
        <div class="max-w-4xl mx-auto">
            <!-- =================================== -->
            <!-- MERCHANT HEADER                   -->
            <!-- =================================== -->
            <section class="text-center mb-8">
                <h1 class="text-4xl lg:text-5xl font-condensed tracking-wider text-white uppercase"><?= esc($merchant['business_name']) ?></h1>
                <div class="mt-3 flex items-center justify-center space-x-2 text-yellow-400">
                    <i class="ri-star-s-fill text-2xl"></i>
                    <span class="text-xl font-bold text-white"><?= number_format($average_rating, 1) ?></span>
                    <span class="text-slate-400">(<?= count($reviews) ?> reviews)</span>
                </div>
            </section>

            <!-- =================================== -->
            <!-- MAIN CONTENT GRID                 -->
            <!-- =================================== -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

                <!-- Left Column (Main Details & Reviews) -->
                <div class="md:col-span-2 space-y-8">
                    <!-- Business Details Section -->
                    <div class="bg-gray-800/50 p-6 rounded-lg">
                        <h2 class="font-condensed text-2xl text-white tracking-wider border-b border-gray-700 pb-2 mb-4">About This Business</h2>
                        <div class="space-y-4 text-slate-300">
                            <p><?= esc($merchant['profile_description'] ?? 'No business description has been provided.') ?></p>
                            
                            <div>
                                <h3 class="font-semibold text-white mb-2">Services Offered:</h3>
                                <?php if (!empty($services)): ?>
                                    <div class="flex flex-wrap gap-2">
                                    <?php foreach($services as $service): ?>
                                        <span class="bg-primary text-white text-xs font-semibold mr-2 px-3 py-1.5 rounded-full"><?= esc($service['name']) ?></span>
                                    <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-slate-400">No specific services are listed by this merchant.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Reviews Section -->
                    <div class="bg-gray-800/50 p-6 rounded-lg">
                        <h2 class="font-condensed text-2xl text-white tracking-wider border-b border-gray-700 pb-2 mb-4">Driver Reviews</h2>
                        <div class="space-y-6">
                            <?php if (!empty($reviews)): ?>
                                <?php foreach($reviews as $review): ?>
                                <div class="border-b border-gray-700 pb-4 last:border-b-0 last:pb-0">
                                    <div class="flex justify-between items-center mb-1">
                                        <h4 class="font-bold text-white"><?= esc($review['driver_name']) ?></h4>
                                        <span class="text-xs text-slate-400"><?= date('d M Y', strtotime($review['created_at'])) ?></span>
                                    </div>
                                    <div class="flex items-center mb-2">
                                        <?php for($i = 0; $i < 5; $i++): ?>
                                            <i class="ri-star-s-fill <?= $i < $review['rating'] ? 'text-yellow-400' : 'text-gray-600' ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <p class="text-slate-300"><?= esc($review['comment']) ?></p>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-slate-400 text-center py-4">Be the first to leave a review for this merchant!</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Right Column (Contact & Map) -->
                <div class="space-y-6">
                    <div class="bg-gray-800/50 p-6 rounded-lg">
                        <h3 class="font-condensed text-xl text-white tracking-wider mb-4">Contact & Location</h3>
                        <div class="space-y-4">
                            <!-- Contact Number -->
                             <a href="tel:<?= esc($merchant['business_contact_number']) ?>" class="flex items-start space-x-3 text-slate-300 hover:text-secondary group">
                                <i class="ri-phone-fill text-xl text-gray-400 group-hover:text-secondary"></i>
                                <div>
                                    <span class="font-semibold text-white">Call</span>
                                    <p class="text-sm"><?= esc($merchant['business_contact_number']) ?></p>
                                </div>
                            </a>
                            <!-- WhatsApp -->
                            <?php if(!empty($merchant['business_whatsapp_number'])): ?>
                            <a href="https://wa.me/<?= esc($merchant['business_whatsapp_number']) ?>" target="_blank" class="flex items-start space-x-3 text-slate-300 hover:text-secondary group">
                                <i class="ri-whatsapp-fill text-xl text-gray-400 group-hover:text-secondary"></i>
                                <div>
                                    <span class="font-semibold text-white">WhatsApp</span>
                                    <p class="text-sm"><?= esc($merchant['business_whatsapp_number']) ?></p>
                                </div>
                            </a>
                            <?php endif; ?>
                             <!-- Address -->
                             <div class="flex items-start space-x-3 text-slate-300">
                                <i class="ri-map-pin-2-fill text-xl text-gray-400"></i>
                                <div>
                                    <span class="font-semibold text-white">Address</span>
                                    <p class="text-sm"><?= esc($merchant['physical_address']) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-600 h-64 rounded-lg flex items-center justify-center text-white">
                        <!-- Map would be initialized here with JavaScript -->
                        <p>Map Placeholder</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>