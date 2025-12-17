# Testing Guide: Subscription Payment Flow

## Prerequisites

1. **PayFast Sandbox Credentials** (in `.env`):
   ```
   payfast.merchantId = 10000100
   payfast.merchantKey = 46f0cd694581a
   payfast.passphrase = jt7NOE43FZPn
   payfast.processUrl = https://sandbox.payfast.co.za/eng/process
   payfast.validateUrl = https://sandbox.payfast.co.za/eng/query/validate
   ```

2. **Test Plans in Database**:
   - At least one plan with `has_trial = 0` (no trial)
   - At least one plan with `has_trial = 1` (with trial)
   - Plans with different prices for testing upgrades/downgrades

## Test Scenarios

### Test 1: New Merchant Registration (No Trial Plan)

**Steps**:
1. Navigate to `/signup/merchant`
2. Fill in registration form
3. Submit registration
4. Select a plan **without** free trial (e.g., "Professional Plan")
5. Verify redirect to `/pricing` or plan selection page
6. Click "Choose Plan" on a non-trial plan

**Expected Results**:
- ✅ Merchant account created
- ✅ Subscription created with status = `'new'`
- ✅ Redirected to merchant dashboard
- ✅ Red payment alert visible at top
- ✅ "Complete Payment Now" button visible
- ✅ Subscription status shows "Payment Required"

**Verify in Database**:
```sql
SELECT id, merchant_id, plan_id, status, created_at 
FROM subscriptions 
WHERE merchant_id = [merchant_id] 
ORDER BY created_at DESC LIMIT 1;
```
Expected: `status = 'new'`

### Test 2: Complete Payment Flow

**Steps** (continuing from Test 1):
1. Click "Complete Payment Now" button on dashboard
2. Verify redirect to PayFast sandbox
3. Use PayFast test card details:
   - Card Number: 4000 0000 0000 0002
   - CVV: 123
   - Expiry: Any future date
4. Complete payment on PayFast
5. Wait for redirect back to application

**Expected Results**:
- ✅ Redirected to PayFast payment page
- ✅ Payment form shows correct plan name and amount
- ✅ After payment, redirected to success page
- ✅ Subscription status updated to `'active'`
- ✅ Payment alert no longer visible on dashboard
- ✅ Can now access all paid features

**Verify in Database**:
```sql
SELECT id, status, current_period_starts_at, current_period_ends_at, payfast_token
FROM subscriptions 
WHERE merchant_id = [merchant_id];
```
Expected: 
- `status = 'active'`
- `current_period_starts_at` and `current_period_ends_at` populated
- `payfast_token` populated

### Test 3: Feature Access Blocking (Unpaid)

**Steps**:
1. Login as merchant with `status = 'new'`
2. Try to access `/merchant/listings`
3. Try to access `/merchant/orders`
4. Try to access `/merchant/locations`

**Expected Results**:
- ✅ Blocked from accessing paid features
- ✅ Redirected to `/merchant/subscription`
- ✅ Error message displayed
- ✅ "Complete Payment" button visible

**Error Message Should Say**:
```
Payment required! Please complete your payment to activate your 
subscription and access premium features. Click "Complete Payment" 
below to proceed to PayFast.
```

### Test 4: Plan Change Before Payment

**Steps**:
1. Login as merchant with `status = 'new'` (unpaid)
2. Navigate to `/merchant/subscription`
3. Click "Change Plan"
4. Select a different plan
5. Click "Switch to [Plan Name]"

**Expected Results**:
- ✅ Subscription plan_id updated to new plan
- ✅ Info message: "Plan updated. Please complete payment..."
- ✅ Redirected to PayFast with new plan amount
- ✅ Can complete payment for new plan

**Verify in Database**:
```sql
SELECT plan_id, status FROM subscriptions WHERE merchant_id = [merchant_id];
```
Expected: `plan_id` changed, `status` still `'new'`

### Test 5: Trial Plan Registration

**Steps**:
1. Register new merchant
2. Select plan **with** free trial
3. Click "Start Free Trial"

