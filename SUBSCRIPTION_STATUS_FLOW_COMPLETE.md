# Complete Subscription Status Flow Documentation

## Overview
This document describes the complete subscription status lifecycle for all plan types, including the new `trial_pending` status that ensures payment method capture before trial access.

## All Subscription Statuses

| Status | Description | Access to Features |
|--------|-------------|-------------------|
| `trial_pending` | Trial plan selected, awaiting payment method | ❌ No access |
| `trial` | Free trial active, payment method on file | ✅ Full access |
| `new` | Paid plan selected, awaiting initial payment | ❌ No access |
| `active` | Paid subscription active and current | ✅ Full access |
| `past_due` | Billing period expired, awaiting renewal | ⚠️ Limited/grace period |
| `expired` | Trial/subscription ended without payment | ❌ No access |
| `cancelled` | Subscription cancelled by merchant | ❌ No access |

## Complete Subscription Flows

### Flow 1: Trial Plan (with payment method capture)

```
1. Merchant signs up and completes profile
   ↓
2. Selects plan WITH trial (e.g., 7-day trial)
   ↓
   Status: 'trial_pending'
   trial_ends_at: Set to now + trial_days
   current_period_starts_at: null
   current_period_ends_at: null
   ↓
3. Redirected to PayFast payment page
   Initial charge: R0.00 (zero)
   Sets up subscription for future billing
   ↓
4. Provides payment details, completes R0.00 transaction
   ↓
   PayFast ITN received
   ↓
   Status: 'trial_pending' → 'trial'
   current_period_starts_at: now
   current_period_ends_at: now + 1 month
   payfast_token: saved
   ↓
5. ✅ Trial starts - Full access to features
   ↓
6. Trial period ends (trial_ends_at reached)
   ↓
   PayFast automatically charges saved card
   ↓
   Status: 'trial' → 'active'
   trial_ends_at: null
   New billing period starts
   ↓
7. ✅ Subscription active - Continues with monthly billing
```

### Flow 2: Paid Plan (no trial)

```
1. Merchant signs up and completes profile
   ↓
2. Selects plan WITHOUT trial (e.g., $29/month)
   ↓
   Status: 'new'
   trial_ends_at: null
   current_period_starts_at: null
   current_period_ends_at: null
   ↓
3. Redirected to PayFast payment page
   Initial charge: Full plan price (e.g., R536)
   Sets up subscription for recurring billing
   ↓
4. Completes payment transaction
   ↓
   PayFast ITN received
   ↓
   Status: 'new' → 'active'
   current_period_starts_at: now
   current_period_ends_at: now + 1 month
   payfast_token: saved
   ↓
5. ✅ Subscription active - Full access immediately
   ↓
6. Each month: PayFast auto-charges
   ↓
   Status remains: 'active'
   Billing period updated
```

### Flow 3: Payment Failure During Trial Signup

```
1. Status: 'trial_pending'
   ↓
2. Payment page shown
   ↓
3. Merchant cancels OR payment fails
   ↓
   Status remains: 'trial_pending'
   ❌ No access to features
   ↓
4. Can retry payment from dashboard/subscription page
   ↓
5. Once successful:
   Status: 'trial_pending' → 'trial'
   ✅ Trial starts
```

### Flow 4: Payment Failure During Paid Signup

```
1. Status: 'new'
   ↓
2. Payment page shown
   ↓
3. Payment fails OR merchant cancels
   ↓
   Status remains: 'new'
   ❌ No access to features
   ↓
4. Can retry payment from dashboard/subscription page
   ↓
5. Once successful:
   Status: 'new' → 'active'
   ✅ Full access granted
```

### Flow 5: Subscription Expiry After Trial

```
1. Status: 'trial'
   Trial period ends
   ↓
2. PayFast attempts to charge saved card
   ↓
   Payment SUCCESSFUL:
   └─> Status: 'trial' → 'active'
       ✅ Subscription continues

   Payment FAILED:
   └─> Status: 'trial' → 'expired'
       ❌ Access revoked
       📧 Email sent to merchant
       Can renew from dashboard
```

### Flow 6: Recurring Payment Failure

```
1. Status: 'active'
   Billing period ends
   ↓
2. PayFast attempts automatic charge
   ↓
   Payment SUCCESSFUL:
   └─> Status remains: 'active'
       Billing period extended +1 month
       ✅ Access continues

   Payment FAILED:
   └─> Status: 'active' → 'expired'
       ❌ Access revoked
       📧 Email sent to merchant
       Can renew from dashboard
```

