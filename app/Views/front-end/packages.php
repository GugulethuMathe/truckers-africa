<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<div class="container mx-auto px-4 py-16">
    <div class="text-center mb-12">
        <h1 class="text-4xl md:text-5xl font-extrabold text-white leading-tight">
            Flexible Plans for Every Merchant
        </h1>
        <p class="text-slate-400 mt-4 max-w-2xl mx-auto">Choose the plan that's right for your business. All plans are designed to help you connect with drivers and grow your reach.</p>
    </div>
    <style>
    .text-gray-700 {
    --tw-text-opacity: 1;
    color: rgb(255 255 255)!important;
}
.text-6xl {
    font-size: 2.3rem;
    line-height: 1;
}
</style>
    <!-- Currency Selector -->
    <div class="flex justify-end mb-4">
        <div class="relative" x-data="{ open: false }" @click.away="open = false">
            <button @click="open = !open" class="flex items-center space-x-1 px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 focus:outline-none border border-gray-300 rounded-md hover:bg-gray-50">
                <i class="fas fa-coins text-gray-500"></i>
                <span id="current-currency">USD</span>
                <i class="fas fa-chevron-down text-xs" :class="{'rotate-180': open}"></i>
            </button>

            <div x-show="open"
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50"
                 style="display: none;">

                <div class="px-3 py-2 text-xs font-medium text-gray-500 border-b">
                    Select Currency
                </div>

                <button onclick="changeCurrency('USD', '$')" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                    <span class="font-medium">$</span>
                    <span class="ml-2">US Dollar</span>
                </button>
                <button onclick="changeCurrency('ZAR', 'R')" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                    <span class="font-medium">R</span>
                    <span class="ml-2">South African Rand</span>
                </button>
                <button onclick="changeCurrency('BWP', 'P')" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                    <span class="font-medium">P</span>
                    <span class="ml-2">Botswana Pula</span>
                </button>
                <button onclick="changeCurrency('NAD', 'N$')" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                    <span class="font-medium">N$</span>
                    <span class="ml-2">Namibian Dollar</span>
                </button>
                <button onclick="changeCurrency('ZMW', 'ZK')" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                    <span class="font-medium">ZK</span>
                    <span class="ml-2">Zambian Kwacha</span>
                </button>
                <button onclick="changeCurrency('KES', 'KSh')" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                    <span class="font-medium">KSh</span>
                    <span class="ml-2">Kenyan Shilling</span>
                </button>
            </div>
        </div>
    </div>
 
    <!-- Pricing Section -->
    <div class="grid md:grid-cols-3 gap-8 max-w-7xl mx-auto">

        <?php if (empty($plans)): ?>
            <p class="text-white text-center md:col-span-3">No subscription plans have been configured yet.</p>
        <?php else: ?>
            <?php foreach ($plans as $plan): ?>
                <div class="bg-gray-800 rounded-lg shadow-lg p-8 flex flex-col justify-between transform hover:scale-105 transition-transform duration-300 <?= ($plan['name'] === 'Professional') ? 'border-2 border-blue-500' : '' ?>">
                    <div>
                        <h3 class="text-2xl font-bold text-white text-center mb-4"><?= esc($plan['name']) ?></h3>
                        <div class="text-center mb-6">
                            <?php if ($plan['has_trial'] && $plan['trial_days'] > 0): ?>
                                <p class="text-6xl font-extrabold text-white mb-3">
                                    <span class="price" data-original-price="<?= esc($plan['price']) ?>" data-original-currency="USD">$<?= esc(number_format($plan['price'], 2)) ?></span><span class="text-2xl font-medium">/mo</span>
                                </p>
                                <div class="mb-2">
                                    <span class="bg-green-500 text-white px-4 py-2 rounded-full text-base font-bold">
                                        Your first <?= esc($plan['trial_days']) ?> days is FREE!
                                    </span>
                                </div>
                            <?php else: ?>
                                <p class="text-6xl font-extrabold text-white mb-3">
                                    <span class="price" data-original-price="<?= esc($plan['price']) ?>" data-original-currency="USD">$<?= esc(number_format($plan['price'], 2)) ?></span><span class="text-2xl font-medium">/mo</span>
                                </p>
                                <?php if ($plan['price'] == 0): ?>
                                    <p class="text-slate-400 mt-2">Free forever</p>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if (!empty($plan['description'])): ?>
                                <p class="text-slate-400 text-center mb-4 mt-4"><?= esc($plan['description']) ?></p>
                            <?php endif; ?>
                        </div>
                        <ul class="text-slate-300 space-y-4 mb-8">
                            <?php if (empty($plan['features'])): ?>
                                <li class="flex items-center"><i class="ri-close-line text-red-400 mr-2"></i>No features listed for this plan.</li>
                            <?php else: ?>
                                <?php foreach ($plan['features'] as $feature): ?>
                                    <li class="flex items-center"><i class="ri-check-line text-green-400 mr-2"></i><?= esc($feature['name']) ?></li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <?php
                        $session = session();
                        $isLoggedInMerchant = $session->get('is_logged_in') && $session->get('user_type') === 'merchant';
                        $link = $isLoggedInMerchant 
                            ? site_url('auth/select-plan/' . $plan['id']) 
                            : site_url('signup/merchant?plan=' . $plan['id']);
                    ?>
                    <a href="<?= $link ?>" class="choose-plan-btn block w-full text-center bg-blue-600 text-white font-semibold py-3 rounded-lg hover:bg-blue-700 transition-colors duration-300 mt-4">Choose plan</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>

    <!-- Enterprise Plan Section -->
    <div class="max-w-4xl mx-auto mt-12">
        <div class="bg-gradient-to-r from-gray-800 to-gray-900 rounded-xl shadow-xl p-8 border border-gray-700">
            <div class="flex flex-col md:flex-row items-center gap-6">
                <!-- Icon -->
                <div class="flex-shrink-0">
                    <div class="w-20 h-20 bg-blue-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-building text-white text-3xl"></i>
                    </div>
                </div>

                <!-- Content -->
                <div class="flex-1 text-center md:text-left">
                    <h3 class="text-2xl font-bold text-white mb-2">
                        Enterprise Plan
                        <span class="text-blue-400 text-lg font-normal ml-2">(Custom for Large Organisations)</span>
                    </h3>
                    <p class="text-slate-300 mb-4">
                        The Enterprise Plan is built for large service providers managing multiple branches and needing wider regional reach. It offers advanced listing features, centralised management, and premium visibility across the platform.
                    </p>
                    <p class="text-slate-400 text-sm mb-4">
                        <i class="fas fa-info-circle text-blue-400 mr-2"></i>
                        For organisations with more than 8 branches: Please contact us for a customised price plan tailored to your network size and operational needs.
                    </p>

                    <!-- Contact Info -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-center md:justify-start">
                        <a href="mailto:support@truckersafrica.com" class="inline-flex items-center justify-center px-5 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-envelope mr-2"></i>
                            support@truckersafrica.com
                        </a>
                        <a href="https://wa.me/27687781223" target="_blank" class="inline-flex items-center justify-center px-5 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition-colors">
                            <i class="fab fa-whatsapp mr-2"></i>
                            +27 68 778 1223
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Notice Modal -->
<div x-data="{
    showModal: false,
    init() {
        window.addEventListener('show-payment-modal', () => {
            this.showModal = true;
        });
    }
}"
     x-show="showModal"
     id="payment-modal"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;"
     @keydown.escape.window="showModal = false">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="showModal = false"></div>

    <!-- Modal Content -->
    <div class="flex items-center justify-center min-h-screen px-4">
        <div x-show="showModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="transform opacity-0 scale-95"
             x-transition:enter-end="transform opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="transform opacity-100 scale-100"
             x-transition:leave-end="transform opacity-0 scale-95"
             class="bg-white rounded-lg shadow-xl max-w-md w-full p-6 relative"
             @click.away="showModal = false">

            <!-- Close button -->
            <button @click="showModal = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>

            <!-- Icon -->
            <div class="flex justify-center mb-4">
                <div class="bg-blue-100 rounded-full p-3">
                    <i class="fas fa-info-circle text-blue-600 text-3xl"></i>
                </div>
            </div>

            <!-- Content -->
            <h3 class="text-xl font-bold text-gray-900 text-center mb-3">Payment Information</h3>
            <p class="text-gray-600 text-center mb-6">
                You will be redirected to PayFast and you will be billed in Rands (ZAR).
            </p>

            <!-- Actions -->
            <div class="flex gap-3">
                <button @click="showModal = false" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button @click="proceedToPayment()" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Proceed
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Currency conversion functionality
let currentDisplayCurrency = 'USD';
let exchangeRates = {};

