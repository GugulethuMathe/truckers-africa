<?php

namespace App\Services;

use CodeIgniter\Database\ConnectionInterface;

class CurrencyService
{
    protected $db;
    protected $cache;
    
    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->cache = \Config\Services::cache();
    }
    
    /**
     * Get exchange rate between two currencies
     */
    public function getExchangeRate(string $fromCurrency, string $toCurrency): ?float
    {
        // Same currency, rate is 1
        if ($fromCurrency === $toCurrency) {
            return 1.0;
        }
        
        // Check cache first
        $cacheKey = "exchange_rate_{$fromCurrency}_{$toCurrency}_" . date('Y-m-d');
        $cachedRate = $this->cache->get($cacheKey);
        
        if ($cachedRate !== null) {
            return (float) $cachedRate;
        }
        
        // Check database for today's rate
        $rate = $this->getStoredRate($fromCurrency, $toCurrency);
        
        if ($rate) {
            // Cache for 1 hour
            $this->cache->save($cacheKey, $rate, 3600);
            return $rate;
        }
        
        // Fetch from API and store
        $rate = $this->fetchAndStoreRate($fromCurrency, $toCurrency);
        
        if ($rate) {
            $this->cache->save($cacheKey, $rate, 3600);
            return $rate;
        }
        
        return null;
    }
    
    /**
     * Convert amount from one currency to another
     */
    public function convertAmount(float $amount, string $fromCurrency, string $toCurrency): ?float
    {
        $rate = $this->getExchangeRate($fromCurrency, $toCurrency);
        
        if ($rate === null) {
            return null;
        }
        
        return round($amount * $rate, 2);
    }
    
    /**
     * Get stored exchange rate from database
     */
    protected function getStoredRate(string $fromCurrency, string $toCurrency): ?float
    {
        $query = $this->db->table('currency_exchange_rates')
            ->where('from_currency', $fromCurrency)
            ->where('to_currency', $toCurrency)
            ->where('rate_date', date('Y-m-d'))
            ->where('is_active', 1)
            ->get();
            
        $result = $query->getRow();
        
        return $result ? (float) $result->exchange_rate : null;
    }
    
    /**
     * Fetch rate using ExchangeRate-API.com (free, reliable, no API key needed)
     */
    protected function fetchAndStoreRate(string $fromCurrency, string $toCurrency): ?float
    {
        try {
            $fromCurrency = strtoupper($fromCurrency);
            $toCurrency = strtoupper($toCurrency);

            // Use ExchangeRate-API.com - free tier allows 1500 requests/month
            $url = "https://api.exchangerate-api.com/v4/latest/{$fromCurrency}";

            $response = $this->fetchWithCurl($url);

            if ($response === false) {
                log_message('error', "Failed to fetch exchange rates for base currency: {$fromCurrency}");
                return null;
            }

            $data = json_decode($response, true);

            if (!isset($data['rates'][$toCurrency])) {
                log_message('error', "Currency {$toCurrency} not found in rates for base {$fromCurrency}");
                return null;
            }

            $rate = (float) $data['rates'][$toCurrency];

            // Store in database
            $this->storeExchangeRate($fromCurrency, $toCurrency, $rate);

            log_message('info', "Successfully fetched rate: {$fromCurrency} → {$toCurrency} = {$rate}");

            return $rate;

        } catch (\Exception $e) {
            log_message('error', 'Exchange rate API error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Fetch URL with cURL for better error handling
     */
    protected function fetchWithCurl(string $url): string|false
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, 'TruckersAfrica/1.0');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            log_message('error', "cURL failed for {$url}: HTTP {$httpCode}, Error: {$error}");
            return false;
        }

        return $response;
    }
    
    /**
     * Store exchange rate in database
     */
    protected function storeExchangeRate(string $fromCurrency, string $toCurrency, float $rate): void
    {
        $data = [
            'from_currency' => $fromCurrency,
            'to_currency' => $toCurrency,
            'exchange_rate' => $rate,
            'rate_date' => date('Y-m-d'),
            'source' => 'fawazahmed0_api'
        ];
        
        // Use replace to handle duplicates
        $this->db->table('currency_exchange_rates')->replace($data);
    }
    
    /**
     * Get all supported currencies
     */
    public function getSupportedCurrencies(int $priority = null): array
    {
        $builder = $this->db->table('supported_currencies')
            ->where('is_active', 1);
            
        if ($priority !== null) {
            $builder->where('priority <=', $priority);
        }
        
        return $builder->orderBy('priority', 'ASC')
            ->orderBy('currency_name', 'ASC')
            ->get()
            ->getResultArray();
    }
    
    /**
     * Get currency information
     */
    public function getCurrencyInfo(string $currencyCode): ?array
    {
        $result = $this->db->table('supported_currencies')
            ->where('currency_code', $currencyCode)
            ->where('is_active', 1)
            ->get()
            ->getRowArray();
            
        return $result ?: null;
    }
    
    /**
     * Format amount with currency symbol
     */
    public function formatAmount(float $amount, string $currencyCode): string
    {
        $currencyInfo = $this->getCurrencyInfo($currencyCode);
        
        if (!$currencyInfo) {
            return number_format($amount, 2) . ' ' . $currencyCode;
        }
        
        $formattedAmount = number_format($amount, $currencyInfo['decimal_places']);
        $format = $currencyInfo['display_format'];
        
        return str_replace('{amount}', $formattedAmount, $format);
    }
    
    /**
     * Detect currency from country code
     */
    public function getCurrencyByCountry(string $countryCode): string
    {
        $currencies = $this->getSupportedCurrencies();
        
        foreach ($currencies as $currency) {
            $countryCodes = json_decode($currency['country_codes'], true);
            if ($countryCodes && in_array(strtoupper($countryCode), $countryCodes)) {
                return $currency['currency_code'];
            }
        }
        
        return 'ZAR'; // Default fallback
    }
    
    /**
     * Update all exchange rates for high priority currencies
     */
    public function updateAllExchangeRates(): array
    {
        $results = [];
        $highPriorityCurrencies = $this->getSupportedCurrencies(1); // High priority only
        $currencyCodes = array_column($highPriorityCurrencies, 'currency_code');

        // Fetch rates for each base currency (more efficient than individual calls)
        foreach ($currencyCodes as $baseCurrency) {
            $allRatesForBase = $this->fetchAllRatesForBaseCurrency($baseCurrency);

            if ($allRatesForBase) {
                // Store rates for all target currencies
                foreach ($currencyCodes as $targetCurrency) {
                    if ($baseCurrency !== $targetCurrency && isset($allRatesForBase[$targetCurrency])) {
                        $rate = $allRatesForBase[$targetCurrency];
                        $this->storeExchangeRate($baseCurrency, $targetCurrency, $rate);

                        $results[] = [
                            'from' => $baseCurrency,
                            'to' => $targetCurrency,
                            'rate' => $rate,
                            'success' => true
                        ];
                    } elseif ($baseCurrency !== $targetCurrency) {
                        $results[] = [
                            'from' => $baseCurrency,
                            'to' => $targetCurrency,
                            'rate' => null,
                            'success' => false
                        ];
                    }
                }
            } else {
                // Mark all pairs with this base as failed
                foreach ($currencyCodes as $targetCurrency) {
                    if ($baseCurrency !== $targetCurrency) {
                        $results[] = [
                            'from' => $baseCurrency,
                            'to' => $targetCurrency,
                            'rate' => null,
                            'success' => false
                        ];
                    }
                }
            }
        }

        // If all API calls failed, use fallback rates
        $successCount = count(array_filter($results, fn($r) => $r['success']));
        if ($successCount === 0) {
            log_message('info', 'All API calls failed, using fallback exchange rates');
            $this->insertFallbackRates();

            // Mark some results as successful with fallback
            foreach ($results as &$result) {
                if ($this->hasFallbackRate($result['from'], $result['to'])) {
                    $result['success'] = true;
                    $result['rate'] = $this->getFallbackRate($result['from'], $result['to']);
                }
            }
        }

        return $results;
    }

    /**
     * Fetch all exchange rates for a base currency using ExchangeRate-API
     */
    protected function fetchAllRatesForBaseCurrency(string $baseCurrency): ?array
    {
        try {
            $url = "https://api.exchangerate-api.com/v4/latest/{$baseCurrency}";
            $response = $this->fetchWithCurl($url);

            if ($response === false) {
                log_message('error', "Failed to fetch rates for base currency: {$baseCurrency}");
                return null;
            }

            $data = json_decode($response, true);

            if (!isset($data['rates']) || !is_array($data['rates'])) {
                log_message('error', "Invalid response format for base currency: {$baseCurrency}");
                return null;
            }

            log_message('info', "Successfully fetched " . count($data['rates']) . " rates for base currency: {$baseCurrency}");

            return $data['rates'];

        } catch (\Exception $e) {
            log_message('error', "Error fetching rates for {$baseCurrency}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Insert fallback exchange rates when API fails
     */
    protected function insertFallbackRates(): void
    {
        $fallbackRates = [
            // USD as base
            ['USD', 'ZAR', 18.50],
            ['USD', 'BWP', 13.45],
            ['USD', 'NAD', 18.50],
            ['USD', 'ZMW', 24.50],
            ['USD', 'KES', 150.00],
            ['USD', 'TZS', 2500.00],
            ['USD', 'UGX', 3700.00],
            ['USD', 'MZN', 63.50],
            ['USD', 'LSL', 18.50],
            ['USD', 'SZL', 18.50],
            ['USD', 'AOA', 900.00],
            ['USD', 'MWK', 1100.00],
            ['USD', 'NGN', 775.00],
            ['USD', 'GHS', 12.00],
            ['USD', 'ETB', 56.00],
            ['USD', 'RWF', 1200.00],

            // Reverse rates
            ['ZAR', 'USD', 0.054],
            ['BWP', 'USD', 0.074],
            ['NAD', 'USD', 0.054],
            ['ZMW', 'USD', 0.041],
            ['KES', 'USD', 0.0067],
            ['TZS', 'USD', 0.0004],
            ['UGX', 'USD', 0.00027],
            ['MZN', 'USD', 0.016],
            ['LSL', 'USD', 0.054],
            ['SZL', 'USD', 0.054],
            ['AOA', 'USD', 0.0011],
            ['MWK', 'USD', 0.00091],
            ['NGN', 'USD', 0.0013],
            ['GHS', 'USD', 0.083],
            ['ETB', 'USD', 0.018],
            ['RWF', 'USD', 0.00083],

            // Cross rates
            ['ZAR', 'BWP', 0.73],
            ['BWP', 'ZAR', 1.37],
            ['ZAR', 'NAD', 1.00],
            ['NAD', 'ZAR', 1.00],
            ['ZAR', 'LSL', 1.00],
            ['LSL', 'ZAR', 1.00],
            ['ZAR', 'SZL', 1.00],
            ['SZL', 'ZAR', 1.00],
            ['ZAR', 'MZN', 3.43],
            ['MZN', 'ZAR', 0.29],
        ];

        foreach ($fallbackRates as [$from, $to, $rate]) {
            $this->storeExchangeRate($from, $to, $rate);
        }
    }

    /**
     * Check if fallback rate exists
     */
    protected function hasFallbackRate(string $from, string $to): bool
    {
        $fallbackPairs = [
            'USD-ZAR', 'USD-BWP', 'USD-NAD', 'USD-ZMW', 'USD-KES', 'USD-TZS', 'USD-UGX',
            'USD-MZN', 'USD-LSL', 'USD-SZL', 'USD-AOA', 'USD-MWK', 'USD-NGN', 'USD-GHS', 'USD-ETB', 'USD-RWF',
            'ZAR-USD', 'BWP-USD', 'NAD-USD', 'ZMW-USD', 'KES-USD', 'TZS-USD', 'UGX-USD',
            'MZN-USD', 'LSL-USD', 'SZL-USD', 'AOA-USD', 'MWK-USD', 'NGN-USD', 'GHS-USD', 'ETB-USD', 'RWF-USD',
            'ZAR-BWP', 'BWP-ZAR', 'ZAR-NAD', 'NAD-ZAR', 'ZAR-LSL', 'LSL-ZAR', 'ZAR-SZL', 'SZL-ZAR', 'ZAR-MZN', 'MZN-ZAR'
        ];

        return in_array("{$from}-{$to}", $fallbackPairs);
    }

    /**
     * Get fallback rate
     */
    protected function getFallbackRate(string $from, string $to): ?float
    {
        $fallbackRates = [
            'USD-ZAR' => 18.50, 'USD-BWP' => 13.45, 'USD-NAD' => 18.50, 'USD-ZMW' => 24.50,
            'USD-KES' => 150.00, 'USD-TZS' => 2500.00, 'USD-UGX' => 3700.00,
            'USD-MZN' => 63.50, 'USD-LSL' => 18.50, 'USD-SZL' => 18.50, 'USD-AOA' => 900.00,
            'USD-MWK' => 1100.00, 'USD-NGN' => 775.00, 'USD-GHS' => 12.00, 'USD-ETB' => 56.00, 'USD-RWF' => 1200.00,
            'ZAR-USD' => 0.054, 'BWP-USD' => 0.074, 'NAD-USD' => 0.054, 'ZMW-USD' => 0.041,
            'KES-USD' => 0.0067, 'TZS-USD' => 0.0004, 'UGX-USD' => 0.00027,
            'MZN-USD' => 0.016, 'LSL-USD' => 0.054, 'SZL-USD' => 0.054, 'AOA-USD' => 0.0011,
            'MWK-USD' => 0.00091, 'NGN-USD' => 0.0013, 'GHS-USD' => 0.083, 'ETB-USD' => 0.018, 'RWF-USD' => 0.00083,
            'ZAR-BWP' => 0.73, 'BWP-ZAR' => 1.37, 'ZAR-NAD' => 1.00, 'NAD-ZAR' => 1.00,
            'ZAR-LSL' => 1.00, 'LSL-ZAR' => 1.00, 'ZAR-SZL' => 1.00, 'SZL-ZAR' => 1.00,
            'ZAR-MZN' => 3.43, 'MZN-ZAR' => 0.29
        ];

        return $fallbackRates["{$from}-{$to}"] ?? null;
    }
}
