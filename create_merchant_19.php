<?php
// Bootstrap CodeIgniter
require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Initialize CodeIgniter application paths
$pathsConfig = require __DIR__ . '/app/Config/Paths.php';
$paths = new Config\Paths();

// Bootstrap the framework
require __DIR__ . '/system/bootstrap.php';

$app = Config\Services::codeigniter();
$app->initialize();

$db = \Config\Database::connect();

// Create merchant with ID 19
$merchantData = [
    'id' => 19,
    'owner_name' => 'Barrera Owner',
    'email' => 'barrera@truckersafrica.com',
    'password_hash' => password_hash('Password123!', PASSWORD_DEFAULT),
    'business_name' => 'Barrera and Dorsey Trading',
    'business_contact_number' => '27663827405',
    'business_whatsapp_number' => '27898234397',
    'physical_address' => '4321 Liberty Street, Johannesburg, 2001',
    'latitude' => -25.93632309,
    'longitude' => 28.01076595,
    'business_description' => 'Full-service truck maintenance and repair facility offering mechanical services, parts supply, and roadside assistance.',
    'main_service' => 'Mechanical Repairs',
    'status' => 'approved',
    'verification_status' => 'approved',
    'is_visible' => 1,
    'is_verified' => 'verified',
    'onboarding_completed' => 1,
    'business_type' => 'business',
    'default_currency' => 'ZAR',
    'approval_notification_seen' => 1,
    'current_locations_count' => 1,
    'current_listings_count' => 0,
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
    'approved_at' => date('Y-m-d H:i:s')
];

// Insert merchant
$builder = $db->table('merchants');
$result = $builder->insert($merchantData);

if ($result) {
    echo "✓ Merchant created successfully (ID: 19)\n";
    echo "  Business: {$merchantData['business_name']}\n";
    echo "  Email: {$merchantData['email']}\n";
    echo "  Password: Password123!\n\n";

    // Now create subscription for this merchant
    $subscriptionData = [
        'merchant_id' => 19,
        'plan_id' => 2, // Growth plan (allows multiple locations)
        'status' => 'active',
        'trial_ends_at' => date('Y-m-d H:i:s', strtotime('+14 days')),
        'current_period_starts_at' => date('Y-m-d H:i:s'),
        'current_period_ends_at' => date('Y-m-d H:i:s', strtotime('+30 days')),
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];

    $subBuilder = $db->table('subscriptions');
    $subResult = $subBuilder->insert($subscriptionData);

    if ($subResult) {
        echo "✓ Active subscription created (Growth Plan)\n";
        echo "  Status: active\n";
        echo "  Trial ends: {$subscriptionData['trial_ends_at']}\n\n";

        echo "=== MERCHANT SETUP COMPLETE ===\n";
        echo "The merchant location 'Barrera and Dorsey Trading' should now be visible on the driver dashboard.\n\n";
        echo "Login credentials:\n";
        echo "  Email: barrera@truckersafrica.com\n";
        echo "  Password: Password123!\n";
    } else {
        echo "✗ Failed to create subscription\n";
        echo "Error: " . $db->error()['message'] . "\n";
    }
} else {
    echo "✗ Failed to create merchant\n";
    echo "Error: " . $db->error()['message'] . "\n";
}