// Load currency preference from localStorage
function loadCurrencyPreference() {
    const savedCurrency = localStorage.getItem('display_currency');
    if (savedCurrency) {
        currentDisplayCurrency = savedCurrency;
        document.getElementById('current-currency').textContent = savedCurrency;
    }
    convertAllPrices();
}

// Change currency and convert all prices
function changeCurrency(currency, symbol) {
    currentDisplayCurrency = currency;
    document.getElementById('current-currency').textContent = currency;
    localStorage.setItem('display_currency', currency);
    convertAllPrices();
}

// Convert all prices on the page
async function convertAllPrices() {
    // Fetch exchange rates if needed
    if (Object.keys(exchangeRates).length === 0) {
        await fetchExchangeRates();
    }

    // Convert all price elements
    const priceElements = document.querySelectorAll('.price');
    priceElements.forEach(element => {
        const originalPrice = parseFloat(element.dataset.originalPrice);
        const originalCurrency = element.dataset.originalCurrency || 'ZAR';

        if (originalPrice !== null && originalCurrency !== currentDisplayCurrency) {
            const convertedPrice = convertPrice(originalPrice, originalCurrency, currentDisplayCurrency);
            if (convertedPrice !== null) {
                const symbol = getCurrencySymbol(currentDisplayCurrency);
                element.innerHTML = `${symbol}${convertedPrice.toFixed(2)}`;
            }
        } else if (originalPrice !== null) {
            const symbol = getCurrencySymbol(originalCurrency);
            element.innerHTML = `${symbol}${originalPrice.toFixed(2)}`;
        }
    });
}

