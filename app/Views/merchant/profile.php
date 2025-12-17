<?= view('merchant/templates/header', ['page_title' => $page_title]) ?>

<div class="px-4 lg:px-6 py-6 lg:py-8">
    <div class="max-w-4xl mx-auto">

        <?= view('merchant/components/notifications') ?>

        <div class="mb-6">
            <h1 class="text-xl lg:text-3xl font-bold text-gray-900"><?= esc($page_title) ?></h1>
            <p class="text-gray-600 text-xs lg:text-base">Keep your business information up to date.</p>
        </div>

        <?php if (session()->has('errors')) : ?>
            <div class="rounded-md bg-red-50 p-4 mb-6 border border-red-300">
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Please correct the following errors:</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-disc pl-5 space-y-1">
                            <?php foreach (session('errors') as $error) : ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="bg-white p-4 lg:p-6 rounded-lg shadow-md">
            <form action="<?= site_url('profile/merchant/update') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <!-- Hidden fields for coordinates -->
                <input type="hidden" id="latitude" name="latitude" value="<?= esc($merchant['latitude'] ?? '') ?>">
                <input type="hidden" id="longitude" name="longitude" value="<?= esc($merchant['longitude'] ?? '') ?>">

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">
                    <div class="lg:col-span-2">
                        <label for="owner_name" class="block text-sm font-medium text-black">Owner Name</label>
                        <input type="text" id="owner_name" name="owner_name" value="<?= esc($merchant['owner_name'] ?? '') ?>" class="mt-1 block w-full rounded-md border-2 border-gray-400 px-3 py-3 lg:py-2 shadow-sm focus:border-brand-blue focus:ring-brand-blue text-base lg:text-sm" required>
                    </div>

                    <div class="lg:col-span-2">
                        <label for="email" class="block text-sm font-medium text-black">Email Address</label>
                        <input type="email" id="email" name="email" value="<?= esc($merchant['email'] ?? '') ?>" class="mt-1 block w-full rounded-md border-2 border-gray-400 px-3 py-3 lg:py-2 shadow-sm focus:border-brand-blue focus:ring-brand-blue text-base lg:text-sm" required>
                        <p class="mt-1 text-xs text-gray-500">This email will be used for notifications and account communications.</p>
                    </div>

                    <div class="lg:col-span-2">
                        <label for="business_name" class="block text-sm font-medium text-black">Business Name</label>
                        <input type="text" id="business_name" name="business_name" value="<?= esc($merchant['business_name'] ?? '') ?>" class="mt-1 block w-full rounded-md border-2 border-gray-400 px-3 py-3 lg:py-2 shadow-sm focus:border-brand-blue focus:ring-brand-blue text-base lg:text-sm" required>
                    </div>

                    <div>
                        <label for="business_contact_input" class="block text-sm font-medium text-gray-700">Business Contact Number</label>
                        <input type="tel" id="business_contact_input" name="business_contact_number_visual" class="mt-1 block w-full rounded-md border-2 border-gray-400 px-3 py-2 shadow-sm focus:border-brand-blue focus:ring-brand-blue sm:text-sm" required data-initial="<?= esc($merchant['business_contact_number'] ?? '') ?>">
                        <input type="hidden" id="business_contact_number" name="business_contact_number" value="<?= esc($merchant['business_contact_number'] ?? '') ?>">
                    </div>

                    <div>
                        <label for="business_whatsapp_input" class="block text-sm font-medium text-gray-700">Business WhatsApp Number <span class="text-gray-500">(Optional)</span></label>
                        <input type="tel" id="business_whatsapp_input" name="business_whatsapp_number_visual" class="mt-1 block w-full rounded-md border-2 border-gray-400 px-3 py-2 shadow-sm focus:border-brand-blue focus:ring-brand-blue sm:text-sm" data-initial="<?= esc($merchant['business_whatsapp_number'] ?? ($merchant['business_contact_number'] ?? '')) ?>">
                        <input type="hidden" id="business_whatsapp_number" name="business_whatsapp_number" value="<?= esc($merchant['business_whatsapp_number'] ?? ($merchant['business_contact_number'] ?? '')) ?>">
                    </div>

                    <div class="md:col-span-2">
                        <label for="physical_address" class="block text-sm font-medium text-gray-700">Physical Address <span class="text-red-500">*</span></label>
                        <div id="autocomplete-container"></div>
                        <!-- Hidden field to store the selected address -->
                        <textarea name="physical_address" id="physical_address" class="hidden"><?= esc($merchant['physical_address'] ?? '') ?></textarea>
                        <p class="mt-1 text-xs text-gray-500">Start typing your address and select from suggestions. This ensures GPS coordinates are captured correctly.</p>
                        <div id="coordinates-status" class="mt-2 text-xs hidden">
                            <span class="inline-flex items-center px-2 py-1 rounded-full">
                                <i class="fas fa-map-marker-alt mr-1"></i>
                                <span id="coordinates-text"></span>
                            </span>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label for="main_service" class="block text-sm font-medium text-gray-700">Main Service Category</label>
                        <select name="main_service" id="main_service" class="mt-1 block w-full rounded-md border-2 border-gray-400 px-3 py-2 shadow-sm focus:border-brand-blue focus:ring-brand-blue sm:text-sm">
                            <option value="">Select a main service category (optional)</option>
                            <?php foreach ($service_categories as $category): ?>
                                <option value="<?= esc($category['name']) ?>" <?= old('main_service', $merchant['main_service'] ?? '') === $category['name'] ? 'selected' : '' ?>>
                                    <?= esc($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Choose the primary service category that best describes your business.</p>
                    </div>

                    <div>
                        <label for="profile_image" class="block text-sm font-medium text-gray-700">Profile Photo</label>
                        <div class="mt-2 flex items-center space-x-6">
                            <div id="profile_image_preview_container">
                                <?php if (!empty($merchant['profile_image_url'])): ?>
                                    <img id="profile_image_preview" src="<?= base_url( esc($merchant['profile_image_url'])) ?>" alt="Current Profile Photo" class="h-24 w-24 rounded-full object-cover border-2 border-gray-300">
                                <?php else: ?>
                                    <div id="profile_image_placeholder" class="h-24 w-24 bg-gray-200 rounded-full flex items-center justify-center text-gray-500 border-2 border-gray-300">
                                        <i class="ri-user-line text-4xl"></i>
                                    </div>
                                    <img id="profile_image_preview" src="" alt="Profile Photo Preview" class="h-24 w-24 rounded-full object-cover border-2 border-gray-300 hidden">
                                <?php endif; ?>
                            </div>
                            <div class="flex-1">
                                <input type="file" id="profile_image" name="profile_image" accept="image/*" onchange="previewProfileImage(this)" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-brand-blue file:text-white hover:file:bg-blue-700">
                                <p class="mt-1 text-xs text-gray-500">Personal photo of the business owner.</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="business_image" class="block text-sm font-medium text-gray-700">Business Image</label>
                        <div class="mt-2 flex items-center space-x-6">
                            <div id="business_image_preview_container">
                                <?php if (!empty($merchant['business_image_url'])): ?>
                                    <img id="business_image_preview" src="<?= base_url( esc($merchant['business_image_url'])) ?>" alt="Current Business Image" class="h-24 w-24 rounded-lg object-cover border-2 border-gray-300">
                                <?php else: ?>
                                    <div id="business_image_placeholder" class="h-24 w-24 bg-gray-200 rounded-lg flex items-center justify-center text-gray-500 border-2 border-gray-300">
                                        <i class="ri-image-line text-4xl"></i>
                                    </div>
                                    <img id="business_image_preview" src="" alt="Business Image Preview" class="h-24 w-24 rounded-lg object-cover border-2 border-gray-300 hidden">
                                <?php endif; ?>
                            </div>
                            <div class="flex-1">
                                <input type="file" id="business_image" name="business_image" accept="image/*" onchange="previewBusinessImage(this)" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-brand-blue file:text-white hover:file:bg-blue-700">
                                <p class="mt-1 text-xs text-gray-500">Logo or photo of your business/storefront.</p>
                            </div>
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <label for="profile_description" class="block text-sm font-medium text-gray-700">Profile Summary</label>
                        <textarea id="profile_description" name="profile_description" rows="3" class="mt-1 block w-full rounded-md border-2 border-gray-400 px-3 py-2 shadow-sm focus:border-brand-blue focus:ring-brand-blue sm:text-sm"><?= esc($merchant['profile_description'] ?? '') ?></textarea>
                        <p class="mt-1 text-xs text-gray-500">A short, catchy summary for your business that appears in search results.</p>
                    </div>

                    <div class="md:col-span-2">
                        <label for="business_description" class="block text-sm font-medium text-gray-700">Full Business Description</label>
                        <textarea id="business_description" name="business_description" rows="6" class="mt-1 block w-full rounded-md border-2 border-gray-400 px-3 py-2 shadow-sm focus:border-brand-blue focus:ring-brand-blue sm:text-sm"><?= esc($merchant['business_description'] ?? '') ?></textarea>
                        <p class="mt-1 text-xs text-gray-500">A detailed description of your business, services, and history. This will be shown on your main profile page.</p>
                    </div>

                    <!-- Visibility Control - Only show for approved merchants -->
                    <?php if (isset($merchant['verification_status']) && $merchant['verification_status'] === 'approved'): ?>
                        <div class="md:col-span-2 bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" id="is_visible" name="is_visible" value="1"
                                           class="h-4 w-4 text-brand-blue focus:ring-brand-blue border-gray-300 rounded"
                                           <?= isset($merchant['is_visible']) && $merchant['is_visible'] ? 'checked' : '' ?>>
                                </div>
                                <div class="ml-3">
                                    <label for="is_visible" class="font-medium text-gray-900">
                                        Make my business visible to drivers
                                    </label>
                                    <p class="text-sm text-gray-600 mt-1">
                                        When enabled, your business will appear in driver searches and on the map. Disable this if you want to temporarily hide your business from drivers.
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mt-6 flex justify-end space-x-4">
                    <a href="<?= site_url('merchant/dashboard') ?>" class="bg-gray-200 text-gray-800 font-semibold py-2 px-4 rounded-lg text-sm hover:bg-gray-300">Cancel</a>
                    <button type="submit" class="bg-brand-blue text-white font-semibold py-2 px-4 rounded-lg text-sm hover:bg-blue-700">Update Profile</button>
                </div>
            </form>
        </div>

    </div>
</div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/css/intlTelInput.css" />
<style>
    .iti--separate-dial-code .iti__selected-dial-code { margin-left: 6px; color: black; }
    span.iti__country-name { color: #0a0a0a; }
    select#country_code { color: black !important; }
    /* Ensure the input matches our Tailwind field height */
    .iti input { height: 42px; }
    .iti { width: 100%; }
    .iti__country-list { z-index: 10000; }
    #business_contact_input, #business_whatsapp_input { padding-left: 52px !important; }
    .iti--separate-dial-code #business_contact_input,
    .iti--separate-dial-code #business_whatsapp_input { padding-left: 88px !important; }
    .iti__selected-flag { border-right: 1px solid #e5e7eb; }
    .iti__flag-container { border-color: #e5e7eb; }
    .iti__selected-flag:hover { background-color: #f9fafb; }
    .iti--separate-dial-code .iti__selected-flag { background-color: #f9fafb; }
    .iti__country-list .iti__country { color: #111827; }
    .iti__dial-code { color: #111827; }
    .iti--allow-dropdown input { border-color: #9ca3af; }
    .iti--allow-dropdown input:focus { border-color: #2563eb; }
    .iti__country-list { box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -4px rgba(0,0,0,0.1); }
    .iti--separate-dial-code .iti__selected-flag { border-right: 1px solid #e5e7eb; }
    .iti__country.iti__highlight { background-color: #eef2ff; }
    .iti__country:hover { background-color: #f3f4f6; }
    .iti__search-input { border-color: #e5e7eb; }
    .iti__search-input:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2); }
    .iti__country-list::-webkit-scrollbar { width: 8px; }
    .iti__country-list::-webkit-scrollbar-thumb { background-color: #d1d5db; border-radius: 9999px; }
    .iti__country-list::-webkit-scrollbar-thumb:hover { background-color: #9ca3af; }
    /* End of styling tweaks */
    
</style>
<!-- Geoapify Autocomplete CSS and JS -->
<link rel="stylesheet" type="text/css" href="https://unpkg.com/@geoapify/geocoder-autocomplete@latest/styles/minimal.css">
<style>
/* Style the autocomplete to match other form inputs */
#autocomplete-container input,
.geoapify-autocomplete-input {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 2px solid #9ca3af;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    line-height: 1.25rem;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

#autocomplete-container input:focus,
.geoapify-autocomplete-input:focus {
    outline: none;
    border-color: #0e2140;
    ring: 2px;
    ring-color: rgba(14, 33, 64, 0.5);
}

.geoapify-autocomplete-items {
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    margin-top: 0.25rem;
    background: white;
    z-index: 9999;
}
</style>
<script src="https://unpkg.com/@geoapify/geocoder-autocomplete@latest/dist/index.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/intlTelInput.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js"></script>
<script>
    (function() {
        var contactInput = document.getElementById('business_contact_input');
        var whatsappInput = document.getElementById('business_whatsapp_input');
        var form = document.querySelector('form[action$="profile/merchant/update"]');
        if (!form || !window.intlTelInput) return;

        function initIti(el) {
            if (!el) return null;
            var iti = window.intlTelInput(el, {
                initialCountry: 'za',
                separateDialCode: true,
                onlyCountries: ['za','bw','sz','ls','na','mw','mz','cd','ao','tz','ke','ug','ng','zm','zw'],
                utilsScript: 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js'
            });
            // Pre-fill from saved digits if available
            var digits = (el.getAttribute('data-initial') || '').replace(/\D+/g, '');
            if (digits) {
                iti.setNumber('+' + digits);
            }
            return iti;
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

    // Geoapify Address Autocomplete
    const GEOAPIFY_API_KEY = '<?= esc($geoapify_api_key ?? '') ?>';
    let addressSelectedFromDropdown = <?= (!empty($merchant['latitude']) && !empty($merchant['longitude'])) ? 'true' : 'false' ?>; // Pre-set to true if merchant already has coordinates

    console.log('Geoapify API Key:', GEOAPIFY_API_KEY ? 'Available' : 'Missing');
    console.log('Initial addressSelectedFromDropdown:', addressSelectedFromDropdown);

    if (GEOAPIFY_API_KEY && typeof autocomplete !== 'undefined') {
        try {
            const addressAutocomplete = new autocomplete.GeocoderAutocomplete(
                document.getElementById('autocomplete-container'),
                GEOAPIFY_API_KEY,
                {
                    placeholder: 'Start typing your business address...',
                    lang: 'en',
                    limit: 10,
                    debounceDelay: 300,
                    skipIcons: false,
                    skipDetails: false,
                    addDetails: true,
                    filter: {
                        type: ['amenity', 'street', 'postcode', 'city', 'county']
                    }
                }
            );

            console.log('Autocomplete initialized:', !!addressAutocomplete);

        // Pre-fill the autocomplete with existing address if available
        <?php if (!empty($merchant['physical_address'])): ?>
        setTimeout(function() {
            const autocompleteInput = document.querySelector('#autocomplete-container input');
            console.log('Autocomplete input found:', !!autocompleteInput);
            if (autocompleteInput) {
                autocompleteInput.value = '<?= esc(str_replace("'", "\\'", $merchant['physical_address'])) ?>';
                console.log('Pre-filled address:', autocompleteInput.value);
            }
        }, 500);
        <?php endif; ?>

        // When user selects an address from suggestions
        addressAutocomplete.on('select', function(location) {
            if (location && location.properties) {
                const props = location.properties;

                // Format full address
                const addressParts = [
                    props.address_line1,
                    props.address_line2,
                    props.city || props.county,
                    props.postcode,
                    props.country
                ].filter(Boolean);

                const fullAddress = addressParts.join(', ');
                document.getElementById('physical_address').value = fullAddress;

                // Fill GPS coordinates
                if (props.lat && props.lon) {
                    const latitude = parseFloat(props.lat).toFixed(8);
                    const longitude = parseFloat(props.lon).toFixed(8);

                    document.getElementById('latitude').value = latitude;
                    document.getElementById('longitude').value = longitude;

                    // Mark that address was selected from dropdown
                    addressSelectedFromDropdown = true;

                    // Show success indicator
                    const statusDiv = document.getElementById('coordinates-status');
                    const statusText = document.getElementById('coordinates-text');
                    if (statusDiv && statusText) {
                        statusDiv.classList.remove('hidden');
                        statusDiv.querySelector('span').className = 'inline-flex items-center px-2 py-1 rounded-full bg-green-100 text-green-800';
                        statusText.textContent = `GPS coordinates captured (${latitude}, ${longitude})`;
                    }
                }
            }
        });

        // Clear button functionality
        addressAutocomplete.on('clear', function() {
            document.getElementById('physical_address').value = '';
            document.getElementById('latitude').value = '';
            document.getElementById('longitude').value = '';
            addressSelectedFromDropdown = false;
        });

        } catch (error) {
            console.error('Error initializing Geoapify autocomplete:', error);
            // Fallback: show a regular input if autocomplete fails
            const container = document.getElementById('autocomplete-container');
            if (container) {
                container.innerHTML = '<input type="text" id="physical_address_fallback" name="physical_address_input" value="<?= esc($merchant['physical_address'] ?? '') ?>" class="mt-1 block w-full rounded-md border-2 border-gray-400 px-3 py-2 shadow-sm focus:border-brand-blue focus:ring-brand-blue sm:text-sm" placeholder="Enter your business address..." required>';

                // Sync fallback input with hidden field
                const fallbackInput = document.getElementById('physical_address_fallback');
                if (fallbackInput) {
                    fallbackInput.addEventListener('input', function() {
                        document.getElementById('physical_address').value = this.value;
                    });
                }
            }
        }
    } else {
        console.warn('Geoapify not available. API Key:', !!GEOAPIFY_API_KEY, 'autocomplete object:', typeof autocomplete);
        // Fallback: show a regular input
        const container = document.getElementById('autocomplete-container');
        if (container) {
            container.innerHTML = '<input type="text" id="physical_address_fallback" name="physical_address_input" value="<?= esc($merchant['physical_address'] ?? '') ?>" class="mt-1 block w-full rounded-md border-2 border-gray-400 px-3 py-2 shadow-sm focus:border-brand-blue focus:ring-brand-blue sm:text-sm" placeholder="Enter your business address..." required>';

            // Sync fallback input with hidden field
            const fallbackInput = document.getElementById('physical_address_fallback');
            if (fallbackInput) {
                fallbackInput.addEventListener('input', function() {
                    document.getElementById('physical_address').value = this.value;
                });
            }
        }
    }

    // Form validation - require address selection from dropdown
    const merchantProfileForm = document.querySelector('form[action$="profile/merchant/update"]');
    if (merchantProfileForm) {
        merchantProfileForm.addEventListener('submit', function(e) {
            const latitude = document.getElementById('latitude').value;
            const longitude = document.getElementById('longitude').value;

            // Check if address was selected from dropdown (has coordinates)
            if (!latitude || !longitude || latitude === '0' || longitude === '0' || !addressSelectedFromDropdown) {
                e.preventDefault();

                // Show error message
                alert(
                    'Please select your address from the dropdown suggestions.\n\n' +
                    'Start typing your address and select it from the list that appears. ' +
                    'This ensures GPS coordinates are captured correctly so drivers can find your location.'
                );

                // Highlight the address field
                const autocompleteInput = document.querySelector('#autocomplete-container input');
                if (autocompleteInput) {
                    autocompleteInput.focus();
                    autocompleteInput.style.borderColor = '#ef4444';
                    autocompleteInput.style.borderWidth = '2px';

                    // Reset border after 3 seconds
                    setTimeout(() => {
                        autocompleteInput.style.borderColor = '';
                        autocompleteInput.style.borderWidth = '';
                    }, 3000);
                }

                return false;
            }
        });
    }
    </script>

<?= view('merchant/templates/footer', ['geoapify_api_key' => $geoapify_api_key ?? '']) ?>
