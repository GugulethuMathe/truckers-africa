# Subscription "new" Status Implementation

## Overview
This document describes the implementation of the "new" subscription status for merchants who sign up for plans without trials. This ensures proper subscription lifecycle management where merchants must complete payment before their subscription becomes active.

## Changes Made

### 1. Database Schema Update
**File**: `truckers_africa_database.sql` (line 1395)
**File**: `add_new_subscription_status.sql` (migration script)

Added 'new' status to the subscriptions table enum field:
```sql
ALTER TABLE `subscriptions`
MODIFY COLUMN `status` enum('trial','active','past_due','cancelled','expired','new') NOT NULL;
```

**Status Values**:
- `trial` - Free trial period is active
- `active` - Subscription is paid and active
- `past_due` - Billing period expired, awaiting renewal
- `cancelled` - Subscription cancelled by user
- `expired` - Trial or subscription period ended
- **`new`** - Merchant selected plan without trial, awaiting first payment

### 2. Onboarding Controller Update
**File**: `app/Controllers/Onboarding.php` (lines 299-325)

Updated the `selectPlan()` method to set subscription status based on whether the plan has a trial:

**Before**:
```php
'status' => $plan['has_trial'] ? 'trial' : 'pending_payment',
```

**After**:
```php
'status' => $plan['has_trial'] ? 'trial' : 'new',
```

When a merchant selects a plan:
- **With trial** (`has_trial = true`): Status set to `'trial'`, period dates are set immediately
- **Without trial** (`has_trial = false`): Status set to `'new'`, period dates remain null until payment

### 3. Payment Controller Update
**File**: `app/Controllers/Payment.php` (lines 446-453)

Added a comment to clarify that the existing payment notification handler already processes the 'new' status correctly:

```php
// Only update status and dates if not already active or if this is a new subscription
// Also handle 'new' status (for plans without trials that need payment before activation)
if ($subscription['status'] !== 'active' || (isset($isUpdate) && !$isUpdate)) {
    $updateData['status'] = 'active';
    $updateData['trial_ends_at'] = null; // Trial is over
    $updateData['current_period_starts_at'] = date('Y-m-d H:i:s');
    $updateData['current_period_ends_at'] = date('Y-m-d H:i:s', strtotime('+1 month'));
}
```

When PayFast sends a successful payment notification (ITN):
- Subscription status changes from `'new'` → `'active'`
- Billing period start and end dates are set
- PayFast token is saved for recurring payments

### 4. Subscription Model
**File**: `app/Models/SubscriptionModel.php` (line 33)

The model already included 'new' in its validation rules:
```php
'status' => 'required|in_list[trial,active,past_due,cancelled,expired,new]'
```

The `getCurrentSubscription()` method (line 60) already includes 'new' status when fetching current subscriptions:
```php
->whereIn('subscriptions.status', ['trial', 'active', 'past_due', 'new'])
```

## Subscription Flow for Plans Without Trials

### Step 1: Merchant Selects Plan
1. Merchant completes onboarding and chooses a paid plan (no trial)
2. Subscription record created with:
   - `status = 'new'`
   - `current_period_starts_at = null`
   - `current_period_ends_at = null`
   - `trial_ends_at = null`

### Step 2: Payment Processing
1. Merchant redirected to PayFast payment page
2. PayFast processes payment
3. PayFast sends ITN (Instant Transaction Notification) to `/payment/notify`

### Step 3: Payment Success
1. Payment controller receives successful ITN
2. Subscription updated to:
   - `status = 'active'`
   - `current_period_starts_at = now()`
   - `current_period_ends_at = now() + 1 month`
   - `payfast_token = <token>` (for recurring payments)
3. Onboarding marked as complete
4. Merchant redirected to dashboard

### Step 4: Recurring Payments
1. PayFast automatically charges the saved payment method monthly
2. Each successful payment renews the subscription for another month
3. Failed payments trigger subscription status change to 'expired'

## Testing the Implementation

### Test Scenario 1: Plan with Trial
1. Sign up as merchant
2. Complete profile
3. Select plan with trial (e.g., 7-day trial)
4. **Expected**: Status = 'trial', no payment required immediately
5. After trial expires, payment required to continue

### Test Scenario 2: Plan Without Trial
1. Sign up as merchant
2. Complete profile
3. Select plan without trial
4. **Expected**: Status = 'new', redirected to payment
5. Complete payment via PayFast
6. **Expected**: Status changes to 'active', subscription activated

### Test Scenario 3: Payment Failure
1. Follow Test Scenario 2
2. Payment fails or is cancelled
3. **Expected**: Status remains 'new', merchant can retry payment

## Database Migration

To apply this change to an existing database, run:

```bash
mysql -h localhost -u root -D app_truckers_africa < add_new_subscription_status.sql
```

Or execute directly in phpMyAdmin/MySQL:
```sql
ALTER TABLE `subscriptions`
MODIFY COLUMN `status` enum('trial','active','past_due','cancelled','expired','new') NOT NULL;
```

## Impact on Existing Code

### Minimal Breaking Changes
- The 'new' status was already included in SubscriptionModel validation
- `getCurrentSubscription()` already queries for 'new' status
- Payment processing already handles non-active statuses correctly

### Areas That May Need Review
1. **Subscription Dashboard** (`app/Views/merchant/subscription/index.php`): May need to display different messaging for 'new' status
2. **Subscription Filters** (`SubscriptionFilter.php`): Ensure merchants with 'new' status have appropriate access restrictions
3. **Email Notifications**: Consider sending reminder emails to merchants with 'new' status who haven't completed payment

## Related Files
- `app/Controllers/Onboarding.php` - Onboarding flow
- `app/Controllers/Payment.php` - Payment processing
- `app/Controllers/Subscription.php` - Subscription management
- `app/Models/SubscriptionModel.php` - Subscription data model
- `truckers_africa_database.sql` - Database schema
- `add_new_subscription_status.sql` - Migration script

## Notes
- The 'new' status is functionally similar to a "pending_payment" status
- Merchants with 'new' status should not have access to premium features
- The status automatically transitions to 'active' upon successful payment via PayFast ITN
- No manual intervention required for status changes

## Date Implemented
November 19, 2025

## Author
Claude Code Assistant