## Key Implementation Details

### Database Changes

**File**: `truckers_africa_database.sql`
```sql
`status` enum('trial','active','past_due','cancelled','expired','new','trial_pending') NOT NULL
```

**Migration**: `add_trial_pending_status.sql`
```sql
ALTER TABLE `subscriptions`
MODIFY COLUMN `status` enum('trial','active','past_due','cancelled','expired','new','trial_pending') NOT NULL;
```

### Code Changes

#### 1. Onboarding Controller (`app/Controllers/Onboarding.php`)

**selectPlan() method - Lines 302-325**:
```php
$subscriptionData = [
    'merchant_id' => $merchantId,
    'plan_id' => $planId,
    'status' => $plan['has_trial'] ? 'trial_pending' : 'new',
    'trial_ends_at' => $plan['has_trial'] ? date('Y-m-d H:i:s', strtotime('+' . $plan['trial_days'] . ' days')) : null,
    'current_period_starts_at' => null, // Will be set after payment
    'current_period_ends_at' => null // Will be set after payment
];
```

#### 2. Payment Controller (`app/Controllers/Payment.php`)

**notify() method - Lines 446-464**:
```php
if ($subscription['status'] === 'trial_pending') {
    // Trial plan - payment method captured, start trial period
    $updateData['status'] = 'trial';
    // trial_ends_at is already set, don't modify it
    $updateData['current_period_starts_at'] = date('Y-m-d H:i:s');
    $updateData['current_period_ends_at'] = date('Y-m-d H:i:s', strtotime('+1 month'));
} else {
    // Paid plan or trial expired - activate immediately
    $updateData['status'] = 'active';
    $updateData['trial_ends_at'] = null;
    $updateData['current_period_starts_at'] = date('Y-m-d H:i:s');
    $updateData['current_period_ends_at'] = date('Y-m-d H:i:s', strtotime('+1 month'));
}
```

**Initial payment amount - Lines 66-70**:
```php
// Initial amount logic:
// - If has trial (regardless of price): R0.00 initially
// - If no trial and paid plan: charge full price initially
// - If free plan: R0.00 initially
$initialAmount = ($hasTrial || $isFree) ? 0.00 : $planPriceZAR;
```

#### 3. Subscription Model (`app/Models/SubscriptionModel.php`)

**Validation rules - Line 33**:
```php
'status' => 'required|in_list[trial,active,past_due,cancelled,expired,new,trial_pending]'
```

**getCurrentSubscription() - Line 60**:
```php
->whereIn('subscriptions.status', ['trial', 'active', 'past_due', 'new', 'trial_pending'])
```

#### 4. Subscription Controller (`app/Controllers/Subscription.php`)

**index() method - Lines 39, 58-59**:
```php
// Query includes trial_pending
->whereIn('subscriptions.status', ['trial', 'active', 'past_due', 'new', 'trial_pending'])

// Display message for trial_pending
elseif ($currentSubscription['status'] === 'trial_pending') {
    session()->setFlashdata('error', 'Please provide your payment method to start your free trial.');
}
```

## Access Control

### Merchants Should NOT Have Access When:
- Status = `'trial_pending'` - Payment method not captured yet
- Status = `'new'` - Initial payment not completed
- Status = `'expired'` - Subscription/trial ended
- Status = `'cancelled'` - Manually cancelled

### Merchants Should Have Full Access When:
- Status = `'trial'` - Active trial period
- Status = `'active'` - Paid and current

### Merchants May Have Limited/Grace Access When:
- Status = `'past_due'` - Billing period ended, waiting for renewal (implementation dependent)

## Feature Checks

Example filter or helper function to check subscription access:

```php
public function hasActiveSubscription($merchantId): bool
{
    $subscription = $this->subscriptionModel->getCurrentSubscription($merchantId);

    if (!$subscription) {
        return false;
    }

    // Only 'trial' and 'active' have full access
    return in_array($subscription['status'], ['trial', 'active']);
}
```

## Email Notifications

### Recommended Email Triggers:

1. **Status: trial_pending**
   - "Complete Your Payment Method to Start Trial"
   - Send if no payment method after 24 hours

2. **Status: trial → active**
   - "Your Trial Has Ended - Subscription Activated"
   - Confirmation of first charge

