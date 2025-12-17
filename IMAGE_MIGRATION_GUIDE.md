# Image Migration Guide
**Moving from `public/uploads/` to `uploads/`**

## Overview
This guide explains how to migrate all images from `public/uploads/` to `uploads/` at the web root.

### Why This Change?
- **Simpler paths**: All images at `https://truckersafrica.com/uploads/` instead of `https://truckersafrica.com/public/uploads/`
- **Consistent structure**: Same path works on localhost and production
- **Database consistency**: All paths stored as `uploads/folder/file.jpg`

---

## Step 1: Update Code (COMPLETED ✅)

All controllers and views have been updated to use `uploads/` without the `public/` prefix.

### Files Modified:
1. **app/Helpers/image_helper.php** - Updated to remove `public/` prefix
2. **app/Controllers/BranchDashboard.php** - Upload paths updated
3. **app/Controllers/MerchantListingRequests.php** - Copy paths updated
4. **app/Controllers/MerchantListingsController.php** - Comments updated
5. **app/Views/driver/services.php** - Uses helper function
6. **app/Views/driver/service_view.php** - Uses helper function
7. **app/Views/driver/merchant_profile.php** - Uses helper function
8. **app/Views/driver/location_view.php** - Uses helper function
9. **app/Views/merchant/listings/form.php** - Uses helper function

---

## Step 2: Move Images on Server

### On Production Server (truckersafrica.com):

```bash
# SSH into your server
ssh your-user@truckersafrica.com

# Navigate to public_html
cd /home/tlouelea/domains/truckersafrica.com/public_html

# Create uploads directory if it doesn't exist
mkdir -p uploads

# Move all images from public/uploads to uploads
# This command moves the entire uploads folder contents
cp -r public/uploads/* uploads/

# Verify files were copied
ls -la uploads/listings/
ls -la uploads/listing-requests/
ls -la uploads/merchant_profiles/
ls -la uploads/driver_profiles/

# After verifying, you can remove the old location (OPTIONAL - do this later after testing)
# rm -rf public/uploads
```

### Directory Structure After Migration:
```
public_html/
├── uploads/                          ← NEW LOCATION
│   ├── listings/                     ← Images accessible at /uploads/listings/
│   ├── listing-requests/
│   ├── merchant_profiles/
│   ├── driver_profiles/
│   └── merchant_documents/
├── public/
│   ├── assets/
│   ├── index.php
│   └── uploads/                      ← OLD LOCATION (can be removed after testing)
├── app/
└── ...
```

---

## Step 3: Update Database

### Run SQL Script:

1. **Backup your database first!**
```bash
mysqldump -u username -p database_name > backup_before_migration.sql
```

2. **Run the update script:**
```bash
mysql -u username -p database_name < UPDATE_IMAGE_PATHS.sql
```

### What the SQL Script Does:
- Updates `merchant_listings.main_image_path`
- Updates `merchant_listing_images.image_path`
- Updates `listing_requests.main_image`
- Updates `listing_requests.gallery_images` (JSON field)
- Updates `merchants.profile_image_path`
- Updates `merchants.business_image_path`
- Updates `truck_drivers.profile_picture`

### Example Changes:
```
BEFORE: public/uploads/listings/1762518267_a008d9b025602c0b2176.png
AFTER:  uploads/listings/1762518267_a008d9b025602c0b2176.png
```

---

## Step 4: Upload Updated Code

Upload these files to the server:

1. `app/Config/Autoload.php`
2. `app/Helpers/image_helper.php`
3. `app/Controllers/BranchDashboard.php`
4. `app/Controllers/MerchantListingRequests.php`
5. `app/Controllers/MerchantListingsController.php`
6. `app/Views/driver/services.php`
7. `app/Views/driver/service_view.php`
8. `app/Views/driver/merchant_profile.php`
9. `app/Views/driver/location_view.php`
10. `app/Views/merchant/listings/form.php`

---

## Step 5: Testing

### Test These URLs:

1. **Driver Services List**
   - Visit: `https://truckersafrica.com/driver/services`
   - Check: All listing images display (primary and branch)

2. **Driver Service Detail**
   - Visit: `https://truckersafrica.com/driver/service/41` (primary)
   - Visit: `https://truckersafrica.com/driver/service/42` (branch)
   - Check: Main image and gallery images display

3. **Merchant Listing Edit**
   - Visit: `https://truckersafrica.com/merchant/listings/edit/40`
   - Check: Main image and gallery images display

4. **Upload New Listing**
   - Create a new listing as merchant
   - Upload images
   - Verify they save to `uploads/listings/` (not `public/uploads/listings/`)
   - Check image displays correctly

5. **Branch Request**
   - Create a listing request as branch user
   - Upload images
   - Verify they save to `uploads/listing-requests/`
   - Approve as merchant
   - Check images copy to `uploads/listings/` correctly

---

## Step 6: Verification Queries

Run these on the database to verify all paths are updated:

```sql
-- Should return 0 for all
SELECT
    'merchant_listings' as table_name,
    COUNT(*) as remaining_public_paths
FROM merchant_listings
WHERE main_image_path LIKE 'public/uploads/%'
UNION ALL
SELECT 'merchant_listing_images', COUNT(*)
FROM merchant_listing_images
WHERE image_path LIKE 'public/uploads/%'
UNION ALL
SELECT 'listing_requests', COUNT(*)
FROM listing_requests
WHERE main_image LIKE 'public/uploads/%';
```

Expected result: All counts should be 0

---

## Rollback Plan (If Needed)

If something goes wrong:

1. **Restore database backup:**
```bash
mysql -u username -p database_name < backup_before_migration.sql
```

2. **Revert code files:**
   - Re-upload old versions of the controllers and views

3. **Keep both image locations:**
   - Don't delete `public/uploads/` until migration is confirmed working

---

## Post-Migration Cleanup (After 1 Week)

Once you've confirmed everything works:

```bash
# On the server
cd /home/tlouelea/domains/truckersafrica.com/public_html

# Remove old uploads folder
rm -rf public/uploads

# Verify no broken images on site
# Check all pages where images are displayed
```

---

## Common Issues & Solutions

### Issue: Images not displaying after migration

**Solution:**
```bash
# Check file permissions
chmod 755 uploads/
chmod 755 uploads/listings/
chmod 644 uploads/listings/*.jpg
chmod 644 uploads/listings/*.png
```

### Issue: New uploads still going to public/uploads/

**Solution:**
- Verify you uploaded the updated controller files
- Clear any server-side caching
- Check that `FCPATH` constant points to the correct directory

### Issue: Some old images still in database with public/ prefix

**Solution:**
- Run the SQL update script again
- Check the verification queries

---

## Summary

**Before Migration:**
- Code: `FCPATH . 'public/uploads/listings'`
- Database: `public/uploads/listings/file.jpg`
- URL: `https://truckersafrica.com/public/uploads/listings/file.jpg`

**After Migration:**
- Code: `FCPATH . 'uploads/listings'`
- Database: `uploads/listings/file.jpg`
- URL: `https://truckersafrica.com/uploads/listings/file.jpg`

**Key Benefit:** Clean, simple paths that work identically on localhost and production!
