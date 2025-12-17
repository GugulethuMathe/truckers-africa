<style>
    * {
        font-family: 'Roboto Condensed', sans-serif;
    }
    
    .hero-heading {
        font-size: 2.8rem !important;
        font-family: 'Roboto Condensed', sans-serif;
    }
    
    @media (min-width: 768px) {
        .hero-heading {
            font-size: 3.67em !important;
            font-family: 'Roboto Condensed', sans-serif;
        }
        
        .hero-description {
            font-size: 1.125rem !important;
            text-align: center !important;
            line-height: 1.75rem !important;
        }
    }
    
    /* Page headings styling */
    main h2 {
        font-family: 'Roboto Condensed', sans-serif !important;
        font-size: 3rem !important;
    }
    
</style>

<main>
    <!-- =================================== -->
    <!-- HERO SECTION                        -->
    <!-- =================================== -->
    <section class="min-h-screen flex items-center pt-20 relative overflow-hidden">
        <div class="absolute inset-0">
            <img src="<?= base_url('assets/images/truck.jpg') ?>" alt="Truck on an open road" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-black/60"></div>
        </div>
        <div class="particles-container absolute inset-0"></div>

        <div class="container mx-auto px-4 relative max-w-5xl text-center z-10">
            <h1 class="hero-heading font-light text-white leading-tight tracking-wide">
                Get discovered by truckers 
