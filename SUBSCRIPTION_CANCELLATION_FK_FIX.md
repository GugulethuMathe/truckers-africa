# Subscription Cancellation Foreign Key Fix

## Problem

When merchants tried to cancel their subscription, the system threw a foreign key constraint error:

```
mysqli_sql_exception: Cannot add or update a child row: a foreign key constraint fails 
(`app_truckers_africa`.`subscription_cancellations`, 
CONSTRAINT `subscription_cancellations_plan_id_foreign` 
FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`id`) 
ON DELETE CASCADE ON UPDATE CASCADE)
```

**Error Location**: `app/Controllers/Subscription.php` line 610

## Root Cause

The `subscription_cancellations` table had an **incorrect foreign key constraint**:

❌ **Wrong**: Foreign key referenced `subscription_plans` table
✅ **Correct**: Should reference `plans` table

**Why This Happened**:
- The actual table name is `plans` (not `subscription_plans`)
- The migration file had the wrong table name in the foreign key constraint
- When trying to insert cancellation data with `plan_id = 3`, the database couldn't find the referenced table

## Solution

### 1. Fixed Migration File

**File**: `app/Database/Migrations/2025-11-19-165304_CreateSubscriptionCancellationsTable.php`

**Changed Line 59**:
```php
// BEFORE (Wrong)
$this->forge->addForeignKey('plan_id', 'subscription_plans', 'id', 'CASCADE', 'CASCADE');

// AFTER (Correct)
$this->forge->addForeignKey('plan_id', 'plans', 'id', 'CASCADE', 'CASCADE');
```

### 2. Fixed SQL File

**File**: `create_subscription_cancellations_table.sql`

**Changed Line 23-24**:
```sql
-- BEFORE (Wrong)
CONSTRAINT `subscription_cancellations_plan_id_foreign` 
  FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`id`) ON DELETE CASCADE ON UPDATE CASCADE

-- AFTER (Correct)
CONSTRAINT `subscription_cancellations_plan_id_foreign` 
  FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
```

### 3. Created Fix Script

**File**: `fix_subscription_cancellations_fk.sql`

This script:
1. Drops the incorrect foreign key constraint
2. Adds the correct foreign key constraint
3. Verifies the fix

## How to Apply the Fix

### Option 1: Run SQL Script (Recommended)

1. Open phpMyAdmin or MySQL Workbench
2. Select database: `app_truckers_africa`
3. Open and run: `fix_subscription_cancellations_fk.sql`
4. Verify success message

### Option 2: Manual SQL Commands

Run these commands in your MySQL client:

```sql
USE app_truckers_africa;

-- Drop incorrect constraint
ALTER TABLE `subscription_cancellations` 
DROP FOREIGN KEY `subscription_cancellations_plan_id_foreign`;

-- Add correct constraint
ALTER TABLE `subscription_cancellations` 
ADD CONSTRAINT `subscription_cancellations_plan_id_foreign` 
FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) 
ON DELETE CASCADE 
ON UPDATE CASCADE;
```

### Option 3: Via phpMyAdmin

1. Go to: `http://localhost/phpmyadmin`
2. Select database: `app_truckers_africa`
3. Click on table: `subscription_cancellations`
4. Go to "Structure" tab
5. Click "Relation view"
6. Find `plan_id` foreign key
7. Change referenced table from `subscription_plans` to `plans`
8. Save changes

## Verification

After applying the fix, verify it worked:

### Test 1: Check Foreign Key

```sql
SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'app_truckers_africa'
  AND TABLE_NAME = 'subscription_cancellations'
  AND REFERENCED_TABLE_NAME IS NOT NULL;
```

**Expected Result**:
```
subscription_cancellations_plan_id_foreign | subscription_cancellations | plan_id | plans | id
```

### Test 2: Try Cancellation

1. Login as merchant with active subscription
2. Go to: `http://localhost/truckers-africa/merchant/subscription`
3. Click "Cancel Subscription"
4. Fill out cancellation form
5. Submit
6. ✅ Should succeed without errors

### Test 3: Verify Data Inserted

```sql
SELECT * FROM subscription_cancellations 
ORDER BY cancelled_at DESC 
LIMIT 5;
```

Should show recent cancellation records.

## Database Schema

### Correct Foreign Keys for `subscription_cancellations`:

```sql
subscription_cancellations
├── subscription_id → subscriptions.id (CASCADE)
├── merchant_id → merchants.id (CASCADE)
└── plan_id → plans.id (CASCADE)  ✅ CORRECT
```

### Table Names in Database:

- ✅ `plans` - Subscription plans table
- ❌ `subscription_plans` - Does NOT exist
- ✅ `subscriptions` - Active subscriptions
- ✅ `subscription_cancellations` - Cancellation feedback

## Impact

**Before Fix**:
- ❌ Merchants couldn't cancel subscriptions
- ❌ Database error on cancellation attempt
- ❌ No cancellation feedback collected
- ❌ Poor user experience

**After Fix**:
- ✅ Merchants can cancel subscriptions successfully
- ✅ Cancellation feedback stored properly
- ✅ No database errors
- ✅ Smooth cancellation flow

## Files Modified

1. ✅ `app/Database/Migrations/2025-11-19-165304_CreateSubscriptionCancellationsTable.php` - Fixed foreign key
2. ✅ `create_subscription_cancellations_table.sql` - Fixed foreign key
3. ✅ `fix_subscription_cancellations_fk.sql` - Created fix script
4. ✅ `SUBSCRIPTION_CANCELLATION_FK_FIX.md` - Documentation

## Prevention

To prevent similar issues in the future:

1. **Always verify table names** before creating foreign keys
2. **Use consistent naming** across the codebase
3. **Test migrations** on a copy of production database
4. **Check foreign key constraints** after running migrations
5. **Use models** to reference table names (e.g., `$this->planModel->getTable()`)

## Related Documentation

- `SUBSCRIPTION_CANCELLATION_MODAL.md` - Cancellation modal implementation
- `SUBSCRIPTION_REACTIVATION_FIX.md` - Reactivation after cancellation
- `FREE_TRIAL_RESTRICTION.md` - Trial restrictions

---

**Issue**: Foreign key constraint error on subscription cancellation
**Root Cause**: Wrong table name in foreign key constraint
**Fix**: Changed `subscription_plans` to `plans`
**Status**: ✅ Fixed - Ready to apply
**Date**: 2025-11-19

