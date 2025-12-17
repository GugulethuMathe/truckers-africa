<?php

/**
 * Cron endpoint for processing expired subscriptions
 * 
 * This file can be called via HTTP by cron jobs when direct CLI access is not available
 * URL: https://www.truckersafrica.com/cron/process-expired-subscriptions.php
 * 
 * Security: Uses a secret token to prevent unauthorized access
 */

// Prevent direct browser access without token
$cronSecret = 'YOUR_SECRET_TOKEN_HERE'; // Change this to a random string
$providedToken = $_GET['token'] ?? '';

if ($providedToken !== $cronSecret) {
    http_response_code(403);
    die('Access denied. Invalid token.');
}

// Set execution time limit
set_time_limit(300); // 5 minutes

// Load CodeIgniter
require_once __DIR__ . '/../../vendor/autoload.php';

// Bootstrap CodeIgniter
$app = require_once __DIR__ . '/../../app/Config/Paths.php';
$paths = new Config\Paths();
require_once SYSTEMPATH . 'bootstrap.php';

// Get the application instance
$app = Config\Services::codeigniter();
$app->initialize();

// Load required models
$subscriptionModel = new \App\Models\SubscriptionModel();
$branchUserModel = new \App\Models\BranchUserModel();
$locationModel = new \App\Models\MerchantLocationModel();

// Start output
echo "Starting expired subscription processing...\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

// Find and process cancelled subscriptions that have expired
echo "Checking for cancelled subscriptions that have reached their end date...\n";

$merchantIds = $subscriptionModel->processCancelledSubscriptions();

if (empty($merchantIds)) {
    echo "No expired subscriptions found.\n";
    exit(0);
}

echo "Found " . count($merchantIds) . " expired subscription(s). Processing...\n\n";

$totalBranchesDeactivated = 0;
$totalLocationsDeactivated = 0;

foreach ($merchantIds as $merchantId) {
    echo "Processing merchant ID: {$merchantId}\n";

    // Deactivate all branch users for this merchant
    $branchesDeactivated = $branchUserModel->where('merchant_id', $merchantId)
                                           ->where('is_active', 1)
                                           ->countAllResults();
    
    $branchUserModel->deactivateAllForMerchant($merchantId);
    
    // Deactivate all non-primary locations
    $locationsDeactivated = $locationModel->where('merchant_id', $merchantId)
                                          ->where('is_primary', 0)
                                          ->where('is_active', 1)
                                          ->countAllResults();
    
    $locationModel->where('merchant_id', $merchantId)
                 ->where('is_primary', 0)
                 ->set(['is_active' => 0])
                 ->update();

    $totalBranchesDeactivated += $branchesDeactivated;
    $totalLocationsDeactivated += $locationsDeactivated;

    echo "  ✓ Deactivated {$branchesDeactivated} branch user(s)\n";
    echo "  ✓ Deactivated {$locationsDeactivated} location(s)\n\n";

    log_message('info', "Expired subscription for merchant {$merchantId}: Deactivated {$branchesDeactivated} branches and {$locationsDeactivated} locations");
}

// Summary
echo "═══════════════════════════════════════\n";
echo "SUMMARY\n";
echo "═══════════════════════════════════════\n";
echo "Merchants processed: " . count($merchantIds) . "\n";
echo "Total branches deactivated: " . $totalBranchesDeactivated . "\n";
echo "Total locations deactivated: " . $totalLocationsDeactivated . "\n";
echo "═══════════════════════════════════════\n\n";

echo "Expired subscription processing completed successfully!\n";
echo "Completed at: " . date('Y-m-d H:i:s') . "\n";

