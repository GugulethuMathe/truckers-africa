<main class="container mx-auto px-4 py-12">
<style>
.prose h2:first-of-type {
    margin-top: 2rem;
    color: white !important;
    border-top: none;
    padding-top: 0;
}
.prose p, .prose strong, .prose li, .prose li strong, .prose ul, .prose ol {
    color: #ffffff !important;
}
</style>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-green-500 text-white text-center p-3 rounded-lg mb-4">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php elseif (session()->getFlashdata('error')) : ?>
        <div class="bg-red-500 text-white text-center p-3 rounded-lg mb-4">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>
    
    <?php
        $galleryImagesForJs = [];
        // Add main image first
        if (!empty($listing['main_image_path'])) {
            $mainImageUrl = get_listing_image_url($listing['main_image_path']);
            $galleryImagesForJs[] = $mainImageUrl;
        }
        // Add gallery images
        if (!empty($gallery_images)) {
            foreach ($gallery_images as $image) {
                if (!empty($image['image_path'])) {
                    $galleryImagesForJs[] = get_listing_image_url($image['image_path']);
                }
            }
        }

        // Ensure we have at least one image
        $firstImage = !empty($galleryImagesForJs) ? $galleryImagesForJs[0] : '';

        // Debug: uncomment to see what images are being generated
        // echo '<pre>Debug Images: ' . print_r($galleryImagesForJs, true) . '</pre>';
    ?>
    
    <div class="bg-gray-800 rounded-lg shadow-xl overflow-hidden" 
         x-data="imageGallery()">
        <!-- Image Gallery -->
        <div>
            <!-- Main Image -->
            <div class="w-full h-64 md:h-96 bg-gray-900 flex items-center justify-center cursor-pointer group"
                 x-show="mainImage"
                 @click="showModal = true; modalImage = mainImage">
                <img :src="mainImage" 
                     alt="<?= esc($listing['title']) ?>" 
                     class="max-w-full max-h-full object-contain transition-transform duration-300 group-hover:scale-105"
                     @error="console.error('Image failed to load:', $event.target.src)">
            </div>

            <!-- No Image Placeholder -->
            <div x-show="!mainImage" 
                 class="w-full h-64 md:h-96 bg-gray-900 flex items-center justify-center">
                <div class="text-center text-gray-500">
                    <i class="ri-image-line text-6xl mb-4"></i>
                    <p>No image available</p>
                </div>
            </div>

            <!-- Thumbnails -->
            <div class="p-4 bg-gray-800/50" x-show="images.length > 1">
                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-4">
                    <template x-for="(image, index) in images" :key="index">
                        <div @click="mainImage = image" 
                             class="cursor-pointer rounded-lg overflow-hidden ring-2 ring-transparent hover:ring-brand-yellow transition-all" 
                             :class="{ '!ring-brand-yellow': mainImage === image }">
                            <img :src="image" 
                                 alt="Gallery Thumbnail" 
                                 class="w-full h-24 object-cover">
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Lightbox Modal -->
        <div x-show="showModal" 
             @keydown.escape.window="showModal = false" 
             @click="showModal = false"
             class="fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50 p-4 transition-opacity duration-300" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0">
            <div @click.stop 
                 class="relative max-w-4xl max-h-full transform transition-transform duration-300" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 scale-95" 
                 x-transition:enter-end="opacity-100 scale-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 scale-100" 
                 x-transition:leave-end="opacity-0 scale-95">
                <img :src="modalImage" 
                     alt="Full size image" 
                     class="max-w-full max-h-full object-contain rounded-lg shadow-2xl">
                <button @click="showModal = false" 
                        class="absolute top-4 right-4 text-white text-4xl font-bold bg-black bg-opacity-50 rounded-full w-12 h-12 flex items-center justify-center hover:bg-opacity-75 transition-all">
                    &times;
                </button>
            </div>
        </div>

        <div class="p-6 md:p-10">
            <!-- Merchant Info -->
            <a href="<?= site_url('merchant/profile/' . $listing['merchant_id']) ?>"
               class="block mb-8 border-b border-gray-700 pb-6 hover:bg-gray-700/50 rounded-lg transition-colors duration-300 p-4 -m-4">
                <div class="flex items-center">
                    <?php if (!empty($listing['business_image_url'])) : ?>
                        <img src="<?= base_url(esc($listing['business_image_url'])) ?>"
                             alt="<?= esc($listing['business_name']) ?> logo"
                             class="h-16 w-16 rounded-full object-cover mr-4 ring-2 ring-gray-600">
                    <?php else : ?>
                        <div class="h-16 w-16 rounded-full bg-gray-700 flex items-center justify-center mr-4 ring-2 ring-gray-600">
                            <i class="ri-store-2-line text-3xl text-gray-400"></i>
                        </div>
                    <?php endif; ?>
                    <div>
                        <p class="text-sm text-gray-400">Offered by</p>
                        <h3 class="text-xl font-bold text-white"><?= esc($listing['business_name']) ?></h3>
                        <?php if (!empty($listing['physical_address'])) : ?>
                            <p class="text-sm text-gray-400 flex items-center mt-1">
                                <i class="ri-map-pin-2-line mr-2"></i>
                                <?= esc($listing['physical_address']) ?>
                            </p>
                        <?php endif; ?>

                        <!-- Contact Information -->
                        <div class="flex flex-wrap items-center gap-3 mt-2">
                            <?php if (!empty($listing['business_contact_number'])) : ?>
                            <span class="flex items-center text-sm text-gray-300" onclick="event.preventDefault(); window.location.href='tel:<?= esc($listing['business_contact_number']) ?>';">
                                <i class="ri-phone-line mr-1 text-brand-yellow"></i>
                                <?= esc($listing['business_contact_number']) ?>
                            </span>
                            <?php endif; ?>

                            <?php if (!empty($listing['business_whatsapp_number'])) : ?>
                            <span class="flex items-center text-sm text-gray-300" onclick="event.preventDefault(); window.open('https://wa.me/<?= str_replace(['+', ' ', '-'], '', $listing['business_whatsapp_number']) ?>', '_blank');">
                                <i class="ri-whatsapp-line mr-1 text-green-500"></i>
                                WhatsApp
                            </span>
                            <?php endif; ?>

                            <?php if (!empty($listing['email'])) : ?>
                            <span class="flex items-center text-sm text-gray-300" onclick="event.preventDefault(); window.location.href='mailto:<?= esc($listing['email']) ?>';">
                                <i class="ri-mail-line mr-1 text-brand-yellow"></i>
                                <?= esc($listing['email']) ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </a>

            <!-- Title -->
            <h1 class="text-3xl md:text-4xl font-bold text-white leading-tight mb-4">
                <?= esc($listing['title']) ?>
            </h1>

            <!-- Price -->
            <p class="text-2xl font-bold text-brand-yellow mb-6">
                <?= display_listing_price($listing) ?>
            </p>

            <!-- Description -->
            <div class="prose prose-invert max-w-none text-gray-300 mb-6">
                <h2 class="text-2xl font-semibold text-white mb-4">Service Description</h2>
                <p><?= nl2br(esc($listing['description'])) ?></p>
            </div>

            <?php if (!empty($listing['location_lat']) && !empty($listing['location_lng'])) : ?>
            <div class="mt-6 pt-6 border-t border-gray-700">
                <h2 class="text-2xl font-semibold text-white mb-4">Location</h2>
                <div id="map" class="w-full h-96 rounded-lg shadow-xl" style="background-color: #1f2937;"></div>
            </div>
            <?php endif; ?>

            <?php if (!empty($listing['business_description'])): ?>
            <div class="prose prose-invert max-w-none text-gray-300 pt-6 mt-6 border-t border-gray-700">
                <h2 class="text-2xl font-semibold text-white mb-4">About <?= esc($listing['business_name']) ?></h2>
                <p><?= nl2br(esc($listing['business_description'])) ?></p>
            </div>
            <?php endif; ?>

            <!-- Action Button -->
            <div class="mt-8 text-center">
                <?php if (session()->get('isLoggedIn') && session()->get('user_type') === 'driver'): ?>
                    <form action="<?= site_url('order/place') ?>" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="listing_id" value="<?= esc($listing['id']) ?>">
                        <button type="submit" 
                                class="bg-brand-yellow text-gray-900 font-bold py-3 px-8 rounded-full hover:bg-yellow-400 transition-colors duration-300 text-lg">
                            Place Order
                        </button>
                    </form>
                <?php else: ?>
                    <a href="<?= site_url('login') ?>"
                       class="font-bold py-3 px-8 rounded-full hover:opacity-90 transition-colors duration-300 text-lg inline-block text-white"
                       style="background-color: #0e2140;">
                        Login as a Driver to Order
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Define the image data in a safer way
        window.galleryImages = <?= json_encode($galleryImagesForJs, JSON_UNESCAPED_SLASHES) ?>;
        
        function imageGallery() {
            return {
                images: window.galleryImages || [],
                mainImage: window.galleryImages && window.galleryImages.length > 0 ? window.galleryImages[0] : '',
                modalImage: '',
                showModal: false,
                
                init() {
                    console.log('Gallery initialized with images:', this.images);
                    console.log('Main image set to:', this.mainImage);
                }
            }
        }
    </script>

    <?php if (!empty($listing['location_lat']) && !empty($listing['location_lng']) && !empty($geoapify_api_key)) : ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const apiKey = "<?= esc($geoapify_api_key) ?>";
        const merchantLat = <?= esc($listing['location_lat']) ?>;
        const merchantLng = <?= esc($listing['location_lng']) ?>;
        const mapElement = document.getElementById('map');

        if (!mapElement) return;

        const map = L.map('map').setView([merchantLat, merchantLng], 14);

        L.tileLayer(`https://maps.geoapify.com/v1/tile/osm-bright/{z}/{x}/{y}.png?apiKey=${apiKey}`, {
            attribution: 'Powered by <a href="https://www.geoapify.com/" target="_blank">Geoapify</a> | &copy; OpenStreetMap contributors',
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
            .bindPopup("<b><?= esc($listing['business_name']) ?></b>").openPopup();
    });
    </script>
    <?php endif; ?>
</main>
