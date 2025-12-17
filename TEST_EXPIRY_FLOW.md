# Testing Subscription Expiry Flow

This guide walks you through testing the complete subscription expiry and branch deactivation flow.

---

## Test 1: Manual Cron Command Execution

### **Step 1: SSH into Server**

```bash
ssh tlouelea@truckersafrica.com
```

### **Step 2: Navigate to Project Directory**

```bash
cd /home/tlouelea/domains/truckersafrica.com/public_html
```

### **Step 3: Run the Command**

```bash
php spark subscription:process-expired
```

### **Expected Output (No Expired Subscriptions):**

```
Starting expired subscription processing...

Checking for cancelled subscriptions that have reached their end date...
No expired subscriptions found.

Expired subscription processing completed successfully!
```

✅ **If you see this:** The command works correctly!

---

## Test 2: Complete Expiry Flow (Web Interface)

### **Step 1: Login as Merchant**

1. Go to: `https://www.truckersafrica.com/login`
2. Login with a merchant account that has:
   - Active subscription
   - Multiple branch locations
   - Branch users created

### **Step 2: Verify Current Status**

Before cancelling, check:

**Dashboard:** `https://www.truckersafrica.com/merchant/dashboard`
- ✅ Subscription status should show "Active" or "Trial"
- ✅ Note the current plan name

**Locations:** `https://www.truckersafrica.com/merchant/locations`
- ✅ Count how many branch locations are active
- ✅ Note the branch user emails

### **Step 3: Test Branch User Login (Before Cancellation)**

1. Open incognito/private window
2. Go to: `https://www.truckersafrica.com/branch/login`
3. Login with a branch user account
4. ✅ Should login successfully
5. Logout

### **Step 4: Cancel Subscription**

1. Go to: `https://www.truckersafrica.com/merchant/subscription`
2. Click **"Cancel Subscription"** button
3. Fill out the cancellation form:
   - **Reason:** Select any reason (e.g., "Too expensive")
   - **Comments:** Optional
   - **Confirmation:** Check the box
4. Click **"Cancel My Subscription"**

### **Step 5: Verify Cancellation Message**

You should see a success message like:

```
Your subscription has been cancelled. You will continue to have full access 
(including all branch locations) until [DATE]. After this date, all branch 
locations will be deactivated. We're sorry to see you go!
```

✅ **Verify:**
- Message mentions "full access"
- Message mentions "branch locations will be deactivated"
- Shows specific expiry date

### **Step 6: Verify Grace Period (Branches Still Active)**

**Test Branch User Login Again:**
1. Go to: `https://www.truckersafrica.com/branch/login`
2. Login with same branch user
3. ✅ Should STILL work (grace period active)

**Check Subscription Status:**
- Go to: `https://www.truckersafrica.com/merchant/subscription`
- Status should show: **"Cancelled"**
- Should show: "Access until [DATE]"

### **Step 7: Simulate Expiry**

Now let's simulate the subscription expiring:

1. Go to: `https://www.truckersafrica.com/merchant/subscription/test-expiry`
2. You should see a success message like:

```
✅ TEST: Subscription expired successfully! 
Deactivated 3 branch user(s) and 3 location(s). 
Status changed from 'cancelled' to 'expired'.
```

✅ **Note the numbers:** How many branches and locations were deactivated

### **Step 8: Verify Branches Are Deactivated**

**Test Branch User Login (Should Fail):**
1. Go to: `https://www.truckersafrica.com/branch/login`
2. Try to login with branch user
3. ❌ Should show error: "Invalid credentials" or "Account inactive"

**Check Merchant Dashboard:**
- Go to: `https://www.truckersafrica.com/merchant/dashboard`
- Subscription status should show: **"Expired"** or **"Previous Plan"**

**Check Locations Page:**
- Go to: `https://www.truckersafrica.com/merchant/locations`
- Branch locations should show as **"Inactive"**

### **Step 9: Verify in Database (Optional)**

If you have database access:

```sql
-- Check subscription status
SELECT id, merchant_id, status, current_period_ends_at 
FROM subscriptions 
WHERE merchant_id = YOUR_MERCHANT_ID;

-- Check branch users
SELECT id, email, is_active 
FROM branch_users 
WHERE merchant_id = YOUR_MERCHANT_ID;

-- Check locations
SELECT id, location_name, is_active, is_primary 
FROM merchant_locations 
WHERE merchant_id = YOUR_MERCHANT_ID;
```

✅ **Expected Results:**
- `subscriptions.status` = `'expired'`
- `branch_users.is_active` = `0` (for all branch users)
- `merchant_locations.is_active` = `0` (for non-primary locations)
- `merchant_locations.is_active` = `1` (for primary location only)