<br>
                <span class="block mt-2">Across Africa</span>
            </h1>
            <p class="hero-description mt-8 text-base md:text-lg text-white/95 max-w-3xl mx-auto leading-relaxed font-light">
              List your services and connect with truckers instantly.
  Discover trusted service providers, truck stops, fuel suppliers, and roadside
                assistance across Africa. 
            </p>

            <div class="mt-10 flex flex-col sm:flex-row justify-center items-center gap-4">
                <a href="<?= site_url('signup/driver') ?>" class="w-full sm:w-auto bg-transparent border-2 border-white text-white px-8 py-3 rounded-md text-base font-semibold hover:bg-white hover:text-[#1e5a7a] transition-all duration-300">
                    Find Service Providers
                </a>
                <a href="<?= site_url('signup/merchant') ?>" class="w-full sm:w-auto bg-[#f5a623] text-gray-900 px-8 py-3 rounded-md text-base font-semibold hover:bg-[#e09612] transition-all duration-300">
                    List Your Business
                </a>
            </div>
        </div>
    </section>

    <!-- Container for the rest of the page content -->
    <div class="bg-white">

        <!-- =================================== -->
        <!-- WHAT IS TRUCKERS AFRICA SECTION   -->
        <!-- =================================== -->
        <section class="py-20">
            <div class="container mx-auto px-4">
                <div class="text-center mb-12">
                    <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">WHAT IS TRUCKERS AFRICA?</h2>
                    <p class="text-lg text-gray-700 max-w-3xl mx-auto">
                        Truckers Africa is an online business and mobile app built for the African trucking industry. We connect truck drivers, logistics companies, and service providers – making trucking simpler, faster, and more efficient.
                    </p>
                </div>

                <!-- Four Icons -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mt-16">
                    <div class="text-center">
                        <div class="flex justify-center mb-4">
                            <i class="ri-store-2-line text-6xl text-gray-900"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Service Listings</h3>
                    </div>
                    <div class="text-center">
                        <div class="flex justify-center mb-4">
                            <i class="ri-map-pin-line text-6xl text-gray-900"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Route Planner</h3>
                    </div>
                    <div class="text-center">
                        <div class="flex justify-center mb-4">
                            <i class="ri-truck-line text-6xl text-gray-900"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Fleet Management</h3>
                    </div>
                    <div class="text-center">
                        <div class="flex justify-center mb-4">
                            <i class="ri-global-line text-6xl text-gray-900"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Load Portal</h3>
                    </div>
                </div>
            </div>
        </section>

        <!-- =================================== -->
        <!-- NEAREST MERCHANTS SECTION          -->
        <!-- =================================== -->
        <?php if (!empty($business_locations)): ?>
        <section class="py-16 bg-gray-50">
            <div class="container mx-auto px-4">
                <div class="text-center mb-10">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-3">NEAREST MERCHANTS</h2>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                        Discover trusted service providers ready to assist you on your journey
                    </p>
                </div>

                <!-- Merchants Grid -->
                <div id="merchants-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php foreach ($business_locations as $location): ?>
                        <div class="merchant-card bg-white border border-gray-200 rounded-xl p-5 hover:shadow-xl transition-shadow duration-300"
                             data-lat="<?= esc($location['latitude'] ?? '') ?>"
                             data-lng="<?= esc($location['longitude'] ?? '') ?>">
                            <div class="flex items-start mb-4">
                                <img src="<?= !empty($location['business_image_url']) ? base_url($location['business_image_url']) : 'https://via.placeholder.com/48x48/e5e7eb/6b7280?text=' . urlencode(substr($location['business_name'], 0, 2)) ?>"
                                     alt="<?= esc($location['business_name']) ?> Logo"
                                     class="w-12 h-12 rounded-full mr-3 object-cover flex-shrink-0 border-2 border-gray-100">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2 mb-1">
                                        <h4 class="font-bold text-gray-900 text-sm truncate flex-1" title="<?= esc($location['location_name']) ?>">
                                            <?= esc($location['location_name']) ?>
                                        </h4>
                                        <span class="distance-badge text-xs text-gray-500 flex-shrink-0 whitespace-nowrap hidden">
                                            <i class="fas fa-location-arrow text-xs mr-0.5"></i><span class="distance-value"></span> km
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-600 truncate" title="<?= esc($location['business_name']) ?>">
                                        <?= esc($location['business_name']) ?>
                                    </p>
                                    <div class="flex items-center gap-1 mt-1">
                                        <?php if ($location['is_primary'] == 1): ?>
                                            <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-0.5 rounded-full">Primary</span>
                                        <?php else: ?>
                                            <span class="inline-block bg-gray-100 text-gray-700 text-xs px-2 py-0.5 rounded-full">Branch</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2 text-sm text-gray-600 mb-4">
                                <?php if (!empty($location['physical_address'])): ?>
                                    <?php
                                        $addressWords = explode(' ', $location['physical_address']);
                                        $shortAddress = implode(' ', array_slice($addressWords, 0, 5));
                                        if (count($addressWords) > 5) {
                                            $shortAddress .= '...';
                                        }
                                    ?>
                                    <div class="flex items-start">
                                        <i class="fas fa-map-marker-alt text-gray-400 mr-2 mt-0.5 flex-shrink-0"></i>
                                        <span class="text-xs truncate" title="<?= esc($location['physical_address']) ?>"><?= esc($shortAddress) ?></span>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($location['contact_number'])): ?>
                                    <div class="flex items-center">
                                        <i class="fas fa-phone text-gray-400 mr-2 flex-shrink-0"></i>
                                        <a href="tel:<?= esc($location['contact_number']) ?>" class="text-blue-600 hover:text-blue-800 text-xs">
                                            <?= esc($location['contact_number']) ?>
                                        </a>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($location['whatsapp_number'])): ?>
                                    <div class="flex items-center">
                                        <i class="fab fa-whatsapp text-green-500 mr-2 flex-shrink-0"></i>
                                        <a href="https://wa.me/<?= str_replace(['+', ' ', '-'], '', $location['whatsapp_number']) ?>"
                                           target="_blank"
                                           class="text-green-600 hover:text-green-800 text-xs">
                                            WhatsApp
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="border-t border-gray-100 pt-3">
                                <a href="<?= base_url('merchant/profile/' . esc($location['merchant_id'], 'url')) ?>"
                                   class="w-full flex items-center justify-center text-sm text-white px-4 py-2 rounded-lg transition-colors hover:opacity-90"
                                   style="background-color: #0e2140;">
                                    <i class="fas fa-store mr-2"></i>View Services
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- View All Button -->
                <div class="text-center mt-10">
                    <a href="<?= site_url('signup/driver') ?>"
                       class="inline-flex items-center px-6 py-3 text-white rounded-lg font-semibold hover:opacity-90 transition-colors"
                       style="background-color: #0e2140;">
                        <i class="fas fa-search mr-2"></i>Find More Merchants
                    </a>
                </div>
            </div>
        </section>

        <!-- Distance Calculation Script -->
        <script>
        (function() {
            // Haversine formula to calculate distance between two points
            function calculateDistance(lat1, lon1, lat2, lon2) {
                const R = 6371; // Earth's radius in km
                const dLat = (lat2 - lat1) * Math.PI / 180;
                const dLon = (lon2 - lon1) * Math.PI / 180;
                const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                          Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                          Math.sin(dLon/2) * Math.sin(dLon/2);
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                return R * c;
            }

            // Update distances and sort cards
            function updateDistances(userLat, userLng) {
                const grid = document.getElementById('merchants-grid');
                if (!grid) return;

                const cards = Array.from(grid.querySelectorAll('.merchant-card'));

                cards.forEach(card => {
                    const lat = parseFloat(card.dataset.lat);
                    const lng = parseFloat(card.dataset.lng);

                    if (!isNaN(lat) && !isNaN(lng)) {
                        const distance = calculateDistance(userLat, userLng, lat, lng);
                        card.dataset.distance = distance;

                        const badge = card.querySelector('.distance-badge');
                        const value = card.querySelector('.distance-value');
                        if (badge && value) {
                            value.textContent = distance.toFixed(1);
                            badge.classList.remove('hidden');
                        }
                    } else {
                        card.dataset.distance = 999999;
                    }
                });

                // Sort cards by distance
                cards.sort((a, b) => parseFloat(a.dataset.distance) - parseFloat(b.dataset.distance));
                cards.forEach(card => grid.appendChild(card));
            }

            // Request user location
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        updateDistances(position.coords.latitude, position.coords.longitude);
                    },
                    function(error) {
                        console.log('Geolocation not available:', error.message);
                    },
                    { timeout: 10000, maximumAge: 300000 }
                );
            }
        })();
        </script>
        <?php endif; ?>

        <!-- =================================== -->
        <!-- EVERYTHING YOU NEED SECTION       -->
        <!-- =================================== -->
        <section class="py-20" style="background-color: #111827;">
            <div class="container mx-auto px-4">
                <div class="text-center mb-12">
                    <h2 class="text-4xl md:text-5xl font-bold text-white mb-4">EVERYTHING YOU NEED ON THE ROAD</h2>
                    <p class="text-lg text-gray-300 max-w-3xl mx-auto">
                        Stop wasting time and money. Get instant access to verified services, profitable loads,<br class="hidden md:block">
                        and truck-safe routes.
                    </p>
                </div>

                <!-- Three Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-16">
                    <div class="p-6" style="background-color: #18212f; border-radius: 0.5rem;">
                        <div class="flex items-center gap-3 mb-4">
                            <i class="ri-route-line text-4xl text-blue-500"></i>
                            <h3 class="text-lg font-bold text-white uppercase">Smart Route Planning</h3>
                        </div>
                        <p class="text-gray-400 text-sm leading-relaxed">
                            Avoid costly detours and delays. Our AI-powered routing saves you fuel, time, and prevents expensive bridge strikes or weight violations.
                        </p>
                    </div>
                    <div class="p-6" style="background-color: #18212f; border-radius: 0.5rem;">
                        <div class="flex items-center gap-3 mb-4">
                            <i class="ri-tools-line text-4xl text-blue-500"></i>
                            <h3 class="text-lg font-bold text-white uppercase">Trusted Network</h3>
                        </div>
                        <p class="text-gray-400 text-sm leading-relaxed">
                            No more roadside scams or overcharging. Every mechanic, fuel station, and service provider is verified and rated by real drivers.
                        </p>
                    </div>
                    <div class="p-6" style="background-color: #18212f; border-radius: 0.5rem;">
                        <div class="flex items-center gap-3 mb-4">
                            <i class="ri-gas-station-line text-4xl text-blue-500"></i>
                            <h3 class="text-lg font-bold text-white uppercase">Real-Time Intel</h3>
                        </div>
                        <p class="text-gray-400 text-sm leading-relaxed">
                            Live fuel prices, parking availability, and load opportunities. Make informed decisions that boost your profit margins every trip.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- =================================== -->
        <!-- MOBILE APP SECTION                -->
        <!-- =================================== -->
        <section class="py-20 bg-white">
            <div class="container mx-auto px-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-16 items-center">
                    <div class="order-2 md:order-1">
                        <div class="relative">
                            <div class="absolute inset-0 bg-gradient-to-br from-blue-500/20 to-purple-500/20 rounded-3xl blur-3xl"></div>
                            <div class="relative w-full max-w-md mx-auto rounded-3xl overflow-hidden shadow-2xl">
                                <img src="<?= base_url('assets/images/ttruckidriver.jpg') ?>" alt="Trucker using mobile app" class="w-full object-cover" style="height: 490px;">
                            </div>
                        </div>
                    </div>
                    <div class="order-1 md:order-2">
                        <div class="inline-block bg-blue-100 text-blue-600 px-4 py-2 rounded-full text-sm font-semibold mb-4">
                            <i class="ri-smartphone-line mr-1"></i> Mobile App
                        </div>
                        <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6 leading-tight">
                            ON THE ROAD?<br>
                            <span class="text-blue-600">STAY CONNECTED</span><br>
                            WITH OUR APP
                        </h2>
                        <p class="text-lg text-gray-600 mb-8 leading-relaxed">
                            Search, book, and manage services from your phone – available on Android and iOS. Access real-time updates, route planning, and verified service providers wherever you are.
                        </p>
                        <div class="flex flex-col sm:flex-row gap-4">
                            <a href="https://play.google.com/store/apps/details?id=com.truckersafrica.app" target="_blank" class="inline-block hover:opacity-80 transition-all hover:scale-105">
                                <img src="<?= base_url('assets/images/google-play.png') ?>" alt="Get it on Google Play" class="h-14 w-auto">
                            </a>
                            <a href="#" class="inline-block hover:opacity-80 transition-all hover:scale-105">
                                <img src="<?= base_url('assets/images/app-store.png') ?>" alt="Download on the App Store" class="h-14 w-auto">
                            </a>
                        </div>
                        <div class="mt-8 flex items-center gap-6 text-sm text-gray-600">
                            <div class="flex items-center gap-2">
                                <i class="ri-star-fill text-yellow-400"></i>
                                <span class="font-semibold">4.8 Rating</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="ri-download-line text-blue-600"></i>
                                <span class="font-semibold">10K+ Downloads</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- =================================== -->
        <!-- BUSINESS CTA SECTION              -->
        <!-- =================================== -->
        <section class="py-16" style="background-color: #111827;">
            <div class="container mx-auto px-4">
                <div class="flex flex-col md:flex-row items-center justify-between gap-8 p-8 md:p-12 rounded-2xl" style="background-color: #3b82f6;">
                    <div class="flex-1">
                        <h2 class="text-3xl md:text-4xl font-bold text-white mb-3">READY TO GROW YOUR BUSINESS?</h2>
                        <p class="text-base text-white/90">
                            Thousands of drivers search our platform daily. Get instant visibility, verified reviews,<br class="hidden lg:block">
                            and direct bookings from truckers who need your services.
                        </p>
                    </div>
                    <a href="<?= site_url('signup/merchant') ?>" class="flex-shrink-0 bg-[#f97316] text-white px-8 py-4 rounded-lg font-bold hover:bg-[#ea580c] transition-colors whitespace-nowrap uppercase text-sm">
                        Boost your Business - Start Today
                    </a>
                </div>
            </div>
        </section>

    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Home page search functionality
    const categoryToggle = document.getElementById('homeCategoryToggle');
    const categoryFilters = document.getElementById('homeCategoryFilters');
    const categoryInput = document.getElementById('homeCategoryInput');
    const searchForm = document.getElementById('homeSearchForm');

    // Toggle category filters
    if (categoryToggle && categoryFilters) {
        categoryToggle.addEventListener('click', function() {
            categoryFilters.classList.toggle('hidden');
            const icon = this.querySelector('i');
            if (categoryFilters.classList.contains('hidden')) {
                icon.className = 'ri-filter-line text-lg';
            } else {
                icon.className = 'ri-filter-fill text-lg';
            }
        });
    }

    // Handle category filter clicks
    const categoryButtons = document.querySelectorAll('.home-category-filter');
    categoryButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            categoryButtons.forEach(btn => {
                btn.classList.remove('active', 'bg-primary');
                btn.classList.add('bg-gray-600', 'hover:bg-gray-500');
            });

            // Add active class to clicked button
            this.classList.add('active', 'bg-primary');
            this.classList.remove('bg-gray-600', 'hover:bg-gray-500');

            // Update hidden input
            if (categoryInput) {
                categoryInput.value = this.dataset.category;
            }
        });
    });

    // Handle form submission
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const activeCategory = document.querySelector('.home-category-filter.active');
            if (activeCategory && categoryInput) {
                categoryInput.value = activeCategory.dataset.category;
            }
        });
    }
});
</script>