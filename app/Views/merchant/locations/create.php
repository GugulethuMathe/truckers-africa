<?= view('merchant/templates/header', ['page_title' => $page_title ?? 'Add New Branch']) ?>

<div class="container-fluid px-4 py-6 max-w-4xl">
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
        <h1 class="text-2xl font-bold text-gray-900">Add New Branch</h1>
        <p class="text-gray-600 mt-1">Add a new branch for your business</p>
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

    <!-- Form -->
    <div class="bg-white rounded-lg shadow p-6">
        <form action="<?= site_url('merchant/locations/store') ?>" method="post" id="locationForm">
            <?= csrf_field() ?>

            <!-- Branch Name -->
            <div class="mb-6">
                <label for="location_name" class="block text-sm font-medium text-gray-700 mb-2">
                    Branch Name <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="location_name"
                       id="location_name"
                       value="<?= old('location_name') ?>"
                       placeholder="e.g., Johannesburg Branch, Cape Town Office"
                       required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <p class="text-xs text-gray-500 mt-1">Give this location a descriptive name</p>
            </div>

            <!-- Physical Address with Autocomplete -->
            <div class="mb-6">
                <label for="physical_address" class="block text-sm font-medium text-gray-700 mb-2">
                    Full Physical Address <span class="text-red-500">*</span>
                </label>
                <!-- Important Notice Box -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 mb-3">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                        <div class="text-sm text-blue-800">
                            <p class="font-semibold mb-1">How to enter your address:</p>
                            <ol class="list-decimal ml-4 space-y-1">
                                <li>Start typing your street address in the field below</li>
                                <li>Wait for dropdown suggestions to appear</li>
                                <li><strong>Click on your address</strong> from the list</li>
                                <li>Look for the green confirmation with GPS coordinates</li>
                            </ol>
                        </div>
                    </div>
                </div>
                <div id="autocomplete-container"></div>
                <!-- Fallback manual input (shown if autocomplete fails) -->
                <input type="text"
                       id="manual_address_input"
                       placeholder="Type your full address here..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 hidden">
                <textarea name="physical_address"
                          id="physical_address"
                          rows="3"
                          class="hidden"><?= old('physical_address') ?></textarea>
                <p class="mt-1 text-xs text-gray-600">
                    <span class="inline-flex items-center">
                        <svg class="w-4 h-4 mr-1 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        <strong>Must select from dropdown</strong> - Manual typing will not work
                    </span>
                </p>
                <p id="address-validation-error" class="mt-1 text-xs text-red-600 font-semibold hidden">Please select an address from the dropdown suggestions.</p>
                <div id="coordinates-status" class="mt-2 text-xs hidden">
                    <span class="inline-flex items-center px-2 py-1 rounded-full">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                        </svg>
                        <span id="coordinates-text"></span>
                    </span>
                </div>
            </div>

            <!-- GPS Coordinates (Hidden - Auto-filled from address autocomplete) -->
            <input type="hidden" name="latitude" id="latitude" value="<?= old('latitude') ?>">
            <input type="hidden" name="longitude" id="longitude" value="<?= old('longitude') ?>">
            <input type="hidden" name="address_selected" id="address_selected" value="<?= old('address_selected') ?>">

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
                               value="<?= old('contact_number') ?>"
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
                               value="<?= old('whatsapp_number') ?>"
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
                           value="<?= old('email') ?>"
                           placeholder="branch@example.com"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <!-- Operating Hours (Optional) -->
            <div class="border-t pt-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Operating Hours (Optional)</h3>
                <textarea name="operating_hours"
                          id="operating_hours"
                          rows="3"
                          placeholder="e.g., Monday-Friday: 08:00-17:00, Saturday: 08:00-13:00, Sunday: Closed"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"><?= old('operating_hours') ?></textarea>
                <p class="text-xs text-gray-500 mt-1">Describe your operating hours for this location</p>
            </div>

            <!-- Branch Manager Information -->
            <div class="border-t pt-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Branch Manager Account</h3>
                <p class="text-sm text-gray-600 mb-4">Create a login account for the branch manager. They will receive an email to set up their password.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="manager_full_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Manager Full Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="manager_full_name"
                               id="manager_full_name"
                               value="<?= old('manager_full_name') ?>"
                               placeholder="John Doe"
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="manager_phone" class="block text-sm font-medium text-gray-700 mb-2">
                            Manager Phone Number <span class="text-red-500">*</span>
                        </label>
                        <input type="tel"
                               name="manager_phone"
                               id="manager_phone"
                               value="<?= old('manager_phone') ?>"
                               placeholder="27123456789"
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="manager_email" class="block text-sm font-medium text-gray-700 mb-2">
                        Manager Email Address <span class="text-red-500">*</span>
                    </label>
                    <input type="email"
                           name="manager_email"
                           id="manager_email"
                           value="<?= old('manager_email') ?>"
                           placeholder="manager@example.com"
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-xs text-gray-500 mt-1">The manager will use this email to log in and will receive a password setup link</p>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end space-x-4">
                <a href="<?= site_url('merchant/locations') ?>" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    Add Location
                </button>
            </div>
        </form>
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

