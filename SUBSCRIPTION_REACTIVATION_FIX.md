# Subscription Reactivation Fix - Implementation Summary

## Problem

When a merchant cancelled their subscription and tried to reactivate by selecting a new plan, they would see:
```
No active subscription. Choose a plan to continue.
```

Even after selecting a plan and attempting payment, the subscription would not activate properly.

## Root Cause

The `processPayment()` method in `app/Controllers/Subscription.php` only looked for **active** subscriptions (statuses: `trial`, `active`, `past_due`, `new`, `trial_pending`). 

When a merchant had a **cancelled** subscription:
1. `getCurrentSubscription()` returned `null` (cancelled not included)
2. `processPayment()` couldn't find a subscription to process
3. No new subscription record was created
4. Payment would fail or not link to any subscription

## Solution

Updated `processPayment()` method to automatically create a new subscription record when a merchant with a cancelled/expired subscription tries to reactivate.

### Changes Made

**File**: `app/Controllers/Subscription.php` (Lines 636-707)

**Added Logic**:
1. Check if merchant has no current active subscription
2. Look for any cancelled/expired subscriptions
3. If found, create a NEW subscription record with appropriate status:
   - **Trial plans**: Create with `trial_pending` status
   - **Paid plans**: Create with `new` status
4. Proceed to payment as normal

### Code Changes

<augment_code_snippet path="app/Controllers/Subscription.php" mode="EXCERPT">
```php
// Check if merchant has a cancelled or expired subscription
// If so, create a new subscription record before payment
$currentSubscription = $this->subscriptionModel->getCurrentSubscription($merchantId);

if (!$currentSubscription) {
    // Check if they have a cancelled/expired subscription
    $oldSubscription = $this->subscriptionModel
        ->where('merchant_id', $merchantId)
        ->whereIn('status', ['cancelled', 'expired'])
        ->orderBy('updated_at', 'DESC')
        ->first();
    
    if ($oldSubscription) {
        // Create new subscription record with appropriate status
        $plan = $this->planModel->find($planId);
        
        if ($plan && $plan['has_trial'] && $plan['trial_days'] > 0) {
            // Plan has trial - create with trial_pending status
            $this->subscriptionModel->insert([
                'merchant_id' => $merchantId,
                'plan_id' => $planId,
                'status' => 'trial_pending',
                'trial_ends_at' => date('Y-m-d H:i:s', strtotime("+{$plan['trial_days']} days")),
                'current_period_starts_at' => date('Y-m-d H:i:s'),
                'current_period_ends_at' => date('Y-m-d H:i:s', strtotime("+1 month"))
            ]);
        } else {
            // No trial - create with 'new' status
            $this->subscriptionModel->insert([
                'merchant_id' => $merchantId,
                'plan_id' => $planId,
                'status' => 'new'
            ]);
        }
        
        log_message('info', "Created new subscription for merchant {$merchantId} after cancellation/expiry");
    }
}
```
</augment_code_snippet>

## How It Works Now

### Reactivation Flow

**Step 1: Merchant Cancels Subscription**
- Subscription status → `cancelled`
- Access continues until period end
- Merchant sees "No Active Subscription" after period ends

**Step 2: Merchant Selects New Plan**
- Goes to `/merchant/subscription/plans`
- Clicks "Subscribe Now" on any plan
- Form submits to `/merchant/subscription/process-payment`

**Step 3: System Creates New Subscription**
- `processPayment()` detects no active subscription
- Finds old cancelled subscription
- Creates NEW subscription record:
  - Trial plan → status: `trial_pending`
  - Paid plan → status: `new`

**Step 4: Payment Processing**
- Redirects to PayFast payment gateway
- Merchant completes payment
- PayFast ITN callback updates subscription:
  - `trial_pending` → `trial` (trial starts)
  - `new` → `active` (subscription active)

**Step 5: Subscription Active**
- Merchant has full access
- Business visible to drivers
- Can receive orders

## Testing Scenarios

### Scenario 1: Reactivate After Cancellation ✅

**Steps**:
1. Login as merchant with active subscription
2. Cancel subscription (with reason and confirmation)
3. Wait for period to end OR manually update status to `cancelled` in database
4. Go to `/merchant/subscription`
5. Click "Choose a Plan"
6. Select any plan and click "Subscribe Now"
7. Complete payment

**Expected Result**:
- ✅ New subscription record created
- ✅ Payment processes successfully
- ✅ Subscription status becomes `active` or `trial`
- ✅ Merchant dashboard shows active subscription
- ✅ Business visible to drivers

### Scenario 2: Reactivate with Trial Plan ✅

**Steps**:
1. Merchant with cancelled subscription
2. Select plan with free trial
3. Click "Subscribe Now"
4. Complete payment setup (R0.00 charge)

**Expected Result**:
- ✅ New subscription created with `trial_pending` status
- ✅ After payment: status → `trial`
- ✅ Trial period starts
- ✅ Full access during trial

### Scenario 3: Reactivate with Paid Plan ✅

**Steps**:
1. Merchant with cancelled subscription
2. Select plan without trial
3. Click "Subscribe Now"
4. Complete payment

**Expected Result**:
- ✅ New subscription created with `new` status
- ✅ After payment: status → `active`
- ✅ Immediate full access

## Database Behavior

### Before Fix:
```sql
-- Merchant cancels
UPDATE subscriptions SET status = 'cancelled' WHERE id = 1;

-- Merchant tries to reactivate
-- No new record created ❌
-- Payment fails or orphaned ❌
```

### After Fix:
```sql
-- Merchant cancels
UPDATE subscriptions SET status = 'cancelled' WHERE id = 1;

-- Merchant tries to reactivate
-- New subscription record created ✅
INSERT INTO subscriptions (merchant_id, plan_id, status, ...) 
VALUES (123, 2, 'new', ...);

-- Payment completes
UPDATE subscriptions SET status = 'active' WHERE id = 2;
```

## Subscription History

Merchants will have multiple subscription records:
- **Old subscription**: status = `cancelled`, preserved for history
- **New subscription**: status = `active`, current subscription

This is intentional and allows:
- ✅ Tracking subscription history
- ✅ Analyzing cancellation patterns
- ✅ Viewing past plans and periods
- ✅ Calculating lifetime value

## Related Files

- `app/Controllers/Subscription.php` - Main subscription controller
- `app/Models/SubscriptionModel.php` - Subscription model
- `app/Views/merchant/subscription/index.php` - Subscription dashboard
- `app/Views/merchant/subscription/plans.php` - Plan selection page
- `app/Controllers/Payment.php` - PayFast payment processing

## Benefits

### For Merchants:
- ✅ Seamless reactivation process
- ✅ No manual intervention needed
- ✅ Can switch plans after cancellation
- ✅ Clear subscription history

### For Business:
- ✅ Reduced support tickets
- ✅ Higher reactivation rate
- ✅ Better subscription tracking
- ✅ Accurate analytics

## Future Enhancements

1. **Win-back Offers**: Show special discount when reactivating
2. **Plan Recommendations**: Suggest different plan based on cancellation reason
3. **Seamless Upgrade**: Allow reactivation with plan upgrade in one step
4. **Grace Period**: Allow reactivation within X days without new payment

---

**Implementation Date**: 2025-11-19
**Status**: ✅ Complete and Ready for Testing
**Issue**: Resolved - Merchants can now reactivate after cancellation

