# PayFast Localhost Testing Fix

## Problem

When testing PayFast payments on localhost, the subscription status remains `'trial_pending'` or `'new'` even after completing payment on PayFast sandbox. This happens because:

1. **PayFast ITN (Instant Transaction Notification) cannot reach localhost**
   - PayFast sends ITN callbacks to the `notify_url` to confirm payment
   - The `notify_url` is set to `http://localhost/truckers-africa/payment/notify`
   - PayFast servers cannot access localhost URLs
   - Without ITN, the subscription status is never updated

2. **The ITN handler is responsible for updating subscription status**
   - `Payment::notify()` method updates status from `'trial_pending'` → `'trial'`
   - Or from `'new'` → `'active'`
   - This never runs on localhost

## Solutions Implemented

### Solution 1: Auto-Activate on Success Page (Recommended for Testing)

**File**: `app/Controllers/Payment.php` - `success()` method

When merchant returns to the success page after payment, the system now automatically:
1. Checks if subscription is in `'new'` or `'trial_pending'` status
2. Updates status appropriately:
   - `'trial_pending'` → `'trial'` (starts trial period)
   - `'new'` → `'active'` (activates paid subscription)
3. Sets billing period dates
4. Marks onboarding as complete

**How to Use**:
- Just complete payment on PayFast sandbox
- Click "Return to Merchant" or wait for auto-redirect
- Subscription will be automatically activated

### Solution 2: Manual Test Endpoint (For Development Only)

**Endpoint**: `GET /payment/test-activate` or `GET /payment/test-activate/{merchantId}`

A test endpoint that simulates what PayFast ITN would do in production.

**Features**:
- Only works in development environment (`ENVIRONMENT = 'development'`)
- Activates subscription for logged-in merchant or specified merchant ID
- Updates status from `'new'`/`'trial_pending'` to `'active'`/`'trial'`
- Sets billing period dates
- Adds fake PayFast token for testing
- Returns JSON response with details

**How to Use**:

1. **For logged-in merchant**:
   ```
   http://localhost/truckers-africa/payment/test-activate
   ```

2. **For specific merchant ID**:
   ```
   http://localhost/truckers-africa/payment/test-activate/123
   ```

**Response Example**:
```json
{
  "success": true,
  "message": "Subscription activated successfully",
  "subscription": {
    "id": 45,
    "old_status": "trial_pending",
    "new_status": "trial",
    "merchant_id": 123
  }
}
```

**Error Responses**:
- Not in development mode: 403 Forbidden
- No merchant ID: 400 Bad Request
- No subscription found: 404 Not Found
- Already active: 400 Bad Request

## Testing Workflow

### Option A: Using Auto-Activation (Easiest)

1. Register merchant and select plan
2. Complete payment on PayFast sandbox
3. Click "Return to Merchant" button
4. ✅ Subscription automatically activated
5. Dashboard shows active subscription

### Option B: Using Test Endpoint

1. Register merchant and select plan
2. Complete payment on PayFast sandbox (or skip it)
3. Open new browser tab
4. Navigate to: `http://localhost/truckers-africa/payment/test-activate`
5. ✅ Subscription manually activated
6. Refresh dashboard to see active subscription

### Option C: Direct Database Update (Last Resort)

If both methods fail, update database directly:

```sql
-- For trial subscriptions
UPDATE subscriptions 
SET status = 'trial',
    current_period_starts_at = NOW(),
    current_period_ends_at = DATE_ADD(NOW(), INTERVAL 1 MONTH),
    payfast_token = 'TEST-TOKEN-123'
WHERE merchant_id = [merchant_id] AND status = 'trial_pending';

-- For paid subscriptions
UPDATE subscriptions 
SET status = 'active',
    trial_ends_at = NULL,
    current_period_starts_at = NOW(),
    current_period_ends_at = DATE_ADD(NOW(), INTERVAL 1 MONTH),
    payfast_token = 'TEST-TOKEN-123'
WHERE merchant_id = [merchant_id] AND status = 'new';

-- Mark onboarding complete
UPDATE merchants 
SET onboarding_completed = 1 
WHERE id = [merchant_id];
```

## Production Deployment

### IMPORTANT: Remove Test Endpoint Before Production!

The `testActivateSubscription()` method should be:
1. **Removed entirely**, OR
2. **Secured with authentication**, OR
3. **Kept with environment check** (already implemented)

The auto-activation in `success()` method should also be removed or made conditional:

```php
// Only auto-activate on localhost
if (ENVIRONMENT === 'development' && $subscription && in_array($subscription['status'], ['new', 'trial_pending'])) {
    // Auto-activation code...
}
```

### Production Setup Requirements

For PayFast to work properly in production:

1. **Public Domain Required**
   - Deploy to a public server with a domain name
   - Example: `https://truckersafrica.com`

2. **Update notify_url**
   - Must be publicly accessible
   - Example: `https://truckersafrica.com/payment/notify`

3. **SSL Certificate Required**
   - PayFast requires HTTPS for ITN callbacks
   - Use Let's Encrypt or commercial SSL

4. **Update PayFast Credentials**
   - Switch from sandbox to production credentials in `.env`
   - Update merchant ID, merchant key, passphrase
   - Update process URL to production PayFast

5. **Test ITN Endpoint**
   - Use PayFast's ITN testing tool
   - Verify ITN callbacks are received
   - Check logs: `writable/logs/log-*.log`

## Verifying Subscription Activation

### Check Database:
```sql
SELECT id, merchant_id, plan_id, status, 
       current_period_starts_at, current_period_ends_at, 
       trial_ends_at, payfast_token
FROM subscriptions 
WHERE merchant_id = [merchant_id];
```

**Expected for Trial**:
- `status = 'trial'`
- `current_period_starts_at` and `current_period_ends_at` set
- `trial_ends_at` set to future date
- `payfast_token` populated

**Expected for Paid**:
- `status = 'active'`
- `current_period_starts_at` and `current_period_ends_at` set
- `trial_ends_at = NULL`
- `payfast_token` populated

### Check Dashboard:
- No payment required alert
- Subscription status shows "Active" or "X-Day Free Trial"
- Can access all paid features

## Troubleshooting

### Issue: Auto-activation not working
**Solution**: Clear browser cache and session, try again

### Issue: Test endpoint returns 403
**Solution**: Check `.env` file has `CI_ENVIRONMENT = development`

### Issue: Still showing payment required
**Solution**: 
1. Check database subscription status
2. Clear session: `session()->destroy()`
3. Log out and log back in

### Issue: Features still blocked
**Solution**: Check `SubscriptionFilter` - it blocks these statuses:
- `'expired'`, `'past_due'`, `'cancelled'`, `'new'`, `'trial_pending'`

Make sure status is `'trial'` or `'active'`

## Files Modified

1. `app/Controllers/Payment.php`
   - Added auto-activation in `success()` method
   - Added `testActivateSubscription()` method

2. `app/Config/Routes.php`
   - Added routes for test activation endpoint