**Expected Results**:
- ✅ Subscription created with status = `'trial_pending'`
- ✅ Dashboard shows "Payment Setup Required" alert
- ✅ Button says "Complete Payment Setup"
- ✅ Message explains no charge during trial

**Verify in Database**:
```sql
SELECT status, trial_ends_at FROM subscriptions WHERE merchant_id = [merchant_id];
```
Expected: `status = 'trial_pending'`, `trial_ends_at` set to future date

### Test 6: Trial Payment Setup

**Steps** (continuing from Test 5):
1. Click "Complete Payment Setup"
2. Complete PayFast payment method capture
3. Verify redirect back to application

**Expected Results**:
- ✅ Subscription status changes to `'trial'`
- ✅ Dashboard shows trial countdown
- ✅ All features accessible during trial
- ✅ No immediate charge

**Verify in Database**:
```sql
SELECT status, trial_ends_at, payfast_token FROM subscriptions WHERE merchant_id = [merchant_id];
```
Expected: `status = 'trial'`, `payfast_token` populated

### Test 7: Subscription Page Display

**Steps**:
1. Login as merchant with `status = 'new'`
2. Navigate to `/merchant/subscription`

**Expected Results**:
- ✅ Large red payment alert at top
- ✅ Current subscription card shows plan details
- ✅ Status badge shows "Payment Required" (orange)
- ✅ "Complete Payment" button (green, prominent)
- ✅ "Change Plan" button (gray, secondary)
- ✅ No "Cancel Subscription" button

### Test 8: Active Subscription Display

**Steps**:
1. Login as merchant with `status = 'active'`
2. Navigate to `/merchant/subscription`

**Expected Results**:
- ✅ No payment alert
- ✅ Status badge shows "Active" (green)
- ✅ "Change Plan" button visible
- ✅ "Cancel Subscription" button visible
- ✅ Next billing date displayed

## Database Queries for Testing

### Check Subscription Status:
```sql
SELECT s.id, s.merchant_id, s.status, s.plan_id, p.name as plan_name, 
       s.current_period_starts_at, s.current_period_ends_at, s.trial_ends_at
FROM subscriptions s
LEFT JOIN plans p ON s.plan_id = p.id
WHERE s.merchant_id = [merchant_id]
ORDER BY s.created_at DESC;
```

### Manually Set Subscription to 'new' (for testing):
```sql
UPDATE subscriptions 
SET status = 'new', 
    current_period_starts_at = NULL, 
    current_period_ends_at = NULL,
    payfast_token = NULL
WHERE merchant_id = [merchant_id];
```

### Check Payment Transactions:
```sql
SELECT * FROM payment_transactions 
WHERE merchant_id = [merchant_id] 
ORDER BY created_at DESC;
```

## Common Issues and Solutions

### Issue: Payment button doesn't redirect
**Solution**: Check that route exists in `app/Config/Routes.php`:
```php
$routes->post('process-payment', 'Subscription::processPayment');
```

### Issue: Still blocked after payment
**Solution**: Check PayFast ITN logs:
```bash
tail -f writable/logs/log-*.log | grep PayFast
```

### Issue: Subscription status not updating
**Solution**: Verify PayFast notify URL is accessible:
- URL: `https://yourdomain.com/payment/notify`
- Must be publicly accessible (not localhost)
- Check firewall/server settings

### Issue: Wrong plan amount shown
**Solution**: Clear session and reload:
```php
session()->remove('plan_change_preview');
```

## PayFast Sandbox Test Cards

| Card Number | Result |
|-------------|--------|
| 4000 0000 0000 0002 | Successful payment |
| 4000 0000 0000 0010 | Failed payment |
| 4000 0000 0000 0028 | Declined payment |

## Logs to Monitor

1. **Application Logs**: `writable/logs/log-[date].log`
2. **PayFast ITN Logs**: Search for "PayFast ITN" in logs
3. **Subscription Changes**: Search for "Subscription" in logs

## Success Criteria

All tests should pass with:
- ✅ Proper redirects to PayFast
- ✅ Correct subscription status updates
- ✅ Feature access properly controlled
- ✅ Clear user messaging
- ✅ No PHP errors in logs
- ✅ Database consistency maintained

