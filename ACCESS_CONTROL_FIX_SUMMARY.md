# Access Control Fix - Complete Summary

## Problem Identified

You reported that:
1. ✅ Selected a plan with NO trial (Lite plan - $1.00, 0 trial days)
2. ❌ Subscription status showing as `'trial'` instead of `'new'`
3. ❌ Able to access branches and other premium features without payment
4. ❌ No access control blocking incomplete subscriptions

## Root Causes Found

### Issue 1: Missing Access Control for Pending Statuses
**File**: `app/Filters/SubscriptionFilter.php` (Line 76)

**Problem**: Filter only blocked `['expired', 'past_due', 'cancelled']`
- Did NOT block `'new'` (awaiting payment)
- Did NOT block `'trial_pending'` (awaiting payment method)

**Result**: Merchants could access all features even without paying

### Issue 2: Old Subscription Creation Code
**File**: `app/Controllers/Auth.php` (Lines 716-728)

**Problem**: Old hardcoded logic set:
- Status = `'trial'` for ALL paid plans (regardless of plan configuration)
- Hardcoded 14-day trial (ignoring plan's actual trial_days)
- Immediate access without payment

**Result**: Wrong status assigned, bypassing payment requirement

### Issue 3: Missing Route Protection
**File**: `app/Config/Filters.php` (Line 82)

**Problem**: Branch creation routes not in subscription filter list
- `merchant/branch/*` not protected
- `merchant/branches/*` not protected

**Result**: Could add branches even without active subscription

## Fixes Applied

### Fix 1: Updated SubscriptionFilter ✅
**File**: `app/Filters/SubscriptionFilter.php`

**Changes**:
```php
// OLD:
$blockedStatuses = ['expired', 'past_due', 'cancelled'];

// NEW:
$blockedStatuses = ['expired', 'past_due', 'cancelled', 'new', 'trial_pending'];
```

**Added messages**:
- `'new'` → "Please complete your payment to activate your subscription"
- `'trial_pending'` → "Please provide your payment method to start your free trial"

**Impact**: Merchants with pending status now blocked from premium features

### Fix 2: Fixed Auth.php Subscription Logic ✅
**File**: `app/Controllers/Auth.php` (Lines 716-729)

**Changes**:
```php
// OLD: Hardcoded 14-day trial for all paid plans
if ($plan['price'] > 0) {
    $subscriptionData['status'] = 'trial';
    $subscriptionData['trial_ends_at'] = date('Y-m-d H:i:s', strtotime('+14 days'));
}

// NEW: Dynamic status based on actual plan configuration
$isFree = ($plan['price'] <= 0);
$hasTrial = !empty($plan['trial_days']) && $plan['trial_days'] > 0;

$subscriptionData = [
    'status' => $isFree ? 'active' : ($hasTrial ? 'trial_pending' : 'new'),
    'trial_ends_at' => $hasTrial ? date('Y-m-d H:i:s', strtotime('+' . $plan['trial_days'] . ' days')) : null,
    'current_period_starts_at' => $isFree ? date('Y-m-d H:i:s') : null,
    'current_period_ends_at' => $isFree ? date('Y-m-d H:i:s', strtotime('+1 month')) : null,
];
```

**Impact**:
- Lite plan (no trial) → Status = `'new'`
- Plus/Max plans (with trial) → Status = `'trial_pending'`
- Free plans → Status = `'active'` (immediate access)

### Fix 3: Added Branch Routes to Filter ✅
**File**: `app/Config/Filters.php` (Lines 82-97)

**Added routes**:
```php
'merchant/branch/*',
'merchant/branches/*',
'merchant/listing-requests/*',
```

**Impact**: All branch and listing request features now protected

## Correct Flow Now

### For Lite Plan (No Trial - $1.00/month):
```
1. Select Lite Plan
   ↓
   Status: 'new' ❌ No Access
   ↓
2. Redirected to PayFast
   Charge: $1.00 (R18.50)
   ↓
3. Complete Payment
   ↓
   Status: 'new' → 'active' ✅ Full Access
   PayFast token saved
   Billing period set
```

### For Plus/Max Plans (With Trial):
```
1. Select Plus/Max Plan
   ↓
   Status: 'trial_pending' ❌ No Access
   ↓
2. Redirected to PayFast
   Charge: R0.00 (capture card)
   ↓
3. Complete Payment Method Capture
   ↓
   Status: 'trial_pending' → 'trial' ✅ Full Access
   PayFast token saved
   Trial period active
   ↓
4. Trial Expires
   PayFast auto-charges
   ↓
   Status: 'trial' → 'active' ✅ Access Continues
```

## Fixing Your Current Subscription

Your current subscription needs to be corrected. Run this in phpMyAdmin:

```sql
-- Check your current subscription
SELECT s.id, s.merchant_id, s.status, p.name, p.has_trial, s.payfast_token
FROM subscriptions s
JOIN plans p ON s.plan_id = p.id
WHERE s.merchant_id = [YOUR_MERCHANT_ID];

-- If you selected Lite (no trial) and haven't paid:
UPDATE subscriptions
SET status = 'new',
    trial_ends_at = NULL,
    current_period_starts_at = NULL,
    current_period_ends_at = NULL
WHERE merchant_id = [YOUR_MERCHANT_ID]
  AND plan_id = 1; -- Lite plan

-- OR if you selected Plus/Max (with trial) and haven't provided payment:
UPDATE subscriptions
SET status = 'trial_pending',
    current_period_starts_at = NULL,
    current_period_ends_at = NULL
WHERE merchant_id = [YOUR_MERCHANT_ID]
  AND plan_id IN (2, 3); -- Plus or Max
```

Alternatively, run the complete fix script:
```bash
mysql -h localhost -u root -D app_truckers_africa < fix_existing_subscriptions.sql
```

## Testing the Fix

### Test 1: Try to Access Branches Now
1. Log in with your current account
2. Try to access: `/merchant/branch/add` or `/merchant/locations`
3. **Expected**: Redirected to subscription page with error message
4. **Message**: "Please complete your payment to activate your subscription"

### Test 2: Complete Payment
1. Go to subscription page
2. Complete payment via PayFast
3. **Expected**: Status changes to `'active'` (for Lite) or `'trial'` (for Plus/Max)
4. **Expected**: Can now access all features

### Test 3: New Signup
1. Create new merchant account
2. Select Lite plan (no trial)
3. **Expected**: Status = `'new'`, no access
4. Complete payment
5. **Expected**: Status = `'active'`, full access

### Test 4: Trial Plan Signup
1. Create new merchant account
2. Select Plus plan (30-day trial)
3. **Expected**: Status = `'trial_pending'`, no access
4. Complete R0.00 payment (capture card)
5. **Expected**: Status = `'trial'`, full access

## Files Modified

1. ✅ `app/Filters/SubscriptionFilter.php` - Access control
2. ✅ `app/Controllers/Auth.php` - Subscription creation logic
3. ✅ `app/Config/Filters.php` - Route protection
4. ✅ `app/Controllers/Onboarding.php` - Already fixed (previous update)
5. ✅ `app/Controllers/Payment.php` - Already fixed (previous update)
6. ✅ `app/Models/SubscriptionModel.php` - Already fixed (previous update)

## Database Updates Needed

1. ✅ Add `'trial_pending'` to status enum (already done)
2. ⚠️ Fix existing subscriptions with wrong status (run `fix_existing_subscriptions.sql`)

## Access Control Summary

### Blocked Statuses (No Access):
- ❌ `'new'` - Payment not completed
- ❌ `'trial_pending'` - Payment method not captured
- ❌ `'expired'` - Trial/subscription ended
- ❌ `'past_due'` - Billing period expired
- ❌ `'cancelled'` - Manually cancelled

### Allowed Statuses (Full Access):
- ✅ `'trial'` - Active trial period
- ✅ `'active'` - Paid and current subscription

## Protected Routes

All these routes now require active subscription:
- `merchant/locations/*` - Location management
- `merchant/branch/*` - Branch creation
- `merchant/branches/*` - Branch management
- `merchant/listings/*` - Listing management
- `merchant/listing-requests/*` - Listing requests
- `merchant/orders/*` - Order management
- `merchant/reports/*` - Reports
- `merchant/analytics/*` - Analytics
- `merchant/settings/*` - Settings
- `merchant/team/*` - Team management
- `branch/*` - All branch user routes (except dashboard/profile)

## Allowed Routes (Even Without Payment)

These routes remain accessible:
- `merchant/subscription` - View subscription status
- `merchant/subscription/plans` - View available plans
- `merchant/subscription/renew` - Renew expired subscription
- `merchant/dashboard` - Dashboard (with warnings)
- `merchant/profile` - View/edit profile
- `payment/*` - Payment processing
- `logout` - Logout

## Next Steps

1. **Immediate**: Run `fix_existing_subscriptions.sql` to correct your current subscription
2. **Test**: Try accessing branches - should be blocked
3. **Complete Payment**: Go through payment flow to activate
4. **Verify**: Check that access is granted after payment

## Questions?

**Q: Why wasn't this caught before?**
A: Two codebases were creating subscriptions (Auth.php and Onboarding.php) with different logic. Auth.php had old hardcoded trial logic.

**Q: Will this affect existing active subscriptions?**
A: No - subscriptions with status `'trial'` or `'active'` that have PayFast tokens will continue working.

**Q: What about free plans?**
A: Free plans (price = $0) get immediate `'active'` status with full access.

**Q: Can I still test without paying?**
A: Use PayFast sandbox mode with test cards. See PAYFAST_TESTING_GUIDE.md.

## Date: November 19, 2025