// Fetch exchange rates from API
async function fetchExchangeRates() {
    try {
        const response = await fetch(`https://api.exchangerate-api.com/v4/latest/ZAR`);
        const data = await response.json();
        exchangeRates = data.rates;
        // Add ZAR to rates
        exchangeRates['ZAR'] = 1;
    } catch (error) {
        console.error('Failed to fetch exchange rates:', error);
    }
}

// Convert price between currencies
function convertPrice(amount, fromCurrency, toCurrency) {
    if (fromCurrency === toCurrency) return amount;

    // Convert through ZAR as base
    let amountInZAR = amount;
    if (fromCurrency !== 'ZAR') {
        const fromRate = exchangeRates[fromCurrency];
        if (!fromRate) return null;
        amountInZAR = amount / fromRate;
    }

    if (toCurrency === 'ZAR') return amountInZAR;

    const toRate = exchangeRates[toCurrency];
    if (!toRate) return null;

    return amountInZAR * toRate;
}

// Get currency symbol
function getCurrencySymbol(currency) {
    const symbols = {
        'ZAR': 'R',
        'USD': '$',
        'BWP': 'P',
        'NAD': 'N$',
        'ZMW': 'ZK',
        'KES': 'KSh',
        'TZS': 'TSh',
        'UGX': 'USh'
    };
    return symbols[currency] || currency;
}

// Modal state management
let selectedPlanLink = '';

function showPaymentModal(link) {
    selectedPlanLink = link;
    window.dispatchEvent(new CustomEvent('show-payment-modal'));
}

function proceedToPayment() {
    if (selectedPlanLink) {
        window.location.href = selectedPlanLink;
    }
}

// Initialize currency system
document.addEventListener('DOMContentLoaded', function() {
    loadCurrencyPreference();

    // Handle choose plan button clicks
    const choosePlanButtons = document.querySelectorAll('.choose-plan-btn');

    choosePlanButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            showPaymentModal(this.href);
        });
    });
});
</script>
