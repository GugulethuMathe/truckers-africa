# PayFast Payment Failure Testing Guide

This guide will help you test the automatic subscription expiration when PayFast payments fail.

## Prerequisites

Before testing, ensure:
- ✅ You have a merchant account with an active subscription
- ✅ You know the merchant's subscription ID and PayFast token
- ✅ Your development environment is running (XAMPP)
- ✅ You have access to the database

---

## Method 1: Manual Database Testing (Quickest)

### Step 1: Check Current Subscription Status

```sql
-- View all subscriptions with merchant details
SELECT
    s.id as subscription_id,
    s.merchant_id,
    m.business_name,
    m.email,
    s.status,
    s.payfast_token,
    s.current_period_ends_at
FROM subscriptions s
JOIN merchants m ON m.id = s.merchant_id
WHERE s.status IN ('active', 'trial');
```

**Take note of:**
- `subscription_id`
- `merchant_id`
- `payfast_token` (will be something like "abc123xyz")

### Step 2: Verify Merchant is Visible to Drivers

1. Open browser in incognito mode
2. Go to: `http://localhost/truckers-africa/login`
3. Login as a driver (or register new driver)
4. Go to dashboard: `http://localhost/truckers-africa/dashboard/driver/`
5. **Verify the merchant appears on the map**
6. Go to routes: `http://localhost/truckers-africa/driver/routes`
7. **Verify the merchant appears as available stop**

### Step 3: Simulate PayFast Failure Notification

Open your API testing tool (Postman, Insomnia, or use curl):

```bash
curl -X POST "http://localhost/truckers-africa/payment/notify" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "merchant_id=10000100" \
  -d "merchant_key=46f0cd694581a" \
  -d "payment_status=FAILED" \
  -d "token=YOUR_PAYFAST_TOKEN_HERE" \
  -d "pf_payment_id=12345" \
  -d "m_payment_id=TEST-FAILED-PAYMENT" \
  -d "amount_gross=299.00" \
  -d "amount_fee=-6.85" \
  -d "amount_net=292.15"
```

**Replace `YOUR_PAYFAST_TOKEN_HERE` with the actual token from Step 1**

### Step 4: Check the Response

You should see a **200 OK** response. The PayFast handler doesn't return detailed JSON, but check the logs.

### Step 5: Check Logs

Open: `C:\xampplatest\htdocs\truckers-africa\writable\logs\log-YYYY-MM-DD.log`

Look for these log entries:
```
WARNING --> PayFast ITN: Payment failed or cancelled. Status: FAILED
INFO --> Subscription ID X marked as expired due to payment failure. Merchant ID: Y
INFO --> Failed transaction recorded for subscription ID: X
```

### Step 6: Verify Subscription Status Changed

```sql
-- Check if subscription status changed to expired
SELECT
    id,
    merchant_id,
    status,
    payfast_token,
    updated_at
FROM subscriptions
WHERE id = YOUR_SUBSCRIPTION_ID;
```

**Expected result:** `status` should now be `'expired'`

### Step 7: Check Failed Transaction Record

```sql
-- View the failed transaction
SELECT * FROM payment_transactions
WHERE subscription_id = YOUR_SUBSCRIPTION_ID
ORDER BY created_at DESC
LIMIT 1;
```

**Expected result:**
- `status` = 'failed'
- `payfast_payment_status` = 'FAILED'
- Recent `processed_at` timestamp

### Step 8: Verify Merchant is Hidden from Drivers

1. Go back to driver dashboard (refresh page)
2. **Merchant should no longer appear on the map**
3. Go to routes page
4. **Merchant should not be listed as available stop**
5. Try to access location directly: `http://localhost/truckers-africa/dashboard/driver/location_view/LOCATION_ID`
6. **Should show "Location not available" error**

---

## Method 2: Using Postman (Step-by-Step)

### Setup in Postman

1. **Create New Request**
   - Method: `POST`
   - URL: `http://localhost/truckers-africa/payment/notify`

2. **Headers**
   - Content-Type: `application/x-www-form-urlencoded`

