<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\SubscriptionModel;
use App\Models\BranchUserModel;
use App\Models\MerchantLocationModel;

/**
 * Process expired subscriptions and deactivate branches
 *
 * This command should be run daily via cron to:
 * 1. Find cancelled subscriptions that have passed their end date
 * 2. Mark them as expired
 * 3. Deactivate all branch users and locations for those merchants
 *
 * Usage: php spark subscription:process-expired
 */
class ProcessExpiredSubscriptions extends BaseCommand
{
    protected $group       = 'Cron';
    protected $name        = 'subscription:process-expired';
    protected $description = 'Process expired subscriptions and deactivate branches';

    public function run(array $params)
    {
        CLI::write('Starting expired subscription processing...', 'yellow');
        CLI::newLine();

        $subscriptionModel = new SubscriptionModel();
        $branchUserModel = new BranchUserModel();
        $locationModel = new MerchantLocationModel();

        // Find and process cancelled subscriptions that have expired
        CLI::write('Checking for cancelled subscriptions that have reached their end date...', 'blue');
        
        $merchantIds = $subscriptionModel->processCancelledSubscriptions();

        if (empty($merchantIds)) {
            CLI::write('No expired subscriptions found.', 'green');
            CLI::newLine();
            return;
        }

        CLI::write('Found ' . count($merchantIds) . ' expired subscription(s). Processing...', 'yellow');
        CLI::newLine();

        $totalBranchesDeactivated = 0;
        $totalLocationsDeactivated = 0;

        foreach ($merchantIds as $merchantId) {
            CLI::write("Processing merchant ID: {$merchantId}", 'cyan');

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

            CLI::write("  ✓ Deactivated {$branchesDeactivated} branch user(s)", 'green');
            CLI::write("  ✓ Deactivated {$locationsDeactivated} location(s)", 'green');
            CLI::newLine();

            log_message('info', "Expired subscription for merchant {$merchantId}: Deactivated {$branchesDeactivated} branches and {$locationsDeactivated} locations");
        }

        // Summary
        CLI::write('═══════════════════════════════════════', 'white');
        CLI::write('SUMMARY', 'white');
        CLI::write('═══════════════════════════════════════', 'white');
        CLI::write('Merchants processed: ' . count($merchantIds), 'yellow');
        CLI::write('Total branches deactivated: ' . $totalBranchesDeactivated, 'yellow');
        CLI::write('Total locations deactivated: ' . $totalLocationsDeactivated, 'yellow');
        CLI::write('═══════════════════════════════════════', 'white');
        CLI::newLine();

        CLI::write('Expired subscription processing completed successfully!', 'green');
    }
}

