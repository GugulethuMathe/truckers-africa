<?= view('admin/templates/header', ['page_title' => 'Add Merchant']) ?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Add New Merchant</h1>
        <a href="<?= site_url('admin/merchants/all') ?>" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
            Back to Merchants
        </a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p class="font-bold">Success</p>
            <p><?= session()->getFlashdata('success') ?></p>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p class="font-bold">Error</p>
            <p><?= session()->getFlashdata('error') ?></p>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form method="POST" action="<?= site_url('admin/merchants/add') ?>">
            <?= csrf_field() ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Owner Information -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">Owner Information</h3>
                    
                    <div>
                        <label for="owner_name" class="block text-sm font-medium text-gray-700 mb-2">Owner Name *</label>
                        <input type="text" id="owner_name" name="owner_name" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="<?= old('owner_name') ?>">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                        <input type="email" id="email" name="email" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="<?= old('email') ?>">
                    </div>
                </div>

                <!-- Business Information -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">Business Information</h3>
                    
                    <div>
                        <label for="business_name" class="block text-sm font-medium text-gray-700 mb-2">Business Name *</label>
                        <input type="text" id="business_name" name="business_name" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="<?= old('business_name') ?>">
                    </div>

                    <div>
                        <label for="main_service" class="block text-sm font-medium text-gray-700 mb-2">Main Service Category</label>
                        <select id="main_service" name="main_service"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select a main service category (optional)</option>
                            <?php foreach ($service_categories as $category): ?>
                                <option value="<?= esc($category['name']) ?>" <?= old('main_service') === $category['name'] ? 'selected' : '' ?>>
                                    <?= esc($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Choose the primary service category that best describes the business.</p>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">Contact Information</h3>
                    
                    <div>
                        <label for="business_contact_number" class="block text-sm font-medium text-gray-700 mb-2">Business Phone *</label>
                        <input type="tel" id="business_contact_input" name="business_contact_display" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               data-initial="<?= old('business_contact_number') ?>">
                        <input type="hidden" id="business_contact_number" name="business_contact_number" value="<?= old('business_contact_number') ?>">
                    </div>

                    <div>
                        <label for="business_whatsapp_number" class="block text-sm font-medium text-gray-700 mb-2">WhatsApp Number</label>
                        <input type="tel" id="business_whatsapp_input" name="business_whatsapp_display"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               data-initial="<?= old('business_whatsapp_number') ?>">
                        <input type="hidden" id="business_whatsapp_number" name="business_whatsapp_number" value="<?= old('business_whatsapp_number') ?>">
                    </div>

                    <div>
                        <label for="physical_address" class="block text-sm font-medium text-gray-700 mb-2">Physical Address <span class="text-red-500">*</span></label>
                        <div id="autocomplete-container" class="relative">
                            <input type="text" id="physical_address" name="physical_address"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="Enter business address and select from dropdown..."
                                   value="<?= old('physical_address') ?>"
                                   required>
                            <input type="hidden" id="latitude" name="latitude" value="<?= old('latitude') ?>">
                            <input type="hidden" id="longitude" name="longitude" value="<?= old('longitude') ?>">
                            <input type="hidden" id="address_selected" name="address_selected" value="<?= old('address_selected') ?>">
                        </div>
                        <p class="mt-1 text-xs text-gray-500"><strong>Important:</strong> You must select an address from the dropdown suggestions.</p>
                        <p id="address-validation-error" class="mt-1 text-xs text-red-600 hidden">Please select an address from the dropdown suggestions.</p>
                    </div>
                </div>

                <!-- Status and Settings -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">Status & Settings</h3>
                    
                    <div>
                        <label for="verification_status" class="block text-sm font-medium text-gray-700 mb-2">Verification Status</label>
                        <select id="verification_status" name="verification_status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="pending" <?= old('verification_status') === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="approved" <?= old('verification_status') === 'approved' ? 'selected' : '' ?>>Approved</option>
                            <option value="rejected" <?= old('verification_status') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                            <option value="suspended" <?= old('verification_status') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                        </select>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="is_visible" name="is_visible" value="1" 
                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                               <?= old('is_visible') ? 'checked' : '' ?>>
                        <label for="is_visible" class="ml-2 block text-sm text-gray-900">
                            Make merchant visible to drivers
                        </label>
                    </div>
                </div>
            </div>

            <!-- Descriptions -->
            <div class="mt-8 space-y-4">
                <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">Descriptions</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="profile_description" class="block text-sm font-medium text-gray-700 mb-2">Profile Description</label>
                        <textarea id="profile_description" name="profile_description" rows="4"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                  placeholder="Brief description about the owner/profile..."><?= old('profile_description') ?></textarea>
                    </div>

                    <div>
                        <label for="business_description" class="block text-sm font-medium text-gray-700 mb-2">Business Description</label>
                        <textarea id="business_description" name="business_description" rows="4"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                  placeholder="Detailed description about the business..."><?= old('business_description') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="mt-8 flex justify-end space-x-4">
                <a href="<?= site_url('admin/merchants/all') ?>" 
                   class="bg-gray-500 text-white px-6 py-2 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Cancel
                </a>
                <button type="submit" 
                        class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    Add Merchant
                </button>
            </div>
        </form>
    </div>
</div>

<!-- International Telephone Input CSS and JS -->
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

    /* Address suggestions styling */
    .suggestions-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #d1d5db;
        border-top: none;
        border-radius: 0 0 0.375rem 0.375rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        max-height: 200px;
        overflow-y: auto;
        display: none;
    }

    .suggestion-item {
        padding: 0.75rem;
        cursor: pointer;
        border-bottom: 1px solid #f3f4f6;
        transition: background-color 0.15s ease;
    }

    .suggestion-item:hover {
        background-color: #f9fafb;
    }

    .suggestion-item:last-child {
        border-bottom: none;
    }

    .suggestion-text {
        font-size: 0.875rem;
        color: #374151;
    }

    .suggestion-subtext {
        font-size: 0.75rem;
        color: #6b7280;
        margin-top: 0.25rem;
    }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/intlTelInput.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js"></script>