3. **Body (form-data)**
   Add these key-value pairs:

   | Key | Value | Description |
   |-----|-------|-------------|
   | merchant_id | 10000100 | PayFast sandbox merchant ID |
   | merchant_key | 46f0cd694581a | PayFast sandbox key |
   | payment_status | FAILED | The failure status |
   | token | abc123xyz | Your subscription's PayFast token |
   | pf_payment_id | 12345 | PayFast payment reference |
   | m_payment_id | TEST-FAILED | Custom payment ID |
   | amount_gross | 299.00 | Amount attempted |
   | amount_fee | -6.85 | PayFast fee |
   | amount_net | 292.15 | Net amount |

4. **Send Request**
   - Click "Send"
   - Should get 200 OK response

5. **Check Results** (Follow Steps 5-8 from Method 1)

---

## Method 3: Full End-to-End PayFast Sandbox Test

### Prerequisites
- PayFast Sandbox Account: https://sandbox.payfast.co.za
- Test card details from PayFast

### Step 1: Set Up Subscription
1. Create/use a test merchant
2. Subscribe to a plan
3. Complete payment in PayFast sandbox
4. Verify subscription is active

### Step 2: Trigger Payment Failure
Unfortunately, PayFast sandbox doesn't have a direct way to trigger failures. You'll need to:

**Option A:** Wait for the recurring billing date and use a test card that declines

**Option B:** Use the manual methods above (Method 1 or 2)

---

## Method 4: Direct Database Testing (Fastest)

### Manually Update Subscription to Test Driver Visibility

```sql
-- 1. Mark subscription as expired
UPDATE subscriptions
SET status = 'expired'
WHERE id = YOUR_SUBSCRIPTION_ID;

-- 2. Check if merchant is hidden from driver queries
SELECT
    ml.id as location_id,
    ml.location_name,
    m.business_name,
    s.status as subscription_status
FROM merchant_locations ml
JOIN merchants m ON m.id = ml.merchant_id
LEFT JOIN subscriptions s ON s.merchant_id = m.id
WHERE ml.is_active = 1
  AND m.verification_status = 'approved'
  AND m.is_visible = 1
  AND (s.status = 'active' OR s.status = 'trial');
```

**Expected:** The merchant with expired subscription should NOT appear in results

---

## Quick Test Checklist

Use this checklist to verify everything works:

### Before Payment Failure
- [ ] Merchant subscription status is 'active' or 'trial'
- [ ] Merchant appears on driver dashboard map
- [ ] Merchant appears in route planning
- [ ] Merchant location can be accessed by drivers
- [ ] Merchant services are visible

### After Payment Failure (Simulated)
- [ ] API returns 200 OK
- [ ] Logs show "Payment failed or cancelled" warning
- [ ] Logs show "Subscription marked as expired"
- [ ] Logs show "Failed transaction recorded"
- [ ] Database: subscription status = 'expired'
- [ ] Database: new record in payment_transactions with status = 'failed'
- [ ] Driver dashboard: merchant NO LONGER visible
- [ ] Route planning: merchant NO LONGER listed
- [ ] Direct location URL: shows "Location not available"
- [ ] Merchant services: NOT visible to drivers

---

## Test Scenarios

### Scenario 1: First Payment Failure
```sql
-- Merchant with active subscription
-- Token: abc123
-- Expected: Status changes to expired immediately
```

### Scenario 2: No Token Provided
```bash
# Send ITN without token
curl -X POST "http://localhost/truckers-africa/payment/notify" \
  -d "payment_status=FAILED" \
  -d "pf_payment_id=12345"
```
**Expected:** Log warning "No token provided", returns 200 OK, no changes

### Scenario 3: Invalid Token
```bash
# Send ITN with non-existent token
curl -X POST "http://localhost/truckers-africa/payment/notify" \
  -d "payment_status=FAILED" \
  -d "token=invalid-token-xyz"
```
**Expected:** Log warning "Could not find subscription", returns 200 OK, no changes

### Scenario 4: Multiple Failures
```bash
# Send two failure notifications
# First call - subscription expires
# Second call - already expired, logs but no error
```
**Expected:** Both return 200 OK, second one just logs

---

## Troubleshooting

### Issue: API returns 400 Bad Request
**Cause:** ITN validation failed
**Solution:** Make sure you're using correct merchant_id and merchant_key

