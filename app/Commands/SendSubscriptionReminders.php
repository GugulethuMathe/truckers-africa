<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\SubscriptionModel;
use App\Models\MerchantModel;
use App\Models\PlanModel;
use App\Helpers\EmailService;

/**
 * Send subscription expiry reminders to merchants
 *
 * This command should be run daily via cron to send reminder emails
 * to merchants whose subscriptions are expiring in 7, 3, or 1 days.
 *
 * Usage: php spark subscription:reminders
 */
class SendSubscriptionReminders extends BaseCommand
{
    protected $group       = 'Cron';
    protected $name        = 'subscription:reminders';
    protected $description = 'Send subscription expiry reminders to merchants';

    public function run(array $params)
    {
        CLI::write('Starting subscription reminders...', 'yellow');

        $subscriptionModel = new SubscriptionModel();
        $merchantModel = new MerchantModel();
        $planModel = new PlanModel();
        $emailService = new EmailService();

        // Define reminder days (7, 3, and 1 days before expiry)
        $reminderDays = [7, 3, 1];
        $totalEmailsSent = 0;
        $totalErrors = 0;

        foreach ($reminderDays as $days) {
            CLI::write("\nChecking subscriptions expiring in {$days} days...", 'blue');

            // Calculate the target expiry date
            $expiryDate = date('Y-m-d', strtotime("+{$days} days"));

            // Find active subscriptions expiring on that date
            $subscriptions = $subscriptionModel
                ->select('subscriptions.*, merchants.email, merchants.business_name, plans.name as plan_name')
                ->join('merchants', 'merchants.id = subscriptions.merchant_id', 'left')
                ->join('plans', 'plans.id = subscriptions.plan_id', 'left')
                ->where('DATE(subscriptions.end_date)', $expiryDate)
                ->where('subscriptions.status', 'active')
                ->where('merchants.email IS NOT NULL')
                ->findAll();

            CLI::write("Found " . count($subscriptions) . " subscription(s) expiring on {$expiryDate}", 'white');

            foreach ($subscriptions as $subscription) {
                try {
                    $merchantData = [
                        'email' => $subscription['email'],
                        'business_name' => $subscription['business_name']
                    ];

                    $subscriptionData = [
                        'plan_name' => $subscription['plan_name'] ?? 'Subscription',
                        'end_date' => $subscription['end_date']
                    ];

                    // Send expiry reminder
                    $result = $emailService->sendSubscriptionExpiryReminder(
                        $merchantData,
                        $subscriptionData,
                        $days
                    );

                    if ($result) {
                        CLI::write("  ✓ Reminder sent to {$subscription['email']} ({$days} days)", 'green');
                        $totalEmailsSent++;

                        // Optional: Mark that reminder was sent (you can add a field to track this)
                        // Example: Update a last_reminder_sent field
                        // $subscriptionModel->update($subscription['id'], ['last_reminder_sent' => date('Y-m-d H:i:s')]);
                    } else {
                        CLI::write("  ✗ Failed to send reminder to {$subscription['email']}", 'red');
                        $totalErrors++;
                    }

                } catch (\Exception $e) {
                    CLI::write("  ✗ Error sending reminder to {$subscription['email']}: {$e->getMessage()}", 'red');
                    $totalErrors++;
                    log_message('error', 'Subscription reminder error: ' . $e->getMessage());
                }
            }
        }

        CLI::newLine();
        CLI::write('Subscription reminders completed!', 'green');
        CLI::write("Total emails sent: {$totalEmailsSent}", 'cyan');

        if ($totalErrors > 0) {
            CLI::write("Total errors: {$totalErrors}", 'red');
        }

        CLI::newLine();
    }
}
