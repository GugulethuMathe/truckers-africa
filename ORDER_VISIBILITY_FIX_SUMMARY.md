# Order Visibility Fix - Option 2 Implementation

## Problem Statement

Orders were not showing for merchants (Main Branch) when drivers placed orders. This was caused by an overly restrictive filter that excluded ALL orders from locations with active branch users, including the primary location.

---

## Root Cause

The original query logic excluded orders from ANY location that had an active branch user:

```php
->where('merchant_locations.id NOT IN (SELECT location_id FROM branch_users WHERE is_active = 1)', null, false)
```

**Issues with this approach:**
- ❌ If a merchant had only ONE location (primary) with a branch user, ALL orders disappeared
- ❌ Merchants were "locked out" of seeing their own orders
- ❌ No visibility for business owners to monitor operations
- ❌ Especially problematic for single-location merchants

---

## Solution Implemented: Option 2

**Show only PRIMARY location orders to main merchant, and branch-specific orders to branch users.**

### New Logic:

```php
->where('(merchant_locations.is_primary = 1 OR merchant_locations.id NOT IN (SELECT location_id FROM branch_users WHERE is_active = 1))', null, false)
```

This means:
- ✅ **Main merchant** sees orders from PRIMARY location (always)
- ✅ **Main merchant** sees orders from locations WITHOUT active branch users
- ✅ **Branch users** see orders from their assigned location only
- ✅ Clear separation of responsibilities
- ✅ Merchants never lose visibility of their main location

---

## Files Modified

### 1. `app/Controllers/MerchantDashboard.php`

**Line 121** - Updated order list query:
```php
// OLD:
->where('merchant_locations.id NOT IN (SELECT location_id FROM branch_users WHERE is_active = 1)', null, false)

// NEW:
->where('(merchant_locations.is_primary = 1 OR merchant_locations.id NOT IN (SELECT location_id FROM branch_users WHERE is_active = 1))', null, false)
```

**Line 173** - Updated order detail verification:
```php
// OLD:
->where('merchant_locations.id NOT IN (SELECT location_id FROM branch_users WHERE is_active = 1)', null, false)

// NEW:
->where('(merchant_locations.is_primary = 1 OR merchant_locations.id NOT IN (SELECT location_id FROM branch_users WHERE is_active = 1))', null, false)
```

---

### 2. `app/Models/OrderModel.php`

**Line 51** - `getOrdersByMerchant()` method:
```php
// OLD:
->where('merchant_locations.id NOT IN (SELECT location_id FROM branch_users WHERE is_active = 1)', null, false)

// NEW:
->where('(merchant_locations.is_primary = 1 OR merchant_locations.id NOT IN (SELECT location_id FROM branch_users WHERE is_active = 1))', null, false)
```

**Line 75** - `getOrderItemsByMerchant()` method:
```php
// OLD:
->where('merchant_locations.id NOT IN (SELECT location_id FROM branch_users WHERE is_active = 1)', null, false)

// NEW:
->where('(merchant_locations.is_primary = 1 OR merchant_locations.id NOT IN (SELECT location_id FROM branch_users WHERE is_active = 1))', null, false)
```

**Line 95** - `getOrderWithItems()` method (2 occurrences):
```php
// OLD:
->where('merchant_locations.id NOT IN (SELECT location_id FROM branch_users WHERE is_active = 1)', null, false)

// NEW:
->where('(merchant_locations.is_primary = 1 OR merchant_locations.id NOT IN (SELECT location_id FROM branch_users WHERE is_active = 1))', null, false)
```

---

## How It Works Now

### Scenario 1: Merchant with 1 Location (Primary) + Branch User

**Before Fix:**
- Merchant sees: ❌ NO ORDERS (locked out)
- Branch user sees: ✅ All orders

**After Fix:**
- Merchant sees: ✅ All orders from primary location
- Branch user sees: ✅ All orders from their location

---

### Scenario 2: Merchant with 2 Locations (Primary + Secondary)

**Location 1 (Primary):** Has branch user  
**Location 2 (Secondary):** Has branch user

**Before Fix:**
- Merchant sees: ❌ NO ORDERS from either location

**After Fix:**
- Merchant sees: ✅ Orders from PRIMARY location only
- Branch user 1 sees: ✅ Orders from location 1
- Branch user 2 sees: ✅ Orders from location 2

---

### Scenario 3: Merchant with 3 Locations

**Location 1 (Primary):** Has branch user  
**Location 2 (Secondary):** Has branch user  
**Location 3 (Secondary):** NO branch user

**Before Fix:**
- Merchant sees: ✅ Orders from location 3 only

**After Fix:**
- Merchant sees: ✅ Orders from PRIMARY (location 1) + location 3
- Branch user 1 sees: ✅ Orders from location 1
- Branch user 2 sees: ✅ Orders from location 2

---

## Benefits

✅ **Prevents lockout** - Merchants always see their primary location orders  
✅ **Clear hierarchy** - Primary location is always visible to owner  
✅ **Delegation** - Branch users manage their specific locations  
✅ **Oversight** - Merchants can monitor main operations  
✅ **Scalability** - Works for single or multi-location businesses  

---

## Testing Checklist

### Test 1: Single Location Merchant
- [ ] Create merchant with 1 primary location
- [ ] Assign branch user to primary location
- [ ] Driver places order at primary location
- [ ] ✅ Merchant should see the order
- [ ] ✅ Branch user should see the order

### Test 2: Multi-Location Merchant
- [ ] Create merchant with 2 locations (1 primary, 1 secondary)
- [ ] Assign branch user to both locations
- [ ] Driver places orders at both locations
- [ ] ✅ Merchant should see orders from PRIMARY only
- [ ] ✅ Branch user 1 sees orders from location 1
- [ ] ✅ Branch user 2 sees orders from location 2

### Test 3: Mixed Configuration
- [ ] Create merchant with 3 locations
- [ ] Assign branch users to primary and one secondary
- [ ] Leave one secondary without branch user
- [ ] Driver places orders at all 3 locations
- [ ] ✅ Merchant sees orders from PRIMARY + location without branch
- [ ] ✅ Branch users see only their assigned location orders

---

## Database Query Explanation

The new WHERE clause uses an OR condition:

```sql
WHERE (
    merchant_locations.is_primary = 1  -- Always show primary location
    OR 
    merchant_locations.id NOT IN (     -- OR show locations without active branch users
        SELECT location_id 
        FROM branch_users 
        WHERE is_active = 1
    )
)
```

This ensures:
1. Primary location is ALWAYS included (regardless of branch user)
2. Any location WITHOUT an active branch user is included
3. Non-primary locations WITH active branch users are excluded

---

## Notes

- Branch users continue to see ONLY their assigned location orders (no change)
- The `is_primary` flag in `merchant_locations` table is critical for this logic
- Inactive branch users (`is_active = 0`) don't affect order visibility
- This implementation maintains backward compatibility with existing branch dashboard logic

---

## Related Files (No Changes Needed)

- `app/Controllers/BranchDashboard.php` - Branch logic unchanged, works correctly
- `app/Models/MasterOrderModel.php` - Driver-side queries unchanged
- `app/Controllers/Order.php` - Order placement logic unchanged

---

## Deployment Notes

1. ✅ No database schema changes required
2. ✅ No migration needed
3. ✅ Backward compatible with existing data
4. ✅ Test thoroughly before production deployment
5. ✅ Monitor merchant feedback after deployment

---

**Implementation Date:** 2025-11-19  
**Issue:** Orders not showing for merchants with branch users  
**Solution:** Option 2 - Show primary location orders to main merchant  
**Status:** ✅ COMPLETE

