# Quick Migration Guide - Trial Pending Status

## What Changed?

Added `'trial_pending'` status to prevent merchants from accessing trial features before providing payment method.

## Status Flow Summary

### Before:
```
Trial Plan: Select Plan → 'trial' (immediate access) → Payment Page
Problem: Access granted before payment method captured ❌
```

### After:
```
Trial Plan: Select Plan → 'trial_pending' → Payment Page (R0.00) → 'trial' (access granted) ✅
Paid Plan: Select Plan → 'new' → Payment Page (full price) → 'active' ✅
```

## Files Changed

1. ✅ `truckers_africa_database.sql` - Schema updated
2. ✅ `app/Models/SubscriptionModel.php` - Validation and queries updated
3. ✅ `app/Controllers/Onboarding.php` - Status set to 'trial_pending' for trial plans
4. ✅ `app/Controllers/Payment.php` - ITN handler updates 'trial_pending' → 'trial'
5. ✅ `app/Controllers/Subscription.php` - Dashboard messages for 'trial_pending'

## Migration Steps

### Step 1: Run SQL Migration

**Option A - Using phpMyAdmin:**
1. Open phpMyAdmin
2. Select `app_truckers_africa` database
3. Go to SQL tab
4. Paste and execute:
```sql
ALTER TABLE `subscriptions`
MODIFY COLUMN `status` enum('trial','active','past_due','cancelled','expired','new','trial_pending') NOT NULL;
```

**Option B - Using MySQL Command Line:**
```bash
mysql -h localhost -u root -D app_truckers_africa < add_trial_pending_status.sql
```

**Option C - Using PHP Script:**
Create `migrate.php` in root:
```php
<?php
require 'vendor/autoload.php';

$db = \Config\Database::connect();
$sql = "ALTER TABLE `subscriptions`
        MODIFY COLUMN `status` enum('trial','active','past_due','cancelled','expired','new','trial_pending') NOT NULL";

if ($db->query($sql)) {
    echo "✅ Migration successful!\n";
} else {
    echo "❌ Migration failed: " . $db->error() . "\n";
}
```

Then run: `php migrate.php`

### Step 2: Verify Migration

```sql
DESCRIBE subscriptions;
```

Look for `status` field - should show:
```
enum('trial','active','past_due','cancelled','expired','new','trial_pending')
```

### Step 3: Test the Flow

1. **Test Trial Plan:**
   - Sign up as new merchant
   - Select plan with trial
   - Check database: `SELECT status FROM subscriptions WHERE merchant_id = X;`
   - Should see: `trial_pending`
   - Complete R0.00 payment
   - Check database again
   - Should see: `trial`

2. **Test Paid Plan:**
   - Sign up as new merchant
   - Select plan without trial
   - Check database: Should see `new`
   - Complete payment
   - Check database: Should see `active`

## Rollback (if needed)

If you need to revert the changes:

```sql
-- Remove trial_pending from enum
ALTER TABLE `subscriptions`
MODIFY COLUMN `status` enum('trial','active','past_due','cancelled','expired','new') NOT NULL;

-- Update any trial_pending subscriptions to trial
UPDATE `subscriptions`
SET `status` = 'trial'
WHERE `status` = 'trial_pending';
```

Then revert code changes in git:
```bash
git checkout HEAD -- app/Controllers/Onboarding.php
git checkout HEAD -- app/Controllers/Payment.php
git checkout HEAD -- app/Controllers/Subscription.php
git checkout HEAD -- app/Models/SubscriptionModel.php
```

## Existing Subscriptions

✅ **No impact** - Existing subscriptions with status 'trial', 'active', 'new', etc. will continue working normally.

The new 'trial_pending' status only affects **new signups** going forward.

## Access Control Recommendation

Add this check to your subscription filter or middleware:

```php
public function hasActiveSubscription($merchantId): bool
{
    $subscription = $this->subscriptionModel->getCurrentSubscription($merchantId);

    if (!$subscription) {
        return false;
    }

    // Only allow access for trial and active statuses
    return in_array($subscription['status'], ['trial', 'active']);
}
```

Merchants with status `'trial_pending'` or `'new'` should be redirected to payment page.

## Questions?

See full documentation:
- `SUBSCRIPTION_STATUS_FLOW_COMPLETE.md` - Complete flow documentation
- `SUBSCRIPTION_NEW_STATUS_IMPLEMENTATION.md` - Original 'new' status docs

## Date: November 19, 2025
