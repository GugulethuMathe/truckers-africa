# Dashboard Cancelled Subscription Display Fix

## Overview

Fixed two issues on the merchant dashboard after subscription cancellation:
1. "Current Plan" label should change to "Previous Plan" when subscription is cancelled/expired
2. "Welcome, !" message was missing the merchant name

## Problems

### Problem 1: Current Plan Label After Cancellation

When a merchant cancelled their subscription, the dashboard still showed:
```
❌ Current Plan
   Max
```

This was confusing because the subscription was no longer active. It should show:
```
✅ Previous Plan
   Max
```

### Problem 2: Missing Merchant Name in Welcome Message

The welcome header showed:
```
❌ Welcome, !
```

Instead of:
```
✅ Welcome, Yvonne Thulare!
```

**Root Cause**: The dashboard was using `session()->get('name')` which doesn't exist in the merchant session. The merchant session stores `first_name` and `business_name` instead.

## Solutions

### Fix 1: Dynamic Plan Label Based on Subscription Status

**File**: `app/Views/merchant/dashboard.php` (Lines 141-161)

**Changed**:
```php
// BEFORE
<p class="text-sm text-gray-500">Current Plan</p>

// AFTER
<?php
$subscriptionStatus = $subscription['status'] ?? 'inactive';
$trialDays = $subscription['trial_days'] ?? 30;
$planName = $subscription['plan_name'] ?? 'No Plan';

// Show "Previous Plan" if subscription is cancelled or expired
$isPreviousPlan = in_array($subscriptionStatus, ['cancelled', 'expired']);
$planLabel = $isPreviousPlan ? 'Previous Plan' : 'Current Plan';
?>
<p class="text-sm text-gray-500"><?= $planLabel ?></p>
```

**Logic**:
- Check subscription status
- If status is `cancelled` or `expired`, show "Previous Plan"
- Otherwise, show "Current Plan"

### Fix 2: Correct Merchant Name Display

**File**: `app/Views/merchant/dashboard.php` (Lines 56-64)

**Changed**:
```php
// BEFORE
<h1 class="text-xl lg:text-3xl font-bold text-gray-900">
    Welcome, <?= esc(session()->get('name')) ?>!
</h1>

// AFTER
<?php
// Get merchant name from session (first_name or business_name)
$merchantName = session()->get('first_name') ?: session()->get('business_name') ?: 'Merchant';
?>
<h1 class="text-xl lg:text-3xl font-bold text-gray-900">
    Welcome, <?= esc($merchantName) ?>!
</h1>
```

**Logic**:
1. Try to get `first_name` from session
2. If empty, try `business_name`
3. If both empty, default to "Merchant"

## Merchant Session Keys

Based on the codebase analysis, merchant sessions contain:

```php
[
    'user_id' => 17,
    'merchant_id' => 17,
    'user_type' => 'merchant',
    'email' => 'merchant@example.com',
    'business_name' => 'Website Zar',
    'first_name' => 'Yvonne Thulare',  // May be empty
    'last_name' => '',                  // Usually empty
    'is_logged_in' => true,
    'verification_status' => 'approved'
]
```

**Note**: `name` key does NOT exist in merchant sessions.

## Subscription Statuses

The system recognizes these subscription statuses:

- `trial` - Free trial period (shows "Current Plan")
- `active` - Paid and active (shows "Current Plan")
- `past_due` - Payment failed (shows "Current Plan")
- `new` - Not yet paid (shows "Current Plan")
- `trial_pending` - Trial not started (shows "Current Plan")
- `cancelled` - Merchant cancelled (shows "Previous Plan" ✅)
- `expired` - Subscription ended (shows "Previous Plan" ✅)
- `inactive` - No subscription (shows "Current Plan")

## Visual Examples

### Before Cancellation:
```
┌─────────────────────────────────────┐
│ Subscription Status                 │
├─────────────────────────────────────┤
│ Current Plan                        │
│ Max                                 │
│                                     │
│ [Manage Subscription]               │
└─────────────────────────────────────┘
```

### After Cancellation:
```
┌─────────────────────────────────────┐
│ Subscription Status                 │
├─────────────────────────────────────┤
│ Previous Plan                       │
│ Max                                 │
│                                     │
│ [Manage Subscription]               │
└─────────────────────────────────────┘
```

### Welcome Message:
```
BEFORE: Welcome, !
AFTER:  Welcome, Yvonne Thulare!
```

## Testing

### Test 1: Cancelled Subscription Display

1. Login as merchant with cancelled subscription
2. Go to: `http://localhost/truckers-africa/merchant/dashboard`
3. ✅ Should see "Previous Plan" label
4. ✅ Should see plan name (e.g., "Max")

### Test 2: Active Subscription Display

1. Login as merchant with active subscription
2. Go to: `http://localhost/truckers-africa/merchant/dashboard`
3. ✅ Should see "Current Plan" label
4. ✅ Should see plan name

### Test 3: Welcome Message

1. Login as merchant
2. Go to: `http://localhost/truckers-africa/merchant/dashboard`
3. ✅ Should see "Welcome, [First Name]!" or "Welcome, [Business Name]!"
4. ✅ Should NOT see "Welcome, !"

### Test 4: Different Merchant Types

**Merchant with first_name**:
- Session: `first_name = "John Doe"`
- Display: "Welcome, John Doe!"

**Merchant without first_name**:
- Session: `first_name = ""`, `business_name = "ABC Company"`
- Display: "Welcome, ABC Company!"

**Merchant with neither**:
- Session: `first_name = ""`, `business_name = ""`
- Display: "Welcome, Merchant!"

## Benefits

### For Merchants:
- ✅ Clear indication that subscription is no longer active
- ✅ Personalized welcome message
- ✅ Better user experience
- ✅ No confusion about subscription status

### For Business:
- ✅ Accurate status display
- ✅ Reduced support tickets
- ✅ Professional appearance
- ✅ Clear communication

## Files Modified

1. ✅ `app/Views/merchant/dashboard.php` - Fixed plan label and welcome message
2. ✅ `DASHBOARD_CANCELLED_SUBSCRIPTION_FIX.md` - Documentation

## Related Issues

- **Subscription Cancellation**: `SUBSCRIPTION_CANCELLATION_MODAL.md`
- **Foreign Key Fix**: `SUBSCRIPTION_CANCELLATION_FK_FIX.md`
- **Reactivation**: `SUBSCRIPTION_REACTIVATION_FIX.md`

---

**Issue**: Dashboard showed "Current Plan" after cancellation and missing merchant name
**Root Cause**: No status-based label logic and wrong session key
**Fix**: Dynamic label based on status + correct session key usage
**Status**: ✅ Fixed and Ready for Testing
**Date**: 2025-11-19

