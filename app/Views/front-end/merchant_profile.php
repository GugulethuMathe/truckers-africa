<main class="container mx-auto px-4 py-12">
<style>
.prose p, .prose strong, .prose li, .prose li strong, .prose ul, .prose ol {
    color: #ffffff !important;
}
</style>
    <!-- Merchant Profile Header -->
    <div class="bg-gray-800 rounded-lg shadow-xl overflow-hidden mb-12">
        <div class="p-6 md:p-10 flex flex-col md:flex-row items-center">
            <!-- Merchant Image -->
            <?php if (!empty($merchant['business_image_url'])) : ?>
                <img src="<?= base_url(esc($merchant['business_image_url'])) ?>" alt="<?= esc($merchant['business_name']) ?> logo" class="h-32 w-32 rounded-full object-cover mb-6 md:mb-0 md:mr-8 ring-4 ring-gray-700">
            <?php else : ?>
                <div class="h-32 w-32 rounded-full bg-gray-700 flex items-center justify-center mb-6 md:mb-0 md:mr-8 ring-4 ring-gray-700">
                    <i class="ri-store-2-line text-6xl text-gray-400"></i>
                </div>
            <?php endif; ?>

            <!-- Merchant Details -->
            <div class="text-center md:text-left">
                <h1 class="text-3xl md:text-4xl font-bold text-white leading-tight"><?= esc($merchant['business_name']) ?></h1>
                <?php if (!empty($merchant['physical_address'])) : ?>
                <p class="text-lg text-gray-400 flex items-center justify-center md:justify-start mt-2">
                    <i class="ri-map-pin-2-line mr-2"></i>
                    <?= esc($merchant['physical_address']) ?>
                </p>
                <?php endif; ?>

                <!-- Contact Information -->
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-4 mt-3">
                    <?php if (!empty($merchant['business_contact_number'])) : ?>
                    <a href="tel:<?= esc($merchant['business_contact_number']) ?>" class="flex items-center text-gray-300 hover:text-white transition-colors">
                        <i class="ri-phone-line mr-2 text-brand-yellow"></i>
                        <span><?= esc($merchant['business_contact_number']) ?></span>
                    </a>
                    <?php endif; ?>

                    <?php if (!empty($merchant['business_whatsapp_number'])) : ?>
                    <a href="https://wa.me/<?= str_replace(['+', ' ', '-'], '', $merchant['business_whatsapp_number']) ?>" target="_blank" class="flex items-center text-gray-300 hover:text-green-400 transition-colors">
                        <i class="ri-whatsapp-line mr-2 text-green-500"></i>
                        <span>WhatsApp</span>
                    </a>
                    <?php endif; ?>

                    <?php if (!empty($merchant['email'])) : ?>
                    <a href="mailto:<?= esc($merchant['email']) ?>" class="flex items-center text-gray-300 hover:text-white transition-colors">
                        <i class="ri-mail-line mr-2 text-brand-yellow"></i>
                        <span><?= esc($merchant['email']) ?></span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Map -->
    <?php if (!empty($merchant['location_lat']) && !empty($merchant['location_lng'])) : ?>
    <div class="bg-gray-800 rounded-lg shadow-xl overflow-hidden mb-12">
        <div id="map" class="w-full h-96" style="background-color: #1f2937;"></div>
    </div>
    <?php endif; ?>

    <!-- About the Merchant -->
    <div class="bg-gray-800 rounded-lg shadow-xl overflow-hidden mb-12 p-6 md:p-10">
        <h2 class="text-2xl font-bold text-white mb-4">About <?= esc($merchant['business_name']) ?></h2>
        <div class="prose prose-invert max-w-none text-gray-300">
            <?php if (!empty($merchant['profile_description'])) : ?>
                <p class="lead text-lg italic text-gray-400 border-l-4 border-brand-yellow pl-4">
                    <?= nl2br(esc($merchant['profile_description'])) ?>
                </p>
            <?php endif; ?>
            <?php if (!empty($merchant['business_description'])) : ?>
                <div class="mt-6">
                    <?= nl2br(esc($merchant['business_description'])) ?>
                </div>
            <?php endif; ?>
            <?php if (empty($merchant['profile_description']) && empty($merchant['business_description'])) : ?>
                <p class="text-gray-500">This merchant has not provided a detailed description yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Merchant's Listings -->
    <h2 class="text-2xl font-bold text-white mb-6">Services Offered by <?= esc($merchant['business_name']) ?></h2>

    <?php if (!empty($listings)): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach ($listings as $listing): ?>
                <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-lg transition-shadow overflow-hidden">
                    <div class="w-full h-40 sm:h-48 bg-gray-100 rounded-md overflow-hidden mb-3">
                        <?php
                            $imagePathRaw = $listing['main_image_path'] ?? '';
                            if (!empty($imagePathRaw) && preg_match('#^https?://#', $imagePathRaw)) {
                                $imageSrc = $imagePathRaw;
                            } else if (!empty($imagePathRaw)) {
                                $imageSrc = get_listing_image_url($imagePathRaw);
                            } else {
                                $imageSrc = '';
                            }
                        ?>
                        <?php if (!empty($imageSrc)): ?>
                            <img src="<?= esc($imageSrc) ?>" alt="<?= esc($listing['title']) ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center">
                                <i class="fas fa-box text-gray-400 text-2xl"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1">
                                    <h4 class="text-lg font-semibold text-gray-900 mb-1"><?= esc($listing['title']) ?></h4>
                                    <p class="text-gray-600 text-sm mb-2 line-clamp-2"><?= esc($listing['description']) ?></p>

                                    <!-- Location Info -->
                                    <?php if (!empty($listing['location_name'])): ?>
                                        <div class="flex items-center gap-2 flex-wrap mb-2">
                                            <span class="text-sm text-gray-700 font-medium"><?= esc($listing['location_name']) ?></span>
                                            <?php if (isset($listing['is_primary']) && $listing['is_primary'] == 1): ?>
                                                <span class="inline-block bg-blue-100 text-blue-800 text-xs px-1.5 py-0.5 rounded-full">Primary</span>
                                            <?php else: ?>
                                                <span class="inline-block text-xs px-1.5 py-0.5 rounded-full" style="background-color: #e6e8eb; color: #0e2140;">BR</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Address -->
                                    <?php
                                        $displayAddress = !empty($listing['location_address']) ? $listing['location_address'] : ($merchant['physical_address'] ?? '');
                                    ?>
                                    <?php if (!empty($displayAddress)): ?>
                                        <p class="text-xs text-gray-500 mb-2">
                                            <i class="fas fa-map-marker-alt mr-1"></i><?= esc($displayAddress) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="flex items-center space-x-3 mt-3">
                                <?php $priceValue = $listing['price'] ?? ''; ?>
                                <?php if ($priceValue !== ''): ?>
                                    <div class="flex items-baseline space-x-2">
                                        <span class="text-sm sm:text-base font-bold text-green-600">
                                            <?= display_listing_price($listing) ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                                <a href="<?= site_url('listing/' . $listing['id']) ?>"
                                   class="px-3 py-1.5 text-white text-sm rounded-md hover:opacity-90 transition-colors whitespace-nowrap"
                                   style="background-color: #0e2140;">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-16 bg-gray-800 rounded-lg">
            <p class="text-gray-400 text-lg">This merchant has no active service listings at the moment.</p>
        </div>
    <?php endif; ?>
