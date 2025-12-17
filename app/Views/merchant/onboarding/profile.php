<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($page_title) ?> - Truckers Africa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/css/intlTelInput.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
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
            <div class="max-w-4xl mx-auto px-4 py-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <img src="<?= site_url('assets/images/logo-icon-black.png') ?>" alt="Truckers Africa" class="h-12 w-auto">
                        <div>
                            <h1 class="text-2xl font-bold brand-color">Welcome to Truckers Africa!</h1>
                            <p class="text-gray-600 mt-1">Let's set up your merchant account</p>
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
            <div class="max-w-4xl mx-auto px-4">
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

                <?php if (session()->has('errors')): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
                        <ul class="list-disc list-inside">
                            <?php foreach (session('errors') as $error): ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Profile Form -->
                <div class="bg-white rounded-lg shadow-md p-8">
                    <div class="mb-6">
                        <h2 class="text-xl font-semibold text-gray-900">Complete Your Business Profile</h2>
                        <p class="text-gray-600 mt-1">Tell us about your business so drivers can find you</p>
                    </div>

                    <form action="<?= site_url('merchant/onboarding/update-profile') ?>" method="post" id="profileForm" enctype="multipart/form-data">
                        <?= csrf_field() ?>

                        <!-- Hidden fields for coordinates -->
                        <input type="hidden" id="latitude" name="latitude" value="<?= esc(old('latitude', $merchant['latitude'] ?? '')) ?>">
                        <input type="hidden" id="longitude" name="longitude" value="<?= esc(old('longitude', $merchant['longitude'] ?? '')) ?>">
                        <input type="hidden" id="business_contact_number" name="business_contact_number" value="<?= esc(old('business_contact_number', $merchant['business_contact_number'] ?? '')) ?>">
                        <input type="hidden" id="business_whatsapp_number" name="business_whatsapp_number" value="<?= esc(old('business_whatsapp_number', $merchant['business_whatsapp_number'] ?? '')) ?>">

                        <div class="space-y-6">
                            <!-- Profile Photo -->
                            <div>
                                <label for="profile_image" class="block text-sm font-medium text-gray-700 mb-2">
                                    Business Profile Photo
                                </label>
                                <div class="flex items-center space-x-6">
                                    <div id="profile_image_preview_container">
                                        <?php if (!empty($merchant['profile_image_url'])): ?>
                                            <img id="profile_image_preview" src="<?= base_url(esc($merchant['profile_image_url'])) ?>" alt="Profile Photo Preview" class="h-24 w-24 rounded-full object-cover border-2 brand-border">
                                        <?php else: ?>
                                            <div id="profile_image_placeholder" class="h-24 w-24 bg-gray-200 rounded-full flex items-center justify-center text-gray-500 border-2 border-gray-300">
                                                <i class="ri-user-line text-4xl"></i>
                                            </div>
                                            <img id="profile_image_preview" src="" alt="Profile Photo Preview" class="h-24 w-24 rounded-full object-cover border-2 brand-border hidden">
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-1">
                                        <input type="file" id="profile_image" name="profile_image" accept="image/*" onchange="previewProfileImage(this)"
                                               class="block w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold brand-bg file:text-white brand-hover"
                                               style="color: #FFFFFF;">
                                        <p class="mt-1 text-xs text-gray-500">Personal photo of the business owner.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Business Image -->
                            <div>
                                <label for="business_image" class="block text-sm font-medium text-gray-700 mb-2">
                                    Business Image
                                </label>
                                <div class="flex items-center space-x-6">
                                    <div id="business_image_preview_container">
                                        <?php if (!empty($merchant['business_image_url'])): ?>
                                            <img id="business_image_preview" src="<?= base_url(esc($merchant['business_image_url'])) ?>" alt="Business Image Preview" class="h-24 w-24 rounded-lg object-cover border-2 brand-border">
                                        <?php else: ?>
                                            <div id="business_image_placeholder" class="h-24 w-24 bg-gray-200 rounded-lg flex items-center justify-center text-gray-500 border-2 border-gray-300">
                                                <i class="ri-image-line text-4xl"></i>
                                            </div>
                                            <img id="business_image_preview" src="" alt="Business Image Preview" class="h-24 w-24 rounded-lg object-cover border-2 brand-border hidden">
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-1">
                                        <input type="file" id="business_image" name="business_image" accept="image/*" onchange="previewBusinessImage(this)"
                                               class="block w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold brand-bg file:text-white brand-hover"
                                               style="color: #FFFFFF;">
                                        <p class="mt-1 text-xs text-gray-500">Logo or photo of your business/storefront.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Business Name -->
                            <div>
                                <label for="business_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Business Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="business_name" name="business_name" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-1 brand-border"
                                       value="<?= esc(old('business_name', $merchant['business_name'] ?? '')) ?>"
                                       placeholder="e.g., ABC Truck Repairs">
                            </div>

                            <!-- Contact Numbers -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="contact_display" class="block text-sm font-medium text-gray-700 mb-2">
                                        Business Contact Number <span class="text-red-500">*</span>
                                    </label>
                                    <input type="tel" id="contact_display" required
                                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-1 brand-border"
                                           value="<?= esc(old('business_contact_number', $merchant['business_contact_number'] ?? '')) ?>">
                                </div>

                                <div>
                                    <label for="whatsapp_display" class="block text-sm font-medium text-gray-700 mb-2">
                                        WhatsApp Number (Optional)
                                    </label>
                                    <input type="tel" id="whatsapp_display"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-1 brand-border"
                                           value="<?= esc(old('business_whatsapp_number', $merchant['business_whatsapp_number'] ?? '')) ?>">
                                </div>
                            </div>

                            <!-- Physical Address -->
                            <div>
                                <label for="physical_address" class="block text-sm font-medium text-gray-700 mb-2">
                                    Physical Address <span class="text-red-500">*</span>
                                </label>
                                <div id="autocomplete-container" class="relative">
                                    <input type="text" id="physical_address" name="physical_address" required
                                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-1 brand-border"
                                           value="<?= esc(old('physical_address', $merchant['physical_address'] ?? '')) ?>"
                                           placeholder="Start typing your address...">
                                </div>
                                <p class="mt-1 text-sm text-gray-500">Start typing to see address suggestions</p>
                            </div>

                            <!-- Main Service -->
                            <div>
                                <label for="main_service" class="block text-sm font-medium text-gray-700 mb-2">
                                    Main Service Category <span class="text-red-500">*</span>
                                </label>
                                <select id="main_service" name="main_service" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-1 brand-border">
                                    <option value="">Select a service category</option>
                                    <?php
                                    $selectedService = old('main_service', $merchant['main_service'] ?? '');
                                    foreach ($service_categories as $category):
                                    ?>
                                        <option value="<?= esc($category['id']) ?>"
                                                <?= $selectedService == $category['id'] ? 'selected' : '' ?>>
                                            <?= esc($category['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Business Description -->
                            <div>
                                <label for="business_description" class="block text-sm font-medium text-gray-700 mb-2">
                                    Business Description <span class="text-red-500">*</span>
                                </label>
                                <textarea id="business_description" name="business_description" required rows="4"
                                          class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-1 brand-border"
                                          placeholder="Describe your business, services, and what makes you unique..."><?= esc(old('business_description', $merchant['business_description'] ?? '')) ?></textarea>
                                <p class="mt-1 text-sm text-gray-500">Minimum 20 characters</p>
                            </div>

                            <!-- Profile Description (Optional) -->
                            <div>
                                <label for="profile_description" class="block text-sm font-medium text-gray-700 mb-2">
                                    Additional Information (Optional)
                                </label>
                                <textarea id="profile_description" name="profile_description" rows="3"
                                          class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-1 brand-border"
                                          placeholder="Any additional information about your business..."><?= esc(old('profile_description', $merchant['profile_description'] ?? '')) ?></textarea>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end pt-6 border-t">
                                <button type="submit"
                                        class="px-8 py-3 brand-bg text-white font-semibold rounded-md brand-hover focus:outline-none focus:ring-2 brand-border focus:ring-offset-2 transition-colors">
                                    Continue to Plan Selection →
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/intlTelInput.min.js"></script>
    <script>
        // Initialize intl-tel-input for phone numbers
        (function() {
            var contactInput = document.getElementById('contact_display');
            var whatsappInput = document.getElementById('whatsapp_display');
            var form = document.getElementById('profileForm');

            function initIti(input) {
                if (!input) return null;
                return window.intlTelInput(input, {
                    initialCountry: 'za',
                    preferredCountries: ['za', 'bw', 'zw', 'mz', 'na'],
                    separateDialCode: true,
                    utilsScript: 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js'
                });
            }

            var contactIti = initIti(contactInput);
            var whatsappIti = initIti(whatsappInput);

            form.addEventListener('submit', function() {
                if (contactIti) {
                    var full = contactIti.getNumber();
                    var digits = (full || '').replace(/\D+/g, '');
                    document.getElementById('business_contact_number').value = digits;
                }
                if (whatsappIti) {
                    var fullW = whatsappIti.getNumber();
                    var digitsW = (fullW || '').replace(/\D+/g, '');
                    document.getElementById('business_whatsapp_number').value = digitsW;
                }
            });
        })();

        // Geoapify address autocomplete
        <?php if (!empty($geoapify_api_key)): ?>
        (function() {
            const apiKey = '<?= $geoapify_api_key ?>';
            const input = document.getElementById('physical_address');
            const container = document.getElementById('autocomplete-container');
            let dropdown = null;

            input.addEventListener('input', debounce(function() {
                const query = input.value;
                if (query.length < 3) {
                    removeDropdown();
                    return;
                }

                fetch(`https://api.geoapify.com/v1/geocode/autocomplete?text=${encodeURIComponent(query)}&apiKey=${apiKey}&limit=5&filter=countrycode:za,bw,zw,mz,na`)
                    .then(response => response.json())
                    .then(data => {
                        removeDropdown();
                        if (data.features && data.features.length > 0) {
                            showDropdown(data.features);
                        }
                    });
            }, 300));

            function showDropdown(features) {
                dropdown = document.createElement('div');
                dropdown.className = 'absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1 max-h-60 overflow-y-auto';
                
                features.forEach(feature => {
                    const item = document.createElement('div');
                    item.className = 'px-4 py-2 hover:bg-gray-100 cursor-pointer';
                    item.textContent = feature.properties.formatted;
                    item.addEventListener('click', function() {
                        input.value = feature.properties.formatted;
                        document.getElementById('latitude').value = feature.properties.lat;
                        document.getElementById('longitude').value = feature.properties.lon;
                        removeDropdown();
                    });
                    dropdown.appendChild(item);
                });
                
                container.appendChild(dropdown);
            }

            function removeDropdown() {
                if (dropdown) {
                    dropdown.remove();
                    dropdown = null;
                }
            }

            function debounce(func, wait) {
                let timeout;
                return function() {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(this, arguments), wait);
                };
            }

            document.addEventListener('click', function(e) {
                if (!container.contains(e.target)) {
                    removeDropdown();
                }
            });
        })();
        <?php endif; ?>

        // Image Preview Functions
        function previewProfileImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                const preview = document.getElementById('profile_image_preview');
                const placeholder = document.getElementById('profile_image_placeholder');

                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    if (placeholder) {
                        placeholder.classList.add('hidden');
                    }
                };

                reader.readAsDataURL(input.files[0]);
            }
        }

        function previewBusinessImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                const preview = document.getElementById('business_image_preview');
                const placeholder = document.getElementById('business_image_placeholder');

                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    if (placeholder) {
                        placeholder.classList.add('hidden');
                    }
                };

                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>

