<?= view('merchant/templates/header', ['page_title' => $page_title]) ?>

<div class="px-6 py-8">
    <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-md">
        <h1 class="text-2xl font-bold mb-6">Update Your Business Profile</h1>

        <?php if (session()->has('errors')): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                <ul><?php foreach (session('errors') as $error): ?><li><?= esc($error) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form action="<?= site_url('profile/merchant/update') ?>" method="post">
            <?= csrf_field() ?>
            <div class="space-y-6">
                <div>
                    <label for="business_name" class="block text-sm font-medium text-gray-700">Business Name</label>
                    <input type="text" name="business_name" id="business_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" value="<?= old('business_name', $merchant['business_name']) ?>" required>
                </div>
                <div>
                    <label for="business_contact_number" class="block text-sm font-medium text-gray-700">Primary Contact Number</label>
                    <input type="tel" name="business_contact_number" id="business_contact_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" value="<?= old('business_contact_number', $merchant['business_contact_number']) ?>" required>
                </div>
                <div>
                    <label for="business_whatsapp_number" class="block text-sm font-medium text-gray-700">WhatsApp Number (Optional)</label>
                    <input type="tel" name="business_whatsapp_number" id="business_whatsapp_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" value="<?= old('business_whatsapp_number', $merchant['business_whatsapp_number']) ?>">
                </div>
                <div>
                    <label for="physical_address" class="block text-sm font-medium text-gray-700">Full Physical Address</label>
                    <div id="autocomplete-container"></div>
                    <textarea name="physical_address" id="physical_address" rows="3" placeholder="Start typing your address..." class="mt-2 block w-full rounded-md border-gray-300 shadow-sm" required><?= old('physical_address', $merchant['physical_address']) ?></textarea>
                     <p class="mt-1 text-xs text-gray-500">Start typing to search for your address, or enter it manually.</p>
                </div>
                <div>
                    <label for="main_service" class="block text-sm font-medium text-gray-700">Main Service Category</label>
                    <select name="main_service" id="main_service" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
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
                    <label for="profile_description" class="block text-sm font-medium text-gray-700">Business Description</label>
                    <textarea name="profile_description" id="profile_description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"><?= old('profile_description', $merchant['profile_description']) ?></textarea>
                </div>
            </div>
            <div class="mt-8 border-t border-gray-200 pt-5">
                <div class="flex justify-end">
                    <a href="<?= site_url('dashboard/merchant') ?>" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
                    <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-brand-blue hover:bg-opacity-90">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>

<link rel="stylesheet" type="text/css" href="https://unpkg.com/@geoapify/geocoder-autocomplete@latest/styles/minimal.css">
<script src="https://unpkg.com/@geoapify/geocoder-autocomplete@latest/dist/index.min.js"></script>
<script>
// Geoapify Address Autocomplete
const GEOAPIFY_API_KEY = '9ecc969502bb4b458ff9fd88d54dbce7';

const addressAutocomplete = new autocomplete.GeocoderAutocomplete(
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
);

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
        ].filter(Boolean); // Remove empty values

        const fullAddress = addressParts.join(', ');

        // Fill the textarea
        document.getElementById('physical_address').value = fullAddress;
    }
});

// Clear button functionality
addressAutocomplete.on('clear', function() {
    document.getElementById('physical_address').value = '';
});
</script>

<?= view('merchant/templates/footer') ?>