3. **Status: new (no payment after 24h)**
   - "Complete Your Payment to Activate"
   - Reminder email with payment link

4. **Status: trial/active → expired**
   - "Your Subscription Has Expired"
   - Instructions to renew
   - Payment link

5. **Recurring Payment Success**
   - "Payment Successful - Subscription Renewed"
   - Receipt and next billing date

6. **Recurring Payment Failed**
   - "Payment Failed - Update Your Payment Method"
   - Link to update card details

## Testing Checklist

### Test Case 1: Trial Plan Signup ✅
- [ ] Select plan with trial
- [ ] Verify status = 'trial_pending'
- [ ] Verify NO access to features
- [ ] Complete R0.00 payment on PayFast
- [ ] Verify status = 'trial'
- [ ] Verify FULL access to features
- [ ] Verify trial_ends_at is set correctly

### Test Case 2: Paid Plan Signup ✅
- [ ] Select plan without trial
- [ ] Verify status = 'new'
- [ ] Verify NO access to features
- [ ] Complete full payment on PayFast
- [ ] Verify status = 'active'
- [ ] Verify FULL access to features

### Test Case 3: Trial Payment Cancellation ✅
- [ ] Select trial plan
- [ ] Status = 'trial_pending'
- [ ] Cancel payment on PayFast page
- [ ] Verify status remains 'trial_pending'
- [ ] Verify NO access
- [ ] Retry payment from dashboard
- [ ] Verify successful completion changes status to 'trial'

### Test Case 4: Paid Payment Cancellation ✅
- [ ] Select paid plan
- [ ] Status = 'new'
- [ ] Cancel payment
- [ ] Verify status remains 'new'
- [ ] Verify NO access
- [ ] Retry payment
- [ ] Verify status = 'active'

### Test Case 5: Trial Expiry with Successful Billing ✅
- [ ] Have active trial (status = 'trial')
- [ ] Wait for trial_ends_at OR manually trigger
- [ ] PayFast auto-charges card
- [ ] Verify status = 'active'
- [ ] Verify access continues

### Test Case 6: Trial Expiry with Failed Billing ✅
- [ ] Have active trial
- [ ] Expire trial with invalid payment method
- [ ] Verify status = 'expired'
- [ ] Verify access revoked
- [ ] Verify email sent

## Migration Instructions

### For Existing Installations:

1. **Backup database**
   ```bash
   mysqldump -u root app_truckers_africa > backup_before_migration.sql
   ```

2. **Run migration**
   ```bash
   mysql -h localhost -u root -D app_truckers_africa < add_trial_pending_status.sql
   ```

3. **Verify migration**
   ```sql
   DESCRIBE subscriptions;
   ```
   Check that status enum includes 'trial_pending'

4. **Check existing subscriptions**
   ```sql
   SELECT id, merchant_id, status, trial_ends_at, created_at
   FROM subscriptions
   WHERE status IN ('trial', 'new');
   ```

5. **No data updates needed** - Existing subscriptions remain unchanged

## Related Files

- `app/Controllers/Onboarding.php` - Plan selection logic
- `app/Controllers/Payment.php` - Payment processing and ITN handling
- `app/Controllers/Subscription.php` - Subscription management dashboard
- `app/Models/SubscriptionModel.php` - Data model and queries
- `truckers_africa_database.sql` - Schema definition
- `add_trial_pending_status.sql` - Migration script
- `SUBSCRIPTION_NEW_STATUS_IMPLEMENTATION.md` - Previous 'new' status documentation

## Frequently Asked Questions

**Q: Why not just use 'trial' immediately when plan is selected?**
A: Because merchants would have access to features before providing payment details, allowing trial abuse.

**Q: Can merchants skip payment for trial plans?**
A: No - the onboarding flow requires payment method capture even for R0.00 charges.

**Q: What happens if PayFast ITN is delayed?**
A: Status remains 'trial_pending' or 'new' until ITN is received. Merchants are redirected to success page but should see "processing" message until ITN updates status.

**Q: How long does R0.00 payment authorization take?**
A: Usually instant, but can take up to 5 minutes in PayFast sandbox mode.

**Q: What if a merchant has 'trial_pending' status for days?**
A: Send reminder emails and provide easy access to retry payment from dashboard. Consider auto-expiring after 7 days.

## Date Implemented
November 19, 2025

## Author
Claude Code Assistant
