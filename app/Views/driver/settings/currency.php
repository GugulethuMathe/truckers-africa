<?= view('driver/templates/header') ?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Currency Preferences</h1>
                    <p class="text-gray-600 mt-1">Choose how you want to see prices and payments</p>
                </div>
                <div class="text-right">
                    <button onclick="goBack()"
                            class="text-indigo-600 hover:text-indigo-800 font-medium">
                        ← Back
                    </button>
                </div>
            </div>
        </div>

        <!-- Currency Preference Form -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="mb-6 bg-green-50 border border-green-200 rounded-md p-4">
                    <div class="flex">
                        <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">
                                <?= session()->getFlashdata('success') ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('errors')): ?>
                <div class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
                    <div class="flex">
                        <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Please fix the following errors:</h3>
                            <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                    <li><?= esc($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <form action="<?= site_url('driver/settings/update-currency') ?>" method="POST">
                <?= csrf_field() ?>
                
                <!-- Current Currency Display -->
                <div class="mb-8 p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                        <div>
                            <h3 class="text-lg font-medium text-blue-900">Current Preference</h3>
                            <p class="text-blue-700">
                                You're currently viewing prices in 
                                <strong><?= esc($currentCurrency['currency_symbol']) ?> - <?= esc($currentCurrency['currency_name']) ?></strong>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Currency Selection -->
                <div class="mb-6">
                    <label for="preferred_currency" class="block text-sm font-medium text-gray-700 mb-3">
                        Select Your Preferred Currency
                    </label>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php foreach ($currencies as $currency): ?>
                            <div class="relative">
                                <input type="radio" 
                                       id="currency_<?= esc($currency['currency_code']) ?>" 
                                       name="preferred_currency" 
                                       value="<?= esc($currency['currency_code']) ?>"
                                       class="sr-only peer"
                                       <?= $driver['preferred_currency'] === $currency['currency_code'] ? 'checked' : '' ?>>
                                <label for="currency_<?= esc($currency['currency_code']) ?>" 
                                       class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-indigo-300 peer-checked:border-indigo-600 peer-checked:bg-indigo-50 transition-all">
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <div class="text-lg font-bold text-gray-900">
                                                    <?= esc($currency['currency_symbol']) ?>
                                                </div>
                                                <div class="text-sm font-medium text-gray-700">
                                                    <?= esc($currency['currency_code']) ?>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-sm text-gray-600">
                                                    <?= esc($currency['currency_name']) ?>
                                                </div>
                                                <?php if ($currency['priority'] == 1): ?>
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 mt-1">
                                                        Popular
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Sample Price Preview -->
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Price Preview Example</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600">Original listing price:</span>
                            <span class="font-medium">R 500 (ZAR)</span>
                        </div>
                        <div>
                            <span class="text-gray-600">You'll see:</span>
                            <span class="font-medium text-indigo-600" id="preview-price">Loading...</span>
                        </div>
                    </div>
                </div>

                <!-- Information Box -->
                <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex">
                        <svg class="w-5 h-5 text-yellow-400 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <h4 class="text-sm font-medium text-yellow-800">Important Information</h4>
                            <ul class="mt-2 text-sm text-yellow-700 list-disc list-inside space-y-1">
                                <li>Exchange rates are updated twice daily for accuracy</li>
                                <li>All payments are processed in the merchant's original currency</li>
                                <li>Converted prices are for display purposes only</li>
                                <li>You can change your preference anytime</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <button type="submit" 
                            class="bg-indigo-600 text-white px-6 py-3 rounded-md hover:bg-indigo-700 font-medium transition-colors">
                        Save Currency Preference
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= view('driver/templates/bottom_nav', ['current_page' => 'account']) ?>

<script>
// Price preview functionality
document.addEventListener('DOMContentLoaded', function() {
    const currencyRadios = document.querySelectorAll('input[name="preferred_currency"]');
    const previewPrice = document.getElementById('preview-price');
    
    // Sample exchange rates for preview (you can make this dynamic)
    const sampleRates = {
        'ZAR': 1.0,
        'USD': 0.054,
        'BWP': 0.73,
        'NAD': 1.0,
        'ZMW': 13.7,
        'KES': 7.8,
        'TZS': 125.0,
        'UGX': 200.0
    };
    
    function updatePreview() {
        const selectedCurrency = document.querySelector('input[name="preferred_currency"]:checked');
        if (selectedCurrency) {
            const currencyCode = selectedCurrency.value;
            const rate = sampleRates[currencyCode] || 1;
            const convertedAmount = (500 * rate).toFixed(2);
            
            // Get currency symbol
            const label = document.querySelector(`label[for="currency_${currencyCode}"]`);
            const symbol = label.querySelector('.text-lg').textContent;
            
            previewPrice.textContent = `${symbol}${convertedAmount} (${currencyCode})`;
        }
    }
    
    // Update preview when currency selection changes
    currencyRadios.forEach(radio => {
        radio.addEventListener('change', updatePreview);
    });
    
    // Initial preview update
    updatePreview();
});

// Back button functionality
function goBack() {
    // Check if there's a previous page in history
    if (document.referrer && document.referrer !== window.location.href) {
        window.history.back();
    } else {
        // Fallback to driver services page if no referrer
        window.location.href = '<?= base_url('driver/services') ?>';
    }
}

// Handle browser back button
window.addEventListener('popstate', function(event) {
    // This ensures proper handling of browser back button
    if (event.state) {
        window.location.reload();
    }
});
</script>

<?= view('driver/templates/footer') ?>
