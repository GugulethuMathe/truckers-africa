<?= view('admin/templates/header', ['page_title' => 'Add Driver']) ?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Add New Driver</h1>
        <a href="<?= site_url('admin/drivers/all') ?>" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
            Back to Drivers
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
    <?php if (session()->get('errors')): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p class="font-bold">Validation Errors</p>
            <ul class="list-disc ml-5 mt-2">
                <?php foreach (session()->get('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>


    <div class="bg-white rounded-lg shadow-md p-6">
        <form method="POST" action="<?= site_url('admin/drivers/add') ?>" onsubmit="console.log('Form submitting...'); return true;"><?php echo csrf_field(); ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Personal Information -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">Personal Information</h3>
                    
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">First Name *</label>
                        <input type="text" id="name" name="name" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="<?= old('name') ?>">
                    </div>

                    <div>
                        <label for="surname" class="block text-sm font-medium text-gray-700 mb-2">Last Name *</label>
                        <input type="text" id="surname" name="surname" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="<?= old('surname') ?>">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                        <input type="email" id="email" name="email" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="<?= old('email') ?>">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password (Optional)</label>
                        <input type="password" id="password" name="password"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="Leave blank to let driver set password">
                        <p class="text-xs text-gray-500 mt-1">If left blank, driver will need to reset password on first login</p>
                    </div>

                    <div>
                        <label for="country_of_residence" class="block text-sm font-medium text-gray-700 mb-2">Country of Residence</label>
                        <input type="text" id="country_of_residence" name="country_of_residence"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="<?= old('country_of_residence') ?>">
                    </div>
                </div>

                <!-- Contact & Vehicle Information -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">Contact & Vehicle Information</h3>
                    
                    <div>
                        <label for="contact_number" class="block text-sm font-medium text-gray-700 mb-2">Contact Number *</label>
                        <input type="tel" id="contact_input" name="contact_display" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               data-initial="<?= old('contact_number') ?>">
                        <input type="hidden" id="contact_number" name="contact_number" value="<?= old('contact_number') ?>">
                    </div>

                    <div>
                        <label for="whatsapp_number" class="block text-sm font-medium text-gray-700 mb-2">WhatsApp Number</label>
                        <input type="tel" id="whatsapp_input" name="whatsapp_display"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               data-initial="<?= old('whatsapp_number') ?>">
                        <input type="hidden" id="whatsapp_number" name="whatsapp_number" value="<?= old('whatsapp_number') ?>">
                    </div>

                    <div>
                        <label for="vehicle_type" class="block text-sm font-medium text-gray-700 mb-2">Vehicle Type</label>
                        <input type="text" id="vehicle_type" name="vehicle_type"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="e.g., Truck, Van, Motorcycle, Pickup, Trailer"
                               value="<?= old('vehicle_type') ?>">
                    </div>

                    <div>
                        <label for="vehicle_registration" class="block text-sm font-medium text-gray-700 mb-2">Vehicle Registration</label>
                        <input type="text" id="vehicle_registration" name="vehicle_registration"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="<?= old('vehicle_registration') ?>">
                    </div>

                    <div>
                        <label for="license_number" class="block text-sm font-medium text-gray-700 mb-2">License Number</label>
                        <input type="text" id="license_number" name="license_number"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="<?= old('license_number') ?>">
                    </div>

                    <div>
                        <label for="preferred_search_radius_km" class="block text-sm font-medium text-gray-700 mb-2">Preferred Search Radius (km)</label>
                        <input type="number" id="preferred_search_radius_km" name="preferred_search_radius_km" min="1" max="500"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="<?= old('preferred_search_radius_km', 50) ?>">
                        <p class="text-xs text-gray-500 mt-1">Default: 50 km</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-4 mt-8 pt-6 border-t border-gray-200">
                <a href="<?= site_url('admin/drivers/all') ?>" class="bg-gray-500 text-white px-6 py-2 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Cancel
                </a>
                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                    Add Driver
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
    #contact_input, #whatsapp_input { padding-left: 52px !important; }
    .iti--separate-dial-code #contact_input,
    .iti--separate-dial-code #whatsapp_input { padding-left: 88px !important; }
    .iti__selected-flag { border-right: 1px solid #e5e7eb; }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/intlTelInput.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js"></script>
<script>
    // Phone number initialization
    (function() {
        var contactInput = document.getElementById('contact_input');
        var whatsappInput = document.getElementById('whatsapp_input');
        var form = document.querySelector('form[action$="admin/drivers/add"]');
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

        // Auto-populate WhatsApp number with contact number if empty
        contactInput.addEventListener('blur', function() {
            if (contactIti && whatsappIti && contactIti.getNumber() && !whatsappIti.getNumber()) {
                whatsappIti.setNumber(contactIti.getNumber());
            }
        });

        form.addEventListener('submit', function() {
            if (contactIti) {
                var full = contactIti.getNumber();
                var digits = (full || '').replace(/\D+/g, '');
                document.getElementById('contact_number').value = digits;
            }
            if (whatsappIti) {
                var fullW = whatsappIti.getNumber();
                var digitsW = (fullW || '').replace(/\D+/g, '');
                document.getElementById('whatsapp_number').value = digitsW;
            }
        });
    })();

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const requiredFields = ['name', 'surname', 'email'];
        let hasErrors = false;

        requiredFields.forEach(function(fieldName) {
            const field = document.getElementById(fieldName);
            if (!field.value.trim()) {
                field.classList.add('border-red-500');
                hasErrors = true;
            } else {
                field.classList.remove('border-red-500');
            }
        });

        // Check contact number separately since it's now handled by intl-tel-input
        const contactInput = document.getElementById('contact_input');
        if (!contactInput.value.trim()) {
            contactInput.classList.add('border-red-500');
            hasErrors = true;
        } else {
            contactInput.classList.remove('border-red-500');
        }

        if (hasErrors) {
            e.preventDefault();
            alert('Please fill in all required fields.');
        }
    });

    // Clear error styling when user starts typing
    document.querySelectorAll('input[required]').forEach(function(field) {
        field.addEventListener('input', function() {
            this.classList.remove('border-red-500');
        });
    });

    document.getElementById('contact_input').addEventListener('input', function() {
        this.classList.remove('border-red-500');
    });
</script>

<?= view('admin/templates/footer') ?>
