<?php
/**
 * Currency Exchange Rate Update Cron Script
 * This script updates exchange rates from fawazahmed0 API
 * Can be called via web request or command line
 */

// Security check - only allow execution from command line or specific IP
$allowedIPs = ['127.0.0.1', '::1']; // Add your server's IP if needed
$isCommandLine = php_sapi_name() === 'cli';
$isAllowedIP = in_array($_SERVER['REMOTE_ADDR'] ?? '', $allowedIPs);

if (!$isCommandLine && !$isAllowedIP) {
    http_response_code(403);
    die('Access denied');
}

// Set up CodeIgniter environment
define('FCPATH', realpath(dirname(__FILE__) . '/../') . DIRECTORY_SEPARATOR);
require_once FCPATH . '../app/Config/Paths.php';

$paths = new Config\Paths();
require_once rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';

// Load environment settings
require_once SYSTEMPATH . 'Config/DotEnv.php';
(new CodeIgniter\Config\DotEnv(ROOTPATH))->load();

// Define environment
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', env('CI_ENVIRONMENT', 'production'));
}

// Bootstrap the application
$app = Config\Services::codeigniter();
$app->initialize();

// Import required classes
use App\Services\CurrencyService;

// Log start time
$startTime = microtime(true);
$logFile = ROOTPATH . 'writable/logs/currency-updates.log';

function logMessage($message, $logFile) {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] {$message}" . PHP_EOL;
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    
    // Also output to console if running from command line
    if (php_sapi_name() === 'cli') {
        echo $logEntry;
    }
}

try {
    logMessage("Starting exchange rate update...", $logFile);
    
    $currencyService = new CurrencyService();
    $results = $currencyService->updateAllExchangeRates();
    
    $successful = 0;
    $failed = 0;
    $failedPairs = [];
    
    foreach ($results as $result) {
        if ($result['success']) {
            $successful++;
            logMessage("✓ {$result['from']} → {$result['to']}: {$result['rate']}", $logFile);
        } else {
            $failed++;
            $failedPairs[] = "{$result['from']} → {$result['to']}";
            logMessage("✗ Failed: {$result['from']} → {$result['to']}", $logFile);
        }
    }
    
    $endTime = microtime(true);
    $executionTime = round($endTime - $startTime, 2);
    
    logMessage("Exchange rate update completed in {$executionTime} seconds", $logFile);
    logMessage("Successful: {$successful}, Failed: {$failed}", $logFile);
    
    if ($failed > 0) {
        logMessage("Failed pairs: " . implode(', ', $failedPairs), $logFile);
    }
    
    // Return JSON response for web requests
    if (!php_sapi_name() === 'cli') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Exchange rates updated successfully',
            'stats' => [
                'successful' => $successful,
                'failed' => $failed,
                'execution_time' => $executionTime
            ]
        ]);
    }
    
} catch (\Exception $e) {
    $errorMessage = "Error updating exchange rates: " . $e->getMessage();
    logMessage($errorMessage, $logFile);
    
    // Return error response for web requests
    if (!php_sapi_name() === 'cli') {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $errorMessage
        ]);
    }
    
    exit(1);
}

exit(0);
?>