<script>
    // Phone number initialization
    (function() {
        var contactInput = document.getElementById('business_contact_input');
        var whatsappInput = document.getElementById('business_whatsapp_input');
        var form = document.querySelector('form[action$="admin/merchants/add"]');
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

        form.addEventListener('submit', function(e) {
            // Validate phone numbers
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

            // Validate address selection
            const addressSelected = document.getElementById('address_selected').value;
            const physicalAddress = document.getElementById('physical_address').value.trim();

            if (physicalAddress && !addressSelected) {
                e.preventDefault();

                // Show error
                document.getElementById('physical_address').classList.add('border-red-500');
                document.getElementById('address-validation-error').classList.remove('hidden');

                // Scroll to error
                document.getElementById('physical_address').scrollIntoView({ behavior: 'smooth', block: 'center' });

                // Show alert
                alert('Please select an address from the dropdown suggestions before submitting.');

                return false;
            }
        });
    })();

    // Address autocomplete functionality
    document.addEventListener('DOMContentLoaded', function() {
        const apiKey = "<?= getenv('GEOAPIFY_API_KEY') ?>";
        const physicalAddressInput = document.getElementById('physical_address');

        if (!apiKey || !physicalAddressInput) {
            console.warn('Address autocomplete not available: missing API key or input field');
            return;
        }

        let debounceTimer;
        let suggestionsList;

        // Create suggestions dropdown
        function createSuggestionsDropdown() {
            suggestionsList = document.createElement('div');
            suggestionsList.className = 'suggestions-dropdown';
            suggestionsList.id = 'address-suggestions';
            document.getElementById('autocomplete-container').appendChild(suggestionsList);
        }

        // Fetch suggestions from Geoapify API
        async function fetchSuggestions(query) {
            try {
                const response = await fetch(`https://api.geoapify.com/v1/geocode/autocomplete?text=${encodeURIComponent(query)}&apiKey=${apiKey}&limit=5&filter=countrycode:za`);
                const data = await response.json();
                return data.features || [];
            } catch (error) {
                console.error('Error fetching address suggestions:', error);
                return [];
            }
        }

        // Display suggestions
        function displaySuggestions(suggestions) {
            suggestionsList.innerHTML = '';

            if (suggestions.length === 0) {
                hideSuggestions();
                return;
            }

            suggestions.forEach(function(suggestion) {
                const item = document.createElement('div');
                item.className = 'suggestion-item';

                const text = document.createElement('div');
                text.className = 'suggestion-text';
                text.textContent = suggestion.properties.formatted;

                const subtext = document.createElement('div');
                subtext.className = 'suggestion-subtext';
                subtext.textContent = suggestion.properties.address_line2 || suggestion.properties.country;

                item.appendChild(text);
                item.appendChild(subtext);

                item.addEventListener('click', function() {
                    physicalAddressInput.value = suggestion.properties.formatted;

                    // Store coordinates and mark as selected
                    document.getElementById('latitude').value = suggestion.properties.lat || '';
                    document.getElementById('longitude').value = suggestion.properties.lon || '';
                    document.getElementById('address_selected').value = '1';

                    // Remove error styling
                    physicalAddressInput.classList.remove('border-red-500');
                    document.getElementById('address-validation-error').classList.add('hidden');

                    hideSuggestions();
                });

                suggestionsList.appendChild(item);
            });

            suggestionsList.style.display = 'block';
        }

        // Hide suggestions
        function hideSuggestions() {
            if (suggestionsList) {
                suggestionsList.style.display = 'none';
            }
        }

        // Initialize
        createSuggestionsDropdown();

        // Input event listener
        physicalAddressInput.addEventListener('input', function(e) {
            const query = e.target.value.trim();

            // Clear selection when user manually types
            document.getElementById('latitude').value = '';
            document.getElementById('longitude').value = '';
            document.getElementById('address_selected').value = '';

            clearTimeout(debounceTimer);

            if (query.length < 3) {
                hideSuggestions();
                return;
            }

            debounceTimer = setTimeout(async () => {
                const suggestions = await fetchSuggestions(query);
                displaySuggestions(suggestions);
            }, 300);
        });

        // Hide suggestions when clicking outside
        document.addEventListener('click', function(e) {
            if (!physicalAddressInput.contains(e.target) && !suggestionsList.contains(e.target)) {
                hideSuggestions();
            }
        });
    });
</script>

<?= view('admin/templates/footer') ?>