// Check if autocomplete initialized successfully
setTimeout(function() {
    const autocompleteInput = document.querySelector('#autocomplete-container input');
    const manualInput = document.getElementById('manual_address_input');

    if (!autocompleteInput && manualInput) {
        // Autocomplete failed, show manual input
        console.warn('Autocomplete failed to initialize. Using manual input.');
        manualInput.classList.remove('hidden');

        // Sync manual input with hidden textarea
        manualInput.addEventListener('input', function() {
            document.getElementById('physical_address').value = this.value;
        });
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
                    document.getElementById('address_selected').value = '1';

                    // Remove error styling if present
                    const autocompleteInput = document.querySelector('#autocomplete-container input');
                    if (autocompleteInput) {
                        autocompleteInput.style.borderColor = '';
                        autocompleteInput.style.borderWidth = '';
                    }
                    document.getElementById('address-validation-error').classList.add('hidden');
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
        document.getElementById('address_selected').value = '';
        addressSelectedFromDropdown = false; // Reset flag when cleared
    });

    // Clear selection when user manually types in the autocomplete field
    const autocompleteInput = document.querySelector('#autocomplete-container input');
    if (autocompleteInput) {
        autocompleteInput.addEventListener('input', function() {
            // Clear the flag when user manually types (not from selecting)
            if (addressSelectedFromDropdown) {
                document.getElementById('latitude').value = '';
                document.getElementById('longitude').value = '';
                document.getElementById('address_selected').value = '';
                addressSelectedFromDropdown = false;
            }
        });
    }
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
let addressSelectedFromDropdown = false;

// Form submission validation - require address selection from dropdown
const locationForm = document.getElementById('locationForm');
if (locationForm) {
    locationForm.addEventListener('submit', function(e) {
        const latitude = document.getElementById('latitude').value;
        const longitude = document.getElementById('longitude').value;
        const physicalAddress = document.getElementById('physical_address').value;
        const addressSelected = document.getElementById('address_selected').value;

        // Check if address field is empty
        if (!physicalAddress || physicalAddress.trim() === '') {
            e.preventDefault();

            // Show error message
            document.getElementById('address-validation-error').classList.remove('hidden');
            document.getElementById('address-validation-error').textContent = 'Physical address is required. Please select an address from the dropdown.';

            // Show alert
            alert(
                '⚠️ Physical Address Required!\n\n' +
                'Please enter and select your full physical address from the dropdown suggestions.\n\n' +
                'This ensures GPS coordinates are captured correctly so drivers can find your location.'
            );

            // Highlight the address field
            const autocompleteInput = document.querySelector('#autocomplete-container input');
            if (autocompleteInput) {
                autocompleteInput.focus();
                autocompleteInput.style.borderColor = '#ef4444';
                autocompleteInput.style.borderWidth = '2px';

                // Scroll to the field
                autocompleteInput.scrollIntoView({ behavior: 'smooth', block: 'center' });

                // Reset border after 5 seconds
                setTimeout(() => {
                    autocompleteInput.style.borderColor = '';
                    autocompleteInput.style.borderWidth = '';
                }, 5000);
            }

            return false;
        }

        // Check if address was selected from dropdown (has coordinates and flag)
        if (!addressSelected || addressSelected !== '1') {
            e.preventDefault();

            // Show error message
            document.getElementById('address-validation-error').classList.remove('hidden');
            document.getElementById('address-validation-error').textContent = 'Please select an address from the dropdown suggestions.';

            // Show alert
            alert(
                '⚠️ Address Not Selected from Dropdown!\n\n' +
                'You must SELECT your address from the dropdown list that appears as you type.\n\n' +
                'Why is this required?\n' +
                '• It captures accurate GPS coordinates\n' +
                '• Drivers can easily find your location\n' +
                '• Ensures proper address formatting\n\n' +
                'Please start typing your address again and click on one of the suggestions that appear.'
            );

            // Highlight the address field
            const autocompleteInput = document.querySelector('#autocomplete-container input');
            if (autocompleteInput) {
                // Clear the input so user can start fresh
                autocompleteInput.value = '';
                document.getElementById('physical_address').value = '';
                document.getElementById('latitude').value = '';
                document.getElementById('longitude').value = '';
                document.getElementById('address_selected').value = '';

                autocompleteInput.focus();
                autocompleteInput.style.borderColor = '#ef4444';
                autocompleteInput.style.borderWidth = '2px';

                // Scroll to the field
                autocompleteInput.scrollIntoView({ behavior: 'smooth', block: 'center' });

                // Reset border after 5 seconds
                setTimeout(() => {
                    autocompleteInput.style.borderColor = '';
                    autocompleteInput.style.borderWidth = '';
                }, 5000);
            }

            return false;
        }

        // If we reach here, validation passed - hide any previous error messages
        document.getElementById('address-validation-error').classList.add('hidden');
    });
} else {
    console.error('Location form not found');
}

function getMyLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            document.getElementById('latitude').value = position.coords.latitude.toFixed(8);
            document.getElementById('longitude').value = position.coords.longitude.toFixed(8);
            alert('Location captured successfully!');
        }, function(error) {
            alert('Unable to get your location. Please enter coordinates manually.');
        });
    } else {
        alert('Geolocation is not supported by your browser.');
    }
}
</script>

<?= view('merchant/templates/footer') ?>