---

## Test 3: Reactivation Flow

### **Step 1: Select New Plan**

1. Go to: `https://www.truckersafrica.com/merchant/subscription/plans`
2. Select a plan (e.g., "Plus Plan")
3. Click **"Subscribe Now"**

### **Step 2: Complete Payment**

1. You'll be redirected to PayFast
2. Use sandbox credentials to complete payment
3. After payment success, you should be redirected

### **Step 3: Branch Selection Page**

After payment, you should see:

**Page Title:** "Activate Your Branches"

**Content:**
- Shows your plan name and location limit
- Lists all inactive branches with checkboxes
- Shows branch manager details
- Counter showing selected vs. limit

### **Step 4: Select Branches to Activate**

1. Check the boxes for branches you want to activate
2. ✅ Try selecting more than your plan allows
   - Should disable remaining checkboxes
   - Should show warning: "You've reached your plan limit"
3. Select valid number of branches
4. Click **"Activate Selected Branches"**

### **Step 5: Verify Reactivation**

**Check Success Message:**
```
Successfully activated 2 branch(es)!
```

**Test Branch User Login:**
1. Go to: `https://www.truckersafrica.com/branch/login`
2. Login with activated branch user
3. ✅ Should work now!

**Check Locations Page:**
- Activated branches should show as **"Active"**
- Non-activated branches should remain **"Inactive"**

---

## Test 4: Cron Job with Real Data

### **Step 1: Create Test Scenario**

1. Login as merchant
2. Cancel subscription
3. **Don't use test-expiry yet**

### **Step 2: Manually Set Expiry Date (Database)**

Using phpMyAdmin or SQL:

```sql
-- Set expiry date to yesterday
UPDATE subscriptions 
SET current_period_ends_at = DATE_SUB(NOW(), INTERVAL 1 DAY)
WHERE merchant_id = YOUR_MERCHANT_ID 
AND status = 'cancelled';
```

### **Step 3: Run Cron Command**

SSH into server:

```bash
cd /home/tlouelea/domains/truckersafrica.com/public_html
php spark subscription:process-expired
```

### **Expected Output:**

```
Starting expired subscription processing...

Checking for cancelled subscriptions that have reached their end date...
Found 1 expired subscription(s). Processing...

Processing merchant ID: 15
  ✓ Deactivated 3 branch user(s)
  ✓ Deactivated 3 location(s)

═══════════════════════════════════════
SUMMARY
═══════════════════════════════════════
Merchants processed: 1
Total branches deactivated: 3
Total locations deactivated: 3
═══════════════════════════════════════

Expired subscription processing completed successfully!
```

### **Step 4: Verify Results**

1. ✅ Subscription status changed to `expired`
2. ✅ Branch users cannot login
3. ✅ Locations marked as inactive
4. ✅ Check application logs for entries

---

## Test Results Checklist

### ✅ Test 1: Manual Command
- [ ] Command runs without errors
- [ ] Shows "No expired subscriptions found" (if none exist)
- [ ] No PHP errors or warnings

### ✅ Test 2: Complete Flow
- [ ] Cancellation message shows grace period
- [ ] Branches work during grace period
- [ ] Test expiry deactivates branches
- [ ] Branch users cannot login after expiry
- [ ] Subscription status shows "Expired"

### ✅ Test 3: Reactivation
- [ ] Payment redirects to branch selection
- [ ] Plan limits enforced correctly
- [ ] Selected branches activate successfully
- [ ] Branch users can login again

### ✅ Test 4: Real Cron Execution
- [ ] Cron finds expired subscriptions
- [ ] Processes them correctly
- [ ] Deactivates branches automatically
- [ ] Logs actions properly

---

## Troubleshooting

### Issue: "Command not found"
**Solution:** Use full PHP path:
```bash
/usr/bin/php spark subscription:process-expired
```

### Issue: "Permission denied"
**Solution:** Make spark executable:
```bash
chmod +x spark
```

### Issue: Test expiry shows "No subscription found"
**Solution:** Make sure you're logged in as merchant with cancelled subscription

### Issue: Branch user can still login after expiry
**Solution:** Check database - `branch_users.is_active` should be 0

---

## Next Steps After Testing

1. ✅ All tests pass
2. ✅ Update cron job to include logging:
   ```bash
   0 2 * * * php /home/tlouelea/domains/truckersafrica.com/public_html/spark subscription:process-expired >> /home/tlouelea/logs/cron-expiry.log 2>&1
   ```
3. ✅ Monitor logs for first week
4. ✅ Document any issues found

---

## Support

If any test fails, check:
1. Application logs: `writable/logs/log-*.log`
2. PHP error logs
3. Database records
4. File permissions

