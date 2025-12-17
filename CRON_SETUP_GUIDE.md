# Cron Job Setup Guide - Truckers Africa

This guide explains how to set up automated cron jobs for the Truckers Africa platform.

## 1. Process Expired Subscriptions

This cron job automatically deactivates branches when subscriptions expire.

### Production Server Setup (truckersafrica.com)

#### **Option A: Direct PHP CLI Execution (Recommended)**

**Cron Command:**
```bash
0 2 * * * php /home/tlouelea/domains/truckersafrica.com/public_html/spark subscription:process-expired >> /home/tlouelea/domains/truckersafrica.com/logs/cron-expiry.log 2>&1
```

**Schedule:** Daily at 2:00 AM  
**Log File:** `/home/tlouelea/domains/truckersafrica.com/logs/cron-expiry.log`

---

#### **Option B: HTTP Endpoint (If CLI not available)**

**Step 1: Generate a Secret Token**

Generate a random token (use this command or any random string generator):
```bash
php -r "echo bin2hex(random_bytes(32));"
```

Example output: `a7f3c9e2b8d4f1a6c5e9b2d8f4a1c7e3b9d5f2a8c6e1b4d7f3a9c5e2b8d4f1a6`

**Step 2: Update the Token**

Edit `public/cron/process-expired-subscriptions.php` and replace:
```php
$cronSecret = 'YOUR_SECRET_TOKEN_HERE';
```

With your generated token:
```php
$cronSecret = 'a7f3c9e2b8d4f1a6c5e9b2d8f4a1c7e3b9d5f2a8c6e1b4d7f3a9c5e2b8d4f1a6';
```

**Step 3: Set Up Cron Job**

**Using curl:**
```bash
0 2 * * * curl --silent "https://www.truckersafrica.com/cron/process-expired-subscriptions.php?token=YOUR_TOKEN_HERE" >> /home/tlouelea/domains/truckersafrica.com/logs/cron-expiry.log 2>&1
```

**Using wget:**
```bash
0 2 * * * wget -O - -q "https://www.truckersafrica.com/cron/process-expired-subscriptions.php?token=YOUR_TOKEN_HERE" >> /home/tlouelea/domains/truckersafrica.com/logs/cron-expiry.log 2>&1
```

**Replace `YOUR_TOKEN_HERE` with your actual token!**

---

### cPanel Setup Instructions

1. **Login to cPanel**
2. **Navigate to:** Advanced → Cron Jobs
3. **Common Settings:** Once Per Day (0 2 * * *)
4. **Command:** Choose one of the options above

**Example for cPanel:**
```
0 2 * * * php /home/tlouelea/domains/truckersafrica.com/public_html/spark subscription:process-expired
```

---

### Testing the Cron Job

#### **Test via SSH:**
```bash
cd /home/tlouelea/domains/truckersafrica.com/public_html
php spark subscription:process-expired
```

#### **Test via Browser (HTTP endpoint):**
```
https://www.truckersafrica.com/cron/process-expired-subscriptions.php?token=YOUR_TOKEN_HERE
```

You should see output like:
```
Starting expired subscription processing...
Time: 2025-11-19 02:00:00

Checking for cancelled subscriptions that have reached their end date...
Found 2 expired subscription(s). Processing...

Processing merchant ID: 15
  ✓ Deactivated 3 branch user(s)
  ✓ Deactivated 3 location(s)

═══════════════════════════════════════
SUMMARY
═══════════════════════════════════════
Merchants processed: 2
Total branches deactivated: 3
Total locations deactivated: 3
═══════════════════════════════════════

Expired subscription processing completed successfully!
```

---

## 2. Other Existing Cron Jobs

### Currency Exchange Rate Updates

**Command:**
```bash
0 3 * * * php /home/tlouelea/domains/truckersafrica.com/public_html/spark UpdateExchangeRates
```

**Schedule:** Daily at 3:00 AM

---

### Subscription Expiry Reminders

**Command:**
```bash
0 9 * * * php /home/tlouelea/domains/truckersafrica.com/public_html/spark subscription:reminders
```

**Schedule:** Daily at 9:00 AM

---

## Complete Cron Job List

Add all these to your cPanel cron jobs:

```bash
# Process expired subscriptions (deactivate branches)
0 2 * * * php /home/tlouelea/domains/truckersafrica.com/public_html/spark subscription:process-expired >> /home/tlouelea/logs/cron-expiry.log 2>&1

# Update currency exchange rates
0 3 * * * php /home/tlouelea/domains/truckersafrica.com/public_html/spark UpdateExchangeRates >> /home/tlouelea/logs/cron-currency.log 2>&1

# Send subscription expiry reminders
0 9 * * * php /home/tlouelea/domains/truckersafrica.com/public_html/spark subscription:reminders >> /home/tlouelea/logs/cron-reminders.log 2>&1
```

---

## Monitoring Cron Jobs

### Check Logs

**Expiry Processing:**
```bash
tail -f /home/tlouelea/domains/truckersafrica.com/logs/cron-expiry.log
```

**Application Logs:**
```bash
tail -f /home/tlouelea/domains/truckersafrica.com/public_html/writable/logs/log-*.log
```

### Verify Cron is Running

Check if cron executed:
```bash
grep CRON /var/log/syslog
```

---

## Troubleshooting

### Issue: "Command not found"

**Solution:** Use full path to PHP:
```bash
/usr/bin/php /home/tlouelea/domains/truckersafrica.com/public_html/spark subscription:process-expired
```

### Issue: "Permission denied"

**Solution:** Make spark executable:
```bash
chmod +x /home/tlouelea/domains/truckersafrica.com/public_html/spark
```

### Issue: HTTP endpoint returns 403

**Solution:** Check that the token in the URL matches the token in the PHP file.

### Issue: No output in logs

**Solution:** Ensure log directory exists and is writable:
```bash
mkdir -p /home/tlouelea/domains/truckersafrica.com/logs
chmod 755 /home/tlouelea/domains/truckersafrica.com/logs
```

---

## Security Notes

1. **Never share your cron token publicly**
2. **Use HTTPS for HTTP endpoints**
3. **Rotate tokens periodically (every 6 months)**
4. **Monitor logs for unauthorized access attempts**
5. **Keep log files outside public_html directory**

---

## Support

If you encounter issues:
1. Check the log files first
2. Test the command manually via SSH
3. Verify file permissions
4. Check PHP version compatibility (requires PHP 7.4+)

