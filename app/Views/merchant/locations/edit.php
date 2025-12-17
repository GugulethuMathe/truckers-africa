<?php
// Helper function to get currency symbol
function getCurrencySymbolLocation($currency) {
    $symbols = [
        'ZAR' => 'R',
        'USD' => '$',
        'BWP' => 'P',
        'NAD' => 'N$',
        'ZMW' => 'ZK',
        'KES' => 'KSh',
        'TZS' => 'TSh',
        'UGX' => 'USh',
        'EUR' => '€',
        'GBP' => '£',
        'NGN' => '₦',
        'GHS' => 'GH₵',
        'MZN' => 'MT',
        'ZWL' => 'Z$'
    ];
    return $symbols[$currency] ?? $currency;
}
?>
<?= view('merchant/templates/header', ['page_title' => $page_title ?? 'Edit Branch']) ?>

<div class="container-fluid px-4 py-6">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="<?= site_url('merchant/locations') ?>" class="inline-flex items-center text-gray-600 hover:text-gray-900">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Branches
        </a>
    </div>

    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Edit Branch</h1>
        <p class="text-gray-600 mt-1">Update branch details</p>
    </div>

    <!-- Error Messages -->
    <?php if (session()->get('errors')): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            <strong class="font-bold">Oops!</strong>
            <ul class="mt-2 list-disc list-inside">
                <?php foreach (session()->get('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (session()->get('error')): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            <p><?= session()->get('error') ?></p>
        </div>
    <?php endif; ?>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Form (2/3 width) -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow p-6">
        <form action="<?= site_url('merchant/locations/update/' . $location['id']) ?>" method="post" id="locationForm">
            <?= csrf_field() ?>

            <?php if ($location['is_primary']): ?>
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                This is your primary location. It will be used as the default for new service listings.
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Branch Name -->
            <div class="mb-6">
                <label for="location_name" class="block text-sm font-medium text-gray-700 mb-2">
                    Branch Name <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="location_name"
                       id="location_name"
                       value="<?= old('location_name', $location['location_name']) ?>"
                       placeholder="e.g., Johannesburg Branch, Cape Town Office"
                       required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Physical Address with Autocomplete -->
            <div class="mb-6">
                <label for="physical_address" class="block text-sm font-medium text-gray-700 mb-2">
                    Full Physical Address <span class="text-red-500">*</span>
                </label>
                <div id="autocomplete-container"></div>
                <!-- Fallback manual input (shown if autocomplete fails) -->
                <input type="text"
                       id="manual_address_input"
                       placeholder="Type your full address here..."
                       value="<?= old('physical_address', $location['physical_address']) ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 hidden">
                <textarea name="physical_address"
                          id="physical_address"
                          rows="3"
                          required
                          class="hidden"><?= old('physical_address', $location['physical_address']) ?></textarea>
                <p class="mt-1 text-xs text-gray-500">Start typing to search for your address.</p>
                <div id="coordinates-status" class="mt-2 text-xs <?= (!empty($location['latitude']) && !empty($location['longitude']) && $location['latitude'] != 0 && $location['longitude'] != 0) ? '' : 'hidden' ?>">
                    <span class="inline-flex items-center px-2 py-1 rounded-full <?= (!empty($location['latitude']) && !empty($location['longitude']) && $location['latitude'] != 0 && $location['longitude'] != 0) ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                        <i class="fas fa-map-marker-alt mr-1"></i>
                        <span id="coordinates-text">
                            <?php if (!empty($location['latitude']) && !empty($location['longitude']) && $location['latitude'] != 0 && $location['longitude'] != 0): ?>
                                GPS coordinates: <?= esc($location['latitude']) ?>, <?= esc($location['longitude']) ?>
                            <?php else: ?>
                                Warning: No GPS coordinates set
                            <?php endif; ?>
                        </span>
                    </span>
                </div>
            </div>

            <!-- GPS Coordinates (Hidden - Auto-filled from address) -->
            <input type="hidden" name="latitude" id="latitude" value="<?= old('latitude', $location['latitude']) ?>">
            <input type="hidden" name="longitude" id="longitude" value="<?= old('longitude', $location['longitude']) ?>">

            <!-- Contact Information -->
            <div class="border-t pt-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Contact Information</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="contact_number" class="block text-sm font-medium text-gray-700 mb-2">
                            Contact Number <span class="text-red-500">*</span>
                        </label>
                        <input type="tel"
                               name="contact_number"
                               id="contact_number"
                               value="<?= old('contact_number', $location['contact_number']) ?>"
                               placeholder="27123456789"
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="whatsapp_number" class="block text-sm font-medium text-gray-700 mb-2">
                            WhatsApp Number
                        </label>
                        <input type="tel"
                               name="whatsapp_number"
                               id="whatsapp_number"
                               value="<?= old('whatsapp_number', $location['whatsapp_number']) ?>"
                               placeholder="27123456789"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email Address
                    </label>
                    <input type="email"
                           name="email"
                           id="email"
                           value="<?= old('email', $location['email']) ?>"
                           placeholder="branch@example.com"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <!-- Operating Hours -->
            <div class="border-t pt-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Operating Hours</h3>
                <textarea name="operating_hours"
                          id="operating_hours"
                          rows="3"
                          placeholder="e.g., Monday-Friday: 08:00-17:00, Saturday: 08:00-13:00, Sunday: Closed"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"><?= old('operating_hours', $location['operating_hours']) ?></textarea>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-between">
                <div>
                    <?php if (!$location['is_primary']): ?>
                        <button type="button" onclick="confirmDelete()" class="px-6 py-2 border border-red-300 text-red-700 rounded-lg hover:bg-red-50 transition">
                            Delete Location
                        </button>
                    <?php endif; ?>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="<?= site_url('merchant/locations') ?>" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Update Location
                    </button>
                </div>
            </div>
        </form>
            </div>
        </div>

        <!-- Right Column - Listings Sidebar (1/3 width) -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow p-6 sticky top-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Service Listings</h3>
                    <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                        <?= $listingsCount ?>
                    </span>
                </div>

                <a href="<?= site_url('merchant/listings/new?location_id=' . $location['id']) ?>"
                   class="block w-full mb-4 px-4 py-2 bg-blue-600 text-white text-center rounded-lg hover:bg-blue-700 transition">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add New Listing
                </a>

                <?php if (empty($listings)): ?>
                    <div class="text-center py-8">
                        <svg class="w-16 h-16 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-gray-500 text-sm">No listings yet</p>
                        <p class="text-gray-400 text-xs mt-1">Create your first listing for this location</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        <?php foreach ($listings as $listing):
                            $listingCurrency = $listing['currency_code'] ?? 'ZAR';
                            $listingSymbol = getCurrencySymbolLocation($listingCurrency);
                            $listingPrice = $listing['price_numeric'] ?? $listing['price'] ?? 0;
                        ?>
                            <div class="border border-gray-200 rounded-lg p-3 hover:border-blue-300 transition">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-gray-900 mb-1">
                                            <?= esc($listing['title']) ?>
                                        </h4>
                                        <?php if (!empty($listingPrice) && $listingPrice > 0): ?>
                                            <p class="text-xs text-gray-600 mb-2">
                                                <?= $listingSymbol ?><?= number_format((float)$listingPrice, 2) ?>
                                            </p>
                                        <?php endif; ?>
                                        <span class="inline-block px-2 py-1 text-xs rounded <?= $listing['status'] === 'approved' ? 'bg-green-100 text-green-800' : ($listing['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                                            <?= ucfirst(esc($listing['status'])) ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($listing['main_image_path'])): ?>
                                        <img src="<?= get_listing_image_url($listing['main_image_path']) ?>"
                                             alt="<?= esc($listing['title']) ?>"
                                             class="w-12 h-12 rounded object-cover ml-2">
                                    <?php endif; ?>
                                </div>
                                <div class="mt-2 flex gap-2">
                                    <a href="<?= site_url('merchant/listings/edit/' . $listing['id']) ?>"
                                       class="text-xs text-blue-600 hover:text-blue-800">
                                        Edit
                                    </a>
                                    <a href="<?= site_url('merchant/listings/view/' . $listing['id']) ?>"
                                       class="text-xs text-gray-600 hover:text-gray-800">
                                        View
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" type="text/css" href="https://unpkg.com/@geoapify/geocoder-autocomplete@latest/styles/minimal.css">
<style>
/* Fix autocomplete z-index to appear above form elements */
.geoapify-autocomplete-items,
.geoapify-autocomplete-items-list,
#autocomplete-container .geoapify-autocomplete-items {
    z-index: 9999 !important;
    position: relative;
}

#autocomplete-container {
    position: relative;
    z-index: 100;
}