### Issue: Subscription status doesn't change
**Check:**
1. Is the token correct? `SELECT payfast_token FROM subscriptions WHERE id = X`
2. Are there any errors in logs? Check `writable/logs/`
3. Is the payment_status exactly 'FAILED' or 'CANCELLED'?

### Issue: Merchant still visible to drivers
**Check:**
1. Did subscription status actually change? Run SQL query
2. Clear browser cache and refresh
3. Check if there are multiple subscriptions for the merchant
4. Verify the subscription filter is working:
```sql
SELECT * FROM merchant_locations ml
JOIN merchants m ON m.id = ml.merchant_id
LEFT JOIN subscriptions s ON s.merchant_id = m.id
WHERE ml.id = YOUR_LOCATION_ID
  AND (s.status = 'active' OR s.status = 'trial');
```

### Issue: Logs show "Error handling failed payment"
**Solution:** Check the full error message in logs and ensure:
- Database connection is working
- SubscriptionModel exists
- PaymentTransactionModel exists
- Tables exist in database

---

## Useful SQL Queries

### View All Active Subscriptions
```sql
SELECT s.id, s.merchant_id, m.business_name, s.status, s.payfast_token
FROM subscriptions s
JOIN merchants m ON m.id = s.merchant_id
WHERE s.status IN ('active', 'trial');
```

### View Failed Transactions
```sql
SELECT pt.*, m.business_name, s.status as subscription_status
FROM payment_transactions pt
JOIN subscriptions s ON s.id = pt.subscription_id
JOIN merchants m ON m.id = pt.merchant_id
WHERE pt.status = 'failed'
ORDER BY pt.created_at DESC;
```

### Reset Subscription for Re-testing
```sql
-- Reset subscription back to active
UPDATE subscriptions
SET status = 'active',
    current_period_ends_at = DATE_ADD(NOW(), INTERVAL 1 MONTH)
WHERE id = YOUR_SUBSCRIPTION_ID;
```

### Check Merchant Visibility
```sql
-- This query simulates what drivers see
SELECT
    ml.id,
    ml.location_name,
    m.business_name,
    m.id as merchant_id,
    s.status as subscription_status,
    s.payfast_token
FROM merchant_locations ml
JOIN merchants m ON m.id = ml.merchant_id
LEFT JOIN subscriptions s ON s.merchant_id = m.id
WHERE ml.is_active = 1
  AND m.verification_status = 'approved'
  AND m.is_visible = 1
  AND (s.status = 'active' OR s.status = 'trial');
```

---

## Expected Log Output (Success)

```
INFO - YYYY-MM-DD HH:MM:SS --> PayFast ITN received.
INFO - YYYY-MM-DD HH:MM:SS --> PayFast ITN Data: {"payment_status":"FAILED","token":"abc123",...}
INFO - YYYY-MM-DD HH:MM:SS --> PayFast ITN Validation Response: VALID
WARNING - YYYY-MM-DD HH:MM:SS --> PayFast ITN: Payment failed or cancelled. Status: FAILED
INFO - YYYY-MM-DD HH:MM:SS --> Subscription ID 21 marked as expired due to payment failure. Merchant ID: 21
INFO - YYYY-MM-DD HH:MM:SS --> Failed transaction recorded for subscription ID: 21
```

---

## Production Considerations

When testing in production:
1. Use PayFast production credentials
2. Monitor logs closely: `writable/logs/log-YYYY-MM-DD.log`
3. Set up email alerts for failed payments
4. Have a merchant recovery process in place
5. Consider grace period before hiding merchant (optional enhancement)

---

## Next Steps After Testing

Once you've confirmed it works:

1. ✅ Monitor logs for real payment failures
2. ✅ Set up email notifications to merchants when payments fail
3. ✅ Create admin dashboard to view failed transactions
4. ✅ Document merchant recovery process
5. ✅ Consider adding grace period (e.g., 3 days before marking expired)

---

## Support

If you encounter issues:
1. Check logs in `writable/logs/`
2. Verify database queries return expected results
3. Test with different payment statuses (FAILED, CANCELLED)
4. Ensure all models exist and are properly namespaced
