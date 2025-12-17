<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Services\CurrencyService;

class CronController extends Controller
{
    /**
     * Update currency exchange rates
     * Can be called via web request for cron jobs
     */
    public function updateCurrencyRates()
    {
        // Security check - allow with secret key or from server
        $secretKey = getenv('CRON_SECRET_KEY') ?: 'truckers-africa-cron-2024';
        $providedKey = $this->request->getGet('key');
        $userAgent = $this->request->getUserAgent();

        // Allow if:
        // 1. Correct secret key provided, OR
        // 2. Called by wget/curl (cron job), OR
        // 3. From localhost
        $isAuthorized = (
            $providedKey === $secretKey ||
            strpos($userAgent, 'wget') !== false ||
            strpos($userAgent, 'curl') !== false ||
            in_array($this->request->getIPAddress(), ['127.0.0.1', '::1'])
        );

        if (!$isAuthorized) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Access denied. Use ?key=truckers-africa-cron-2024 or call via wget/curl'
            ]);
        }
        
        $startTime = microtime(true);
        
        try {
            $currencyService = new CurrencyService();
            $results = $currencyService->updateAllExchangeRates();
            
            $successful = 0;
            $failed = 0;
            $failedPairs = [];
            $successfulPairs = [];
            
            foreach ($results as $result) {
                if ($result['success']) {
                    $successful++;
                    $successfulPairs[] = "{$result['from']} → {$result['to']}: {$result['rate']}";
                } else {
                    $failed++;
                    $failedPairs[] = "{$result['from']} → {$result['to']}";
                }
            }
            
            $endTime = microtime(true);
            $executionTime = round($endTime - $startTime, 2);
            
            // Log the update
            log_message('info', "Currency rates updated: {$successful} successful, {$failed} failed, {$executionTime}s");
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Exchange rates updated successfully',
                'stats' => [
                    'successful' => $successful,
                    'failed' => $failed,
                    'execution_time' => $executionTime,
                    'total_pairs' => count($results)
                ],
                'details' => [
                    'successful_pairs' => array_slice($successfulPairs, 0, 10), // Show first 10
                    'failed_pairs' => $failedPairs
                ]
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Currency rate update failed: ' . $e->getMessage());
            
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error updating exchange rates: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Test endpoint to verify cron system is working
     */
    public function test()
    {
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Cron system is working',
            'timestamp' => date('Y-m-d H:i:s'),
            'server_time' => time()
        ]);
    }
    
    /**
     * Public test endpoint for currency updates (no security check)
     * Remove this after testing is complete
     */
    public function publicTestUpdate()
    {
        $startTime = microtime(true);

        try {
            $currencyService = new CurrencyService();
            $results = $currencyService->updateAllExchangeRates();

            $successful = 0;
            $failed = 0;
            $failedPairs = [];
            $successfulPairs = [];

            foreach ($results as $result) {
                if ($result['success']) {
                    $successful++;
                    $successfulPairs[] = "{$result['from']} → {$result['to']}: {$result['rate']}";
                } else {
                    $failed++;
                    $failedPairs[] = "{$result['from']} → {$result['to']}";
                }
            }

            $endTime = microtime(true);
            $executionTime = round($endTime - $startTime, 2);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'PUBLIC TEST: Exchange rates updated successfully',
                'stats' => [
                    'successful' => $successful,
                    'failed' => $failed,
                    'execution_time' => $executionTime,
                    'total_pairs' => count($results)
                ],
                'sample_rates' => array_slice($successfulPairs, 0, 5),
                'failed_pairs' => $failedPairs,
                'note' => 'This is a public test endpoint. Remove after testing.'
            ]);

        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error in public test: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Get currency system status
     */
    public function currencyStatus()
    {
        try {
            $db = \Config\Database::connect();

            // Count supported currencies
            $currencyCount = $db->table('supported_currencies')
                ->where('is_active', 1)
                ->countAllResults();

            // Count today's exchange rates
            $rateCount = $db->table('currency_exchange_rates')
                ->where('rate_date', date('Y-m-d'))
                ->countAllResults();

            // Get latest rate update
            $latestRate = $db->table('currency_exchange_rates')
                ->orderBy('created_at', 'DESC')
                ->limit(1)
                ->get()
                ->getRowArray();

            return $this->response->setJSON([
                'success' => true,
                'status' => [
                    'supported_currencies' => $currencyCount,
                    'todays_rates' => $rateCount,
                    'latest_update' => $latestRate ? $latestRate['created_at'] : 'Never',
                    'system_ready' => $currencyCount > 0 && $rateCount > 0
                ]
            ]);

        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error checking currency status: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Process expired subscriptions
     * Should be run daily to check for expired trials and past-due subscriptions
     */
    public function processExpiredSubscriptions()
    {
        // Security check - same as currency update
        $secretKey = getenv('CRON_SECRET_KEY') ?: 'truckers-africa-cron-2024';
        $providedKey = $this->request->getGet('key');
        $userAgent = $this->request->getUserAgent();

        $isAuthorized = (
            $providedKey === $secretKey ||
            strpos($userAgent, 'wget') !== false ||
            strpos($userAgent, 'curl') !== false ||
            in_array($this->request->getIPAddress(), ['127.0.0.1', '::1'])
        );

        if (!$isAuthorized) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Access denied. Use ?key=truckers-africa-cron-2024 or call via wget/curl'
            ]);
        }

        $startTime = microtime(true);

        try {
            $subscriptionModel = new \App\Models\SubscriptionModel();
            $results = $subscriptionModel->processExpiredSubscriptions();

            $endTime = microtime(true);
            $executionTime = round($endTime - $startTime, 2);

            // Log the update
            log_message('info', "Expired subscriptions processed: {$results['expired']} trials expired, {$results['past_due']} marked past due, {$executionTime}s");

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Expired subscriptions processed successfully',
                'stats' => [
                    'trials_expired' => $results['expired'],
                    'marked_past_due' => $results['past_due'],
                    'total_processed' => $results['expired'] + $results['past_due'],
                    'execution_time' => $executionTime
                ],
                'timestamp' => date('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Expired subscription processing failed: ' . $e->getMessage());

            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error processing expired subscriptions: ' . $e->getMessage()
            ]);
        }
    }
}
