<?= view('templates/home-header', ['page_title' => 'Merchant Signup', 'page_class' => 'bg-gray-100']) ?>
<?php
    // The $googleUser variable is now passed directly from the controller
    $fullName = old('full_name', $googleUser['name'] ?? '');
    $email = old('email', $googleUser['email'] ?? '');
?>
<style>
    
    select#country_code {
    color: black !important;
}
span.iti__country-name {
    color: #0a0a0a;
}
.iti--separate-dial-code .iti__selected-dial-code {
    margin-left: 6px;
    color: black;
}
</style>
    <!-- Main container for the centered content -->
    <div class="flex flex-col items-center justify-center min-h-screen py-12 px-4">
        
        <!-- Back Arrow -->
        <div class="w-full max-w-md mb-4">
            <a href="<?= site_url('signup') ?>" class="inline-flex items-center text-gray-600 hover:text-gray-900">
                <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Back to Account Type Selection
            </a>
        </div>

        <!-- Main Content Box -->
        <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-md">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Create a Service Provider Account</h1>
                <p class="text-gray-600">List your services to thousands of drivers.</p>
            </div>

            <!-- Validation Errors & Flash Messages -->
            <?php if (session()->get('errors')) : ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Oops!</strong>
                    <ul class="mt-2 list-disc list-inside">
                        <?php foreach (session()->get('errors') as $error) : ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (session()->get('error')) : ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <p><?= session()->get('error') ?></p>
                </div>
            <?php endif; ?>

            <!-- Google Sign Up Button -->
            <!-- <div>
                <a href="<?= site_url('auth/google') ?>" class="w-full flex justify-center items-center py-3 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-all">
                    <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">
                        <path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12c0-6.627,5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24s8.955,20,20,20s20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"></path><path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"></path><path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z"></path><path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571l6.19,5.238C42.022,35.141,44,30.028,44,24C44,22.659,43.862,21.35,43.611,20.083z"></path>
                    </svg>
                    Continue with Google
                </a>
            </div> -->

            <!-- Divider -->
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <!-- <div class="w-full border-t border-gray-300"></div> -->
                </div>
                <!-- <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500">OR</span>
                </div> -->
            </div>

            <form id="merchantSignupForm" action="<?= site_url('signup/merchant/create') ?>" method="post">
                <?= csrf_field() ?>
                <div class="space-y-6">
                    <div>
                        <label for="full_name" class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" name="full_name" id="full_name" value="<?= esc($fullName) ?>" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-brand-blue focus:border-brand-blue sm:text-sm">
                    </div>

                    <div>
                        <label for="business_type" class="block text-sm font-medium text-gray-700 mb-3">Business Type</label>

                        <!-- Individual Option -->
                        <div class="mb-3">
                            <label class="flex items-start p-3 border border-gray-300 rounded-md cursor-pointer hover:bg-gray-50 transition-colors">
                                <input type="radio" name="business_type" value="individual" <?= old('business_type') === 'individual' ? 'checked' : '' ?> required class="mt-1 h-4 w-4 text-brand-blue focus:ring-brand-blue border-gray-300">
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">Individual / Sole Proprietor</div>
                                    <div class="text-xs text-gray-600 mt-1">
                                        Operating as an individual or sole proprietor. You'll need: ID document, proof of business address, and business image.
                                    </div>
                                </div>
                            </label>
                        </div>

                        <!-- Business Option -->
                        <div class="mb-3">
                            <label class="flex items-start p-3 border border-gray-300 rounded-md cursor-pointer hover:bg-gray-50 transition-colors">
                                <input type="radio" name="business_type" value="business" <?= old('business_type') === 'business' ? 'checked' : '' ?> required class="mt-1 h-4 w-4 text-brand-blue focus:ring-brand-blue border-gray-300">
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">Registered Business / Company</div>
                                    <div class="text-xs text-gray-600 mt-1">
                                        Registered company (Pty Ltd, CC, etc.). You'll need: Owner's ID, Company Registration Certificate , Proof of business address, and business image.
                                    </div>
                                </div>
                            </label>
                        </div>

                        <p class="text-xs text-gray-500 mt-2">💡 Choose the option that best describes your business structure. This determines the verification documents required.</p>
                    </div>

                    <div>
                        <label for="company_name" class="block text-sm font-medium text-gray-700">
                            <span id="company_name_label">Business/Company Name</span>
                        </label>
                        <input type="text" name="company_name" id="company_name" value="<?= old('company_name') ?>" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-brand-blue focus:border-brand-blue sm:text-sm">
                        <p class="text-xs text-gray-500 mt-1" id="company_name_help">Enter your business or trading name.</p>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input type="email" name="email" id="email" value="<?= esc($email) ?>" required <?= $googleUser ? 'readonly' : '' ?> class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-brand-blue focus:border-brand-blue sm:text-sm <?= $googleUser ? 'bg-gray-100' : '' ?>">
                    </div>

                    <div>
                        <label for="phone_input" class="block text-sm font-medium text-gray-700">Phone Number</label>
                        <input type="tel" id="phone_input" name="phone_visual" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-brand-blue focus:border-brand-blue sm:text-sm" />
                        <input type="hidden" name="phone" id="phone_combined" />
                        <!-- <p class="text-xs text-gray-500 mt-1">Select your country by flag and enter your number. We will save it as digits only, e.g. 27663827405.</p> -->
                    </div>

                    <?php if (!$googleUser): ?>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" name="password" id="password" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-brand-blue focus:border-brand-blue sm:text-sm">
                    </div>

                    <div>
                        <label for="password_confirm" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                        <input type="password" name="password_confirm" id="password_confirm" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-brand-blue focus:border-brand-blue sm:text-sm">
                    </div>
                    <?php endif; ?>

                    <div class="flex items-center">
                        <input id="terms" name="terms" type="checkbox" required class="h-4 w-4 text-brand-blue focus:ring-brand-blue border-gray-300 rounded">
                        <label for="terms" class="ml-2 block text-sm text-gray-900">
                            I agree to the <a href="<?= site_url('terms') ?>" class="text-brand-blue hover:underline">Terms and Conditions</a>
                        </label>
                    </div>

                    <div>
                        <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-brand-blue hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-blue transition-all">
                            Create Account
                        </button>
                    </div>
                </div>
            </form>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/css/intlTelInput.css" />
            <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/intlTelInput.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js"></script>
            <script>
                (function() {
                    var input = document.getElementById('phone_input');
                    var form = document.getElementById('merchantSignupForm');
                    if (!input || !form || !window.intlTelInput) return;

                    var iti = window.intlTelInput(input, {
                        initialCountry: 'za',
                        separateDialCode: true,
                        onlyCountries: ['za','bw','sz','ls','na','mw','mz','cd','ao','tz','ke','ug','ng','zm','zw'],
                        utilsScript: 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js'
                    });

                    form.addEventListener('submit', function() {
                        // Get E.164 format like +27663827405, convert to digits only
                        var full = iti.getNumber();
                        var digits = (full || '').replace(/\D+/g, '');
                        // Remove any trunk leading zeros immediately after country code (e.g., 27 0 66... -> 27 66...)
                        try {
                            var country = iti.getSelectedCountryData();
                            if (country && country.dialCode) {
                                var re = new RegExp('^' + country.dialCode + '0+');
                                digits = digits.replace(re, country.dialCode);
                            }
                        } catch (e) {}
                        document.getElementById('phone_combined').value = digits;
                    });
                })();

                // Business type change handler
                function updateBusinessTypeLabels() {
                    const businessTypeRadios = document.querySelectorAll('input[name="business_type"]');
                    const companyNameLabel = document.getElementById('company_name_label');
                    const companyNameHelp = document.getElementById('company_name_help');
                    const companyNameInput = document.getElementById('company_name');

                    let selectedType = '';
                    businessTypeRadios.forEach(radio => {
                        if (radio.checked) {
                            selectedType = radio.value;
                        }
                    });

                    if (selectedType === 'individual') {
                        companyNameLabel.textContent = 'Business/Trading Name';
                        companyNameHelp.textContent = 'Enter your business or trading name (e.g., "John\'s Transport Services").';
                        companyNameInput.placeholder = 'e.g., John\'s Transport Services';
                    } else if (selectedType === 'business') {
                        companyNameLabel.textContent = 'Registered Company Name';
                        companyNameHelp.textContent = 'Enter your official registered company name as it appears on your Company Registration Crtificate.';
                        companyNameInput.placeholder = 'e.g., ABC Transport (Pty) Ltd';
                    } else {
                        companyNameLabel.textContent = 'Business/Company Name';
                        companyNameHelp.textContent = 'Enter your business or trading name.';
                        companyNameInput.placeholder = '';
                    }
                }

                // Add event listeners to radio buttons
                document.querySelectorAll('input[name="business_type"]').forEach(radio => {
                    radio.addEventListener('change', updateBusinessTypeLabels);
                });

                // Initialize labels on page load
                updateBusinessTypeLabels();
            </script>
        </div>

        <div class="mt-8 text-sm text-gray-600 text-center">
            <p>Already have an account? <a href="<?= site_url('login') ?>" class="font-medium text-brand-blue hover:underline">Login here</a></p>
        </div>
    </div>

<?= view('templates/home-footer') ?>
