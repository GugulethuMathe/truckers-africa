# Server Upload Guide - Fix MySQL DEFINER Error

## Problem
When importing the database to your server's phpMyAdmin, you get this error:
```
#1227 - Access denied; you need (at least one of) the SUPER privilege(s) for this operation
```

This happens because the SQL dump contains `DEFINER` clauses in VIEWs, TRIGGERs, PROCEDUREs, etc., which require SUPER privileges that shared hosting providers don't provide.

## Solution Options

### Option 1: Fix SQL Dump Before Upload (RECOMMENDED)

**Step 1:** Export your database
```bash
# Using phpMyAdmin on localhost
# Export -> Custom -> Format: SQL
# Make sure to include VIEWs, TRIGGERs, etc.
```

**Step 2:** Run the fix script
```bash
cd C:\xampplatest\htdocs\truckers-africa
php clean_sql_for_server.php
```

This will:
- Read `truckers_africa_database.sql`
- Remove all DEFINER clauses and SQL SECURITY DEFINER
- Create a new file named `truckers_africa_database_clean.sql`

**Step 3:** Upload the fixed SQL file to your server via phpMyAdmin

---

### Option 2: Manual Fix (Quick Method)

**Step 1:** Open your SQL dump file in a text editor (Notepad++, VS Code, etc.)

**Step 2:** Find and replace using regex:

**Find:**
```
DEFINER=`root`@`localhost`
```

**Replace with:** (empty/nothing)

**Step 3:** Save the file and upload to server

---

### Option 3: Fix Existing Server Database

If you've already partially imported the database, run this SQL script in phpMyAdmin on your server:

```sql
-- Copy the content from fix_server_database.sql
-- Paste it in phpMyAdmin SQL tab
-- Click "Go"
```

Or directly in phpMyAdmin:
1. Open the SQL tab
2. Paste the contents of `fix_server_database.sql`
3. Click "Go"

---

## Complete Upload Checklist

### 1. Prepare Database Dump
- [ ] Export database from localhost
- [ ] Run `php clean_sql_for_server.php` OR manually remove DEFINER clauses
- [ ] Verify the fixed SQL file has no DEFINER errors

### 2. Prepare Files
- [ ] Zip your project files: `app/`, `public/`, `writable/`, etc.
- [ ] Exclude: `vendor/`, `.git/`, `writable/cache/*`, `writable/logs/*`

### 3. Upload to Server
- [ ] Upload files via FTP/cPanel File Manager
- [ ] Extract files on server
- [ ] Create database on server (via cPanel/phpMyAdmin)
- [ ] Import the FIXED SQL file

### 4. Configure Environment
- [ ] Copy `.env` file to server
- [ ] Update database credentials in `.env`:
```ini
database.default.hostname = localhost
database.default.database = your_server_database_name
database.default.username = your_server_username
database.default.password = your_server_password
```
- [ ] Update `app.baseURL` in `.env`:
```ini
app.baseURL = 'https://yourdomain.com/'
```
- [ ] Set environment to production:
```ini
CI_ENVIRONMENT = production
```

### 5. Set Permissions
Run these commands via SSH or cPanel Terminal:
```bash
chmod -R 755 public/
chmod -R 775 writable/
chmod -R 775 writable/cache/
chmod -R 775 writable/logs/
chmod -R 775 writable/session/
chmod -R 775 public/uploads/
```

### 6. Install Composer Dependencies
Via SSH:
```bash
cd /path/to/your/project
composer install --no-dev --optimize-autoloader
```

Or upload the `vendor/` folder if SSH access is not available.

### 7. Test the Site
- [ ] Visit your domain
- [ ] Test login (driver, merchant, admin)
- [ ] Test image uploads
- [ ] Test order creation
- [ ] Check email notifications (update SMTP settings if needed)

---

## Common Issues After Upload

### Issue 1: 500 Internal Server Error
**Solution:** Check `.htaccess` file exists in public folder:
```apache
# Copy from public/.htaccess
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php/$1 [L]
```

### Issue 2: Images Not Showing
**Solution:**
- Verify `public/uploads/` folder exists
- Check folder permissions (755 or 775)
- Update image paths if needed

### Issue 3: Session Issues
**Solution:**
- Clear browser cache
- Check `writable/session/` permissions (775)
- Update session configuration in `app/Config/Session.php` if using file-based sessions

### Issue 4: Email Not Sending
**Solution:**
- Update SMTP settings in `.env`
- Use server's mail settings (check with hosting provider)
- Test with a simple PHP mail script first

---

## Security Checklist for Production

- [ ] Change all default passwords
- [ ] Set `CI_ENVIRONMENT = production`
- [ ] Disable error display in production
- [ ] Enable HTTPS/SSL
- [ ] Update PayFast to production credentials
- [ ] Backup database regularly
- [ ] Set up monitoring/logging

---

## Files Created to Help You

1. **clean_sql_for_server.php** - Automated script to fix SQL dumps
2. **truckers_africa_database_clean.sql** - Cleaned SQL file ready for upload
3. **fix_server_database.sql** - SQL to fix already imported database (if needed)
4. **SERVER_UPLOAD_GUIDE.md** - This complete guide

---

## Need Help?

If you encounter issues:
1. Check error logs: `writable/logs/log-{date}.log`
2. Enable debug mode temporarily: `CI_ENVIRONMENT = development`
3. Check server error logs (cPanel > Error Log)
4. Contact your hosting provider for permission issues

---

## Quick Command Reference

```bash
# Fix SQL dump
php clean_sql_for_server.php

# Verify no DEFINER clauses remain
grep -i "DEFINER" truckers_africa_database_clean.sql

# Set permissions (via SSH)
find public/ -type f -exec chmod 644 {} \;
find public/ -type d -exec chmod 755 {} \;
find writable/ -type f -exec chmod 664 {} \;
find writable/ -type d -exec chmod 775 {} \;

# Install dependencies
composer install --no-dev --optimize-autoloader

# Clear cache
php spark cache:clear
```

Good luck with your deployment! 🚀
