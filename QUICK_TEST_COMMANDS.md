# Quick Test Commands - Subscription Expiry

## 🚀 Quick Start Testing

### 1. Test Cron Command (SSH)

```bash
cd /home/tlouelea/domains/truckersafrica.com/public_html
php spark subscription:process-expired
```

---

### 2. Test URLs (Browser)

**Cancel Subscription:**
```
https://www.truckersafrica.com/merchant/subscription
```

**Simulate Expiry (Test Only):**
```
https://www.truckersafrica.com/merchant/subscription/test-expiry
```

**Branch Login:**
```
https://www.truckersafrica.com/branch/login
```

**Reactivate Subscription:**
```
https://www.truckersafrica.com/merchant/subscription/plans
```

**Branch Selection (After Reactivation):**
```
https://www.truckersafrica.com/merchant/subscription/select-branches
```

---

### 3. Database Queries (phpMyAdmin)

**Check Subscription Status:**
```sql
SELECT id, merchant_id, status, current_period_ends_at 
FROM subscriptions 
WHERE status IN ('cancelled', 'expired')
ORDER BY updated_at DESC 
LIMIT 10;
```

**Check Branch Users:**
```sql
SELECT id, merchant_id, email, is_active 
FROM branch_users 
ORDER BY updated_at DESC 
LIMIT 10;
```

**Check Locations:**
```sql
SELECT id, merchant_id, location_name, is_active, is_primary 
FROM merchant_locations 
ORDER BY updated_at DESC 
LIMIT 10;
```

**Manually Expire a Subscription (For Testing):**
```sql
-- Set expiry date to yesterday
UPDATE subscriptions 
SET current_period_ends_at = DATE_SUB(NOW(), INTERVAL 1 DAY)
WHERE id = YOUR_SUBSCRIPTION_ID;
```

---

### 4. Check Logs

**Cron Log:**
```bash
tail -f /home/tlouelea/logs/cron-expiry.log
```

**Application Log:**
```bash
tail -f /home/tlouelea/domains/truckersafrica.com/public_html/writable/logs/log-$(date +%Y-%m-%d).log
```

**Search for Expiry Events:**
```bash
grep "Expired subscription" /home/tlouelea/domains/truckersafrica.com/public_html/writable/logs/log-*.log
```

---

## 📋 Testing Checklist

### Quick Test (5 minutes)

- [ ] Run: `php spark subscription:process-expired`
- [ ] Visit: `/merchant/subscription/test-expiry`
- [ ] Try branch login (should fail)
- [ ] Check database: `branch_users.is_active = 0`

### Full Test (15 minutes)

- [ ] Login as merchant
- [ ] Cancel subscription
- [ ] Verify grace period message
- [ ] Test branch login (should work)
- [ ] Run test-expiry
- [ ] Test branch login (should fail)
- [ ] Reactivate subscription
- [ ] Select branches to activate
- [ ] Test branch login (should work)

---

## 🔧 Troubleshooting Commands

**Check PHP Version:**
```bash
php -v
```

**Check Spark Commands:**
```bash
php spark list
```

**Test Database Connection:**
```bash
php spark db:table subscriptions
```

**Check File Permissions:**
```bash
ls -la spark
```

**Make Spark Executable:**
```bash
chmod +x spark
```

---

## 📊 Expected Results

### After Cancellation:
- ✅ Status: `cancelled`
- ✅ Branches: Active (grace period)
- ✅ Message: "Access until [DATE]"

### After Expiry:
- ✅ Status: `expired`
- ✅ Branches: Inactive
- ✅ Branch login: Fails

### After Reactivation:
- ✅ Status: `active` or `trial`
- ✅ Selected branches: Active
- ✅ Branch login: Works

---

## 🎯 One-Line Test

Test everything in one command:

```bash
cd /home/tlouelea/domains/truckersafrica.com/public_html && php spark subscription:process-expired && tail -20 writable/logs/log-$(date +%Y-%m-%d).log
```

This will:
1. Navigate to project directory
2. Run the expiry command
3. Show last 20 lines of today's log

---

## 📞 Quick Support

**Issue:** Command not found  
**Fix:** Use full path: `/usr/bin/php spark subscription:process-expired`

**Issue:** Permission denied  
**Fix:** `chmod +x spark`

**Issue:** No output  
**Fix:** Check logs: `tail -f writable/logs/log-*.log`

**Issue:** Branch still active  
**Fix:** Check database: `SELECT * FROM branch_users WHERE merchant_id = X`