/* Style the autocomplete input to match other form inputs */
#autocomplete-container input,
.geoapify-autocomplete-input {
    width: 100%;
    padding: 0.5rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    line-height: 1.25rem;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

#autocomplete-container input:focus,
.geoapify-autocomplete-input:focus {
    outline: none;
    border-color: #3b82f6;
    ring: 2px;
    ring-color: rgba(59, 130, 246, 0.5);
}

/* Style the dropdown */
.geoapify-autocomplete-items {
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    margin-top: 0.25rem;
    background: white;
}
</style>
<script src="https://unpkg.com/@geoapify/geocoder-autocomplete@latest/dist/index.min.js"></script>
<script>
// Geoapify Address Autocomplete
const GEOAPIFY_API_KEY = '<?= esc($geoapify_api_key ?? '') ?>';

console.log('Initializing location address autocomplete...');
console.log('API Key available:', !!GEOAPIFY_API_KEY);

if (!GEOAPIFY_API_KEY) {
    console.error('Geoapify API key not found');
}

const addressAutocomplete = GEOAPIFY_API_KEY ? new autocomplete.GeocoderAutocomplete(
    document.getElementById('autocomplete-container'),
    GEOAPIFY_API_KEY,
    {
        placeholder: 'Start typing your address...',
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
) : null;

// Check if autocomplete initialized successfully and set existing address value
setTimeout(function() {
    const autocompleteInput = document.querySelector('#autocomplete-container input');
    const manualInput = document.getElementById('manual_address_input');
    const existingAddress = '<?= esc(addslashes($location['physical_address'] ?? '')) ?>';

    if (!autocompleteInput && manualInput) {
        // Autocomplete failed, show manual input
        console.warn('Autocomplete failed to initialize. Using manual input.');
        manualInput.classList.remove('hidden');

        // Sync manual input with hidden textarea
        manualInput.addEventListener('input', function() {
            document.getElementById('physical_address').value = this.value;
        });
    } else if (autocompleteInput && existingAddress) {
        // Set the existing address value in the autocomplete input
        autocompleteInput.value = existingAddress;
        console.log('Pre-filled existing address:', existingAddress);
    }
}, 500);

// When user selects an address from suggestions
if (addressAutocomplete) {
    console.log('Address autocomplete initialized successfully');

    addressAutocomplete.on('select', function(location) {
        console.log('Address selected event triggered');
        console.log('Location object:', location);

        if (location && location.properties) {
            const props = location.properties;
            console.log('Location properties:', props);

            // Format full address
            const addressParts = [
                props.address_line1,
                props.address_line2,
                props.city || props.county,
                props.postcode,
                props.country
            ].filter(Boolean); // Remove empty values

            const fullAddress = addressParts.join(', ');
            console.log('Formatted address:', fullAddress);

            // Fill the textarea
            document.getElementById('physical_address').value = fullAddress;
            console.log('Physical address field updated');

            // Fill GPS coordinates - Geoapify returns coordinates in properties.lat and properties.lon
            if (props.lat && props.lon) {
                const latitude = parseFloat(props.lat).toFixed(8);
                const longitude = parseFloat(props.lon).toFixed(8);

                console.log('Setting coordinates - Latitude:', latitude, 'Longitude:', longitude);

                const latField = document.getElementById('latitude');
                const lngField = document.getElementById('longitude');

                console.log('Latitude field found:', !!latField);
                console.log('Longitude field found:', !!lngField);

                if (latField && lngField) {
                    latField.value = latitude;
                    lngField.value = longitude;
                    console.log('Coordinates set successfully');
                    console.log('Latitude field value:', latField.value);
                    console.log('Longitude field value:', lngField.value);

                    // Mark that address was selected from dropdown
                    addressSelectedFromDropdown = true;
                } else {
                    console.error('Could not find latitude or longitude fields!');
                }

                // Show success indicator
                const statusDiv = document.getElementById('coordinates-status');
                const statusText = document.getElementById('coordinates-text');
                if (statusDiv && statusText) {
                    statusDiv.classList.remove('hidden');
                    statusDiv.querySelector('span').className = 'inline-flex items-center px-2 py-1 rounded-full bg-green-100 text-green-800';
                    statusText.textContent = `GPS coordinates captured (${latitude}, ${longitude})`;
                }
            } else {
                console.warn('No coordinates found in location.properties');
                console.log('Available properties:', props);

                // Show warning indicator
                const statusDiv = document.getElementById('coordinates-status');
                const statusText = document.getElementById('coordinates-text');
                if (statusDiv && statusText) {
                    statusDiv.classList.remove('hidden');
                    statusDiv.querySelector('span').className = 'inline-flex items-center px-2 py-1 rounded-full bg-yellow-100 text-yellow-800';
                    statusText.textContent = 'Warning: No GPS coordinates available for this address';
                }
            }
        } else {
            console.warn('Location or location.properties is null');
        }
    });

    // Clear button functionality
    addressAutocomplete.on('clear', function() {
        document.getElementById('physical_address').value = '';
        document.getElementById('latitude').value = '';
        document.getElementById('longitude').value = '';
        addressSelectedFromDropdown = false; // Reset flag when cleared
    });
} else {
    console.warn('Address autocomplete not initialized. Showing manual input.');
    // Show manual input immediately
    const manualInput = document.getElementById('manual_address_input');
    if (manualInput) {
        manualInput.classList.remove('hidden');
        manualInput.addEventListener('input', function() {
            document.getElementById('physical_address').value = this.value;
            // Note: Manual input doesn't provide coordinates
            console.warn('Manual address input - coordinates will not be set');
        });
    }
}

// Track if user has selected an address from the dropdown
let addressSelectedFromDropdown = <?= (!empty($location['latitude']) && !empty($location['longitude'])) ? 'true' : 'false' ?>; // Pre-set to true if location already has coordinates

// Form submission validation - require address selection from dropdown (unless editing existing location with coordinates)
const locationForm = document.getElementById('locationForm');
if (locationForm) {
    locationForm.addEventListener('submit', function(e) {
        const latitude = document.getElementById('latitude').value;
        const longitude = document.getElementById('longitude').value;
        const physicalAddress = document.getElementById('physical_address').value;

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
} else {
    console.error('Location form not found');
}

function getMyLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            document.getElementById('latitude').value = position.coords.latitude.toFixed(8);
            document.getElementById('longitude').value = position.coords.longitude.toFixed(8);
            alert('Location updated successfully!');
        }, function(error) {
            alert('Unable to get your location. Please enter coordinates manually.');
        });
    } else {
        alert('Geolocation is not supported by your browser.');
    }
}

function confirmDelete() {
    if (confirm('Are you sure you want to delete this location? This action cannot be undone.')) {
        window.location.href = '<?= site_url('merchant/locations/delete/' . $location['id']) ?>';
    }
}
</script>

<?= view('merchant/templates/footer') ?>