</main>

<?php if (!empty($merchant['location_lat']) && !empty($merchant['location_lng']) && !empty($geoapify_api_key)) : ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const apiKey = "<?= esc($geoapify_api_key) ?>";
    const merchantLat = <?= esc($merchant['location_lat']) ?>;
    const merchantLng = <?= esc($merchant['location_lng']) ?>;
    const mapElement = document.getElementById('map');

    if (!mapElement) return;

    const map = L.map('map').setView([merchantLat, merchantLng], 14);

    L.tileLayer(`https://maps.geoapify.com/v1/tile/osm-bright/{z}/{x}/{y}.png?apiKey=${apiKey}`, {
        attribution: 'Powered by <a href="https://www.geoapify.com/" target="_blank">Geoapify</a> | © OpenStreetMap contributors',
        maxZoom: 20,
        id: 'osm-bright'
    }).addTo(map);

    const merchantIcon = L.icon({
        iconUrl: `https://api.geoapify.com/v1/icon/?type=material&color=%23fb923c&icon=store&iconSize=large&apiKey=${apiKey}`,
        iconSize: [38, 56],
        iconAnchor: [19, 55],
        popupAnchor: [0, -56]
    });
    L.marker([merchantLat, merchantLng], {icon: merchantIcon}).addTo(map)
        .bindPopup("<b><?= esc($merchant['business_name']) ?></b>").openPopup();
});
</script>
<?php endif; ?>
