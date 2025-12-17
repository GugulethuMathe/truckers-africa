# Testing Guide: Auto-Create Primary Location

## Quick Test Scenarios

### Scenario 1: New Merchant First Profile Update ✅
**Expected**: Location should be auto-created

**Steps**:
1. Register a new merchant account
2. Complete onboarding (if required)
3. Go to profile edit page: `http://localhost/truckers-africa/profile/merchant/edit`
4. Fill in all required fields including physical address
5. Click "Update Profile"

**Expected Results**:
- ✅ Profile updated successfully
- ✅ Primary location created automatically
- ✅ Location name: `{Business Name} - Main Branch`
- ✅ Branch user created with email: `merchant+branch{id}@domain.com`
- ✅ Email sent to merchant (check logs if email fails)
- ✅ Can now create listings without "create location first" error

**Verify**:
```sql
-- Check location was created
SELECT * FROM merchant_locations WHERE merchant_id = {YOUR_MERCHANT_ID};

-- Check branch user was created
SELECT * FROM branch_users WHERE merchant_id = {YOUR_MERCHANT_ID};

-- Verify location is primary and active
SELECT location_name, is_primary, is_active FROM merchant_locations WHERE merchant_id = {YOUR_MERCHANT_ID};
```

---

### Scenario 2: Update Profile Again (Should NOT Create Duplicate) ✅
**Expected**: No new location created

**Steps**:
1. Use merchant from Scenario 1 (already has location)
2. Go to profile edit page
3. Update some fields (e.g., business description)
4. Click "Update Profile"

**Expected Results**:
- ✅ Profile updated successfully
- ✅ NO new location created
- ✅ Existing location unchanged

**Verify**:
```sql
-- Should still have only 1 location
SELECT COUNT(*) FROM merchant_locations WHERE merchant_id = {YOUR_MERCHANT_ID};
-- Expected: 1
```

---

### Scenario 3: Merchant With Existing Location ✅
**Expected**: No new location created

**Steps**:
1. Create a merchant account
2. Manually create a location via: `http://localhost/truckers-africa/merchant/locations/create`
3. Go to profile edit page
4. Update profile with address
5. Click "Update Profile"

**Expected Results**:
- ✅ Profile updated successfully
- ✅ NO new location created (already has one)
- ✅ Existing location unchanged

---

### Scenario 4: Update Profile Without Address ✅
**Expected**: No location created

**Steps**:
1. Register a new merchant account
2. Go to profile edit page
3. Fill in fields but leave physical address empty
4. Try to submit (should fail validation)

**Expected Results**:
- ❌ Validation error: "Physical Address is required"
- ✅ No location created

---

### Scenario 5: Test Listing Creation After Auto-Creation ✅
**Expected**: Can create listings immediately

**Steps**:
1. Complete Scenario 1 (auto-create location)
2. Go to create listing page: `http://localhost/truckers-africa/merchant/listings/create`
3. Fill in listing details
4. Select the auto-created location from dropdown
5. Submit listing

**Expected Results**:
- ✅ No "create location first" error
- ✅ Location appears in dropdown
- ✅ Listing created successfully

---

## Database Verification Queries

### Check Auto-Created Location
```sql
SELECT 
    ml.id,
    ml.location_name,
    ml.physical_address,
    ml.is_primary,
    ml.is_active,
    ml.created_at,
    m.business_name
FROM merchant_locations ml
JOIN merchants m ON m.id = ml.merchant_id
WHERE ml.merchant_id = {YOUR_MERCHANT_ID}
ORDER BY ml.created_at DESC;
```

### Check Branch User
```sql
SELECT 
    bu.id,
    bu.email,
    bu.full_name,
    bu.phone_number,
    bu.is_active,
    bu.password_reset_token,
    bu.password_reset_expires,
    ml.location_name
FROM branch_users bu
JOIN merchant_locations ml ON ml.id = bu.location_id
WHERE bu.merchant_id = {YOUR_MERCHANT_ID};
```

### Check Email Format
```sql
SELECT 
    bu.email,
    m.email as merchant_email,
    CASE 
        WHEN bu.email LIKE CONCAT(SUBSTRING_INDEX(m.email, '@', 1), '+branch%')
        THEN 'Valid Format ✅'
        ELSE 'Invalid Format ❌'
    END as email_format_check
FROM branch_users bu
JOIN merchants m ON m.id = bu.merchant_id
WHERE bu.merchant_id = {YOUR_MERCHANT_ID};
```

---

## Log Verification

### Check Application Logs
Location: `writable/logs/log-{DATE}.log`

**Look for**:
```
INFO - Auto-created primary location (ID: X) for merchant Y
```

**Or errors**:
```
ERROR - Failed to auto-create primary location: {error message}
```

---

## Email Verification

### Check Email Was Sent
If email sending is configured, check inbox for:
- **Subject**: "Your Primary Business Location Has Been Created - {Business Name}"
- **To**: Merchant's email
- **Content**: Location details, branch user email, next steps

### If Email Fails
Check logs for:
```
WARNING - Failed to send primary location email: {error message}
```

**Note**: Location is still created even if email fails!

---

## Common Issues & Solutions

### Issue 1: Location Not Created
**Symptoms**: Profile updates but no location appears

**Check**:
1. Does merchant already have locations?
   ```sql
   SELECT COUNT(*) FROM merchant_locations WHERE merchant_id = {ID};
   ```
2. Was physical address provided?
3. Check error logs for exceptions

### Issue 2: Branch User Email Conflict
**Symptoms**: Error about duplicate email

**Solution**: The email aliasing should prevent this, but if it happens:
```sql
-- Check for existing branch users
SELECT email FROM branch_users WHERE merchant_id = {ID};
```

### Issue 3: Transaction Rollback
**Symptoms**: Neither location nor branch user created

**Check**:
- Database transaction logs
- Application error logs
- Validation errors in BranchUserModel

---

## Success Indicators

After completing Scenario 1, you should see:

✅ **In Database**:
- 1 new record in `merchant_locations`
- 1 new record in `branch_users`
- `is_primary = 1` for the location
- `is_active = 1` for both location and branch user

✅ **In Application**:
- No "create location first" error when creating listings
- Location appears in dropdown on listing creation page
- Merchant dashboard shows location count

✅ **In Logs**:
- Success message about location creation
- No error messages

✅ **User Experience**:
- Merchant can immediately create listings
- No need to manually create location
- Clear email notification received

---

## Rollback (If Needed)

To manually remove auto-created location for testing:

```sql
-- Get location ID
SELECT id FROM merchant_locations WHERE merchant_id = {ID} AND is_primary = 1;

-- Delete branch user first (foreign key constraint)
DELETE FROM branch_users WHERE location_id = {LOCATION_ID};

-- Delete location
DELETE FROM merchant_locations WHERE id = {LOCATION_ID};

-- Reset merchant location count
UPDATE merchants SET current_locations_count = 0 WHERE id = {MERCHANT_ID};
```

Then you can test auto-creation again!

---

## Performance Notes

- Auto-creation adds ~200-500ms to profile update
- Uses database transaction (atomic operation)
- Email sending is non-blocking (doesn't fail profile update)
- Logs all operations for debugging

---

**Happy Testing! 🚀**

