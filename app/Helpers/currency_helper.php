<?php

use App\Services\CurrencyService;

if (!function_exists('format_currency')) {
    /**
     * Format amount with currency symbol
     */
    function format_currency(float $amount, string $currencyCode): string
    {
        $currencyService = new CurrencyService();
        return $currencyService->formatAmount($amount, $currencyCode);
    }
}

if (!function_exists('convert_and_format_price')) {
    /**
     * Convert price from one currency to another and format it
     */
    function convert_and_format_price(
        float $amount, 
        string $fromCurrency, 
        string $toCurrency, 
        bool $showOriginal = true
    ): string {
        $currencyService = new CurrencyService();
        
        // If same currency, just format
        if ($fromCurrency === $toCurrency) {
            return format_currency($amount, $fromCurrency);
        }
        
        // Convert amount
        $convertedAmount = $currencyService->convertAmount($amount, $fromCurrency, $toCurrency);
        
        if ($convertedAmount === null) {
            // Conversion failed, show original with note
            return format_currency($amount, $fromCurrency) . ' <span class="text-xs text-gray-500">(conversion unavailable)</span>';
        }
        
        $convertedFormatted = format_currency($convertedAmount, $toCurrency);
        
        if (!$showOriginal) {
            return $convertedFormatted;
        }
        
        // Show both original and converted
        $originalFormatted = format_currency($amount, $fromCurrency);
        return $convertedFormatted . ' <span class="text-xs text-gray-500">(' . $originalFormatted . ')</span>';
    }
}

if (!function_exists('get_driver_currency_preference')) {
    /**
     * Get the current driver's currency preference
     */
    function get_driver_currency_preference(): string
    {
        $session = session();
        $userId = $session->get('user_id');
        $userType = $session->get('user_type');

        // Only get currency preference for logged-in drivers
        if (!$userId || $userType !== 'driver') {
            return 'ZAR'; // Default fallback
        }

        // Try to get from session cache first
        $cachedCurrency = $session->get('driver_preferred_currency');
        if ($cachedCurrency) {
            return $cachedCurrency;
        }

        // Get from database
        $driverModel = new \App\Models\TruckDriverModel();
        $driver = $driverModel->find($userId);

        $currency = $driver['preferred_currency'] ?? 'ZAR';

        // Cache in session for this request
        $session->set('driver_preferred_currency', $currency);

        return $currency;
    }
}

if (!function_exists('display_listing_price')) {
    /**
     * Display listing price with currency symbol from database (no conversion)
     */
    function display_listing_price(array $listing): string
    {
        $priceNumeric = $listing['price_numeric'] ?? $listing['price'] ?? 0;
        $currencyCode = $listing['currency_code'] ?? 'ZAR';

        // Get currency symbol from listing if available, otherwise fetch from database
        if (!empty($listing['currency_symbol'])) {
            $symbol = $listing['currency_symbol'];
        } else {
            $currencyService = new CurrencyService();
            $currencyInfo = $currencyService->getCurrencyInfo($currencyCode);
            $symbol = $currencyInfo['currency_symbol'] ?? 'R';
        }

        // Format and return price with symbol
        return $symbol . number_format((float) $priceNumeric, 2);
    }
}

if (!function_exists('get_exchange_rate_info')) {
    /**
     * Get exchange rate information for display
     */
    function get_exchange_rate_info(string $fromCurrency, string $toCurrency): ?array
    {
        if ($fromCurrency === $toCurrency) {
            return null;
        }
        
        $currencyService = new CurrencyService();
        $rate = $currencyService->getExchangeRate($fromCurrency, $toCurrency);
        
        if ($rate === null) {
            return null;
        }
        
        return [
            'rate' => $rate,
            'formatted' => '1 ' . $fromCurrency . ' = ' . number_format($rate, 4) . ' ' . $toCurrency,
            'last_updated' => date('Y-m-d H:i')
        ];
    }
}

if (!function_exists('currency_dropdown_options')) {
    /**
     * Get currency dropdown options for forms
     */
    function currency_dropdown_options(int $priority = 1): array
    {
        $currencyModel = new \App\Models\CurrencyModel();
        return $currencyModel->getDropdownOptions($priority);
    }
}

if (!function_exists('detect_currency_from_country')) {
    /**
     * Detect currency from country code
     */
    function detect_currency_from_country(string $countryCode): string
    {
        $currencyService = new CurrencyService();
        return $currencyService->getCurrencyByCountry($countryCode);
    }
}

if (!function_exists('format_order_total_with_breakdown')) {
    /**
     * Format order total with currency breakdown
     */
    function format_order_total_with_breakdown(array $order, array $orderItems = []): string
    {
        $driverCurrency = $order['currency_code'] ?? get_driver_currency_preference();
        $grandTotal = $order['grand_total'] ?? 0;
        
        // If no breakdown needed (single currency order)
        if (empty($orderItems)) {
            return format_currency($grandTotal, $driverCurrency);
        }
        
        // Group items by original currency
        $currencyTotals = [];
        foreach ($orderItems as $item) {
            $itemCurrency = $item['currency_code'] ?? 'ZAR';
            $itemTotal = ($item['price'] ?? 0) * ($item['quantity'] ?? 1);
            
            if (!isset($currencyTotals[$itemCurrency])) {
                $currencyTotals[$itemCurrency] = 0;
            }
            $currencyTotals[$itemCurrency] += $itemTotal;
        }
        
        // If only one currency, show simple total
        if (count($currencyTotals) <= 1) {
            return format_currency($grandTotal, $driverCurrency);
        }
        
        // Show breakdown for multiple currencies
        $breakdown = [];
        foreach ($currencyTotals as $currency => $total) {
            $breakdown[] = format_currency($total, $currency);
        }
        
        $result = format_currency($grandTotal, $driverCurrency);
        $result .= ' <span class="text-xs text-gray-500">(' . implode(' + ', $breakdown) . ')</span>';
        
        return $result;
    }
}
