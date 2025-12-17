-- Create merchant with ID 19
INSERT INTO `merchants` (
    `id`,
    `owner_name`,
    `email`,
    `password_hash`,
    `business_name`,
    `business_contact_number`,
    `business_whatsapp_number`,
    `physical_address`,
    `latitude`,
    `longitude`,
    `business_description`,
    `main_service`,
    `status`,
    `verification_status`,
    `is_visible`,
    `is_verified`,
    `onboarding_completed`,
    `business_type`,
    `default_currency`,
    `approval_notification_seen`,
    `current_locations_count`,
    `current_listings_count`,
    `created_at`,
    `updated_at`,
    `approved_at`
) VALUES (
    19,
    'Barrera Owner',
    'barrera@truckersafrica.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- Password: password
    'Barrera and Dorsey Trading',
    '27663827405',
    '27898234397',
    '4321 Liberty Street, Johannesburg, 2001',
    -25.93632309,
    28.01076595,
    'Full-service truck maintenance and repair facility offering mechanical services, parts supply, and roadside assistance.',
    'Mechanical Repairs',
    'approved',
    'approved',
    1,
    'verified',
    1,
    'business',
    'ZAR',
    1,
    1,
    0,
    NOW(),
    NOW(),
    NOW()
);

-- Create active subscription for merchant 19
INSERT INTO `subscriptions` (
    `merchant_id`,
    `plan_id`,
    `status`,
    `trial_ends_at`,
    `current_period_starts_at`,
    `current_period_ends_at`,
    `created_at`,
    `updated_at`
) VALUES (
    19,
    2, -- Growth plan (allows multiple locations)
    'active',
    DATE_ADD(NOW(), INTERVAL 14 DAY),
    NOW(),
    DATE_ADD(NOW(), INTERVAL 30 DAY),
    NOW(),
    NOW()
);

-- Verify the merchant was created
SELECT
    m.id,
    m.business_name,
    m.email,
    m.status,
    m.verification_status,
    m.is_visible,
    s.status as subscription_status
FROM merchants m
LEFT JOIN subscriptions s ON m.id = s.merchant_id
WHERE m.id = 19;

-- Verify the location is linked correctly
SELECT
    ml.id,
    ml.location_name,
    ml.merchant_id,
    m.business_name,
    ml.is_primary,
    ml.is_active
FROM merchant_locations ml
LEFT JOIN merchants m ON ml.merchant_id = m.id
WHERE ml.id = 6;
