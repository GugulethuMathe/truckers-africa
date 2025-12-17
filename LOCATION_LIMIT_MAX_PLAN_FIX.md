# Location Limit Message for Maximum Plan - Implementation

## Problem

When merchants on the **highest-priced plan** (Max - $20/month) reached their location limit, they saw:
```
You've reached your plan limit of X location(s). Upgrade to add more.
```

This was confusing because:
- ❌ They're already on the maximum plan
- ❌ There's no higher plan to upgrade to
- ❌ "Upgrade your plan" link doesn't make sense

## Solution

Updated the location limit message to detect if merchant is on the maximum plan and show:
```
You've reached your plan limit of X location(s). Contact Support to add more location(s).
```

With a link to `/merchant/help` instead of `/merchant/subscription/plans`.

## Changes Made

### 1. Model - `app/Models/MerchantLocationModel.php`

#### A. Updated `canAddLocation()` Method (Lines 120-169)

**Added**:
- `is_max_plan` flag to return array
- Check if merchant is on maximum plan
- Conditional message based on plan tier

```php
// Check if merchant is on the highest-priced plan
$isMaxPlan = $this->isOnMaximumPlan($merchantId);

if ($canAdd) {
    $message = "You can add " . ($maxAllowed - $currentCount) . " more location(s).";
} else {
    // If on maximum plan, show "Contact Support" instead of "Upgrade"
    if ($isMaxPlan) {
        $message = "You've reached your plan limit of {$maxAllowed} location(s). Contact Support to add more location(s).";
    } else {
        $message = "You've reached your plan limit of {$maxAllowed} location(s). Upgrade to add more.";
    }
}

return [
    'can_add' => $canAdd,
    'current_count' => $currentCount,
    'max_allowed' => $maxAllowed,
    'message' => $message,
    'is_max_plan' => $isMaxPlan  // NEW
];
```

#### B. Added `isOnMaximumPlan()` Method (Lines 171-195)

**New private method** to check if merchant is on the highest-priced plan:

```php
private function isOnMaximumPlan(int $merchantId): bool
{
    $subscriptionModel = new \App\Models\SubscriptionModel();
    $planModel = new \App\Models\SubscriptionPlanModel();
    
    // Get merchant's current subscription
    $subscription = $subscriptionModel->getCurrentSubscription($merchantId);
    
    if (!$subscription || !isset($subscription['plan_id'])) {
        return false;
    }
    
    // Get the highest-priced plan
    $highestPlan = $planModel->orderBy('price', 'DESC')->first();
    
    if (!$highestPlan) {
        return false;
    }
    
    // Check if merchant's plan is the highest-priced plan
    return (int)$subscription['plan_id'] === (int)$highestPlan['id'];
}
```

**Logic**:
1. Get merchant's current subscription
2. Query for highest-priced plan (`ORDER BY price DESC LIMIT 1`)
3. Compare merchant's plan_id with highest plan's id
4. Return true if they match

### 2. Controller - `app/Controllers/MerchantLocations.php`

#### Updated `index()` Method (Lines 23-48)

**Added** `is_max_plan` to view data:

```php
$data = [
    'page_title' => 'Business Locations',
    'locations' => $locations,
    'can_add_location' => $canAddCheck['can_add'],
    'location_limit_message' => $canAddCheck['message'],
    'is_max_plan' => $canAddCheck['is_max_plan'] ?? false,  // NEW
    'usage_stats' => $usageStats
];
```

### 3. View - `app/Views/merchant/locations/index.php`

#### Updated Limit Message (Lines 115-134)

**Added conditional link** based on `is_max_plan` flag:

```php
<p class="text-sm text-yellow-700">
    <?= $location_limit_message ?>
    <?php if (isset($is_max_plan) && $is_max_plan): ?>
        <a href="<?= site_url('merchant/help') ?>" class="font-medium underline hover:text-yellow-800">Contact Support</a>
    <?php else: ?>
        <a href="<?= site_url('merchant/subscription/plans') ?>" class="font-medium underline hover:text-yellow-800">Upgrade your plan</a>
    <?php endif; ?>
</p>
```

## How It Works

### Current Plans (from database):

| Plan ID | Plan Name | Price | Max Locations |
|---------|-----------|-------|---------------|
| 1 | Lite | $1.00 | 1 |
| 2 | Plus | $15.00 | 3 |
| 3 | Max | $20.00 | Varies |

**Highest Plan**: Max ($20.00) - Plan ID: 3

### Scenario 1: Merchant on Lite Plan (Reached Limit)

**Plan**: Lite ($1.00) - 1 location max
**Current Locations**: 1
**Is Max Plan**: ❌ No

**Message Shown**:
```
You've reached your plan limit of 1 location(s). Upgrade to add more.
[Upgrade your plan] → /merchant/subscription/plans
```

### Scenario 2: Merchant on Plus Plan (Reached Limit)

**Plan**: Plus ($15.00) - 3 locations max
**Current Locations**: 3
**Is Max Plan**: ❌ No

**Message Shown**:
```
You've reached your plan limit of 3 location(s). Upgrade to add more.
[Upgrade your plan] → /merchant/subscription/plans
```

### Scenario 3: Merchant on Max Plan (Reached Limit) ✅

**Plan**: Max ($20.00) - X locations max
**Current Locations**: X (at limit)
**Is Max Plan**: ✅ Yes

**Message Shown**:
```
You've reached your plan limit of X location(s). Contact Support to add more location(s).
[Contact Support] → /merchant/help
```

## Testing

### Test 1: Merchant on Lower Plan

1. Login as merchant on Lite or Plus plan
2. Add locations until limit reached
3. Go to: `http://localhost/truckers-africa/merchant/locations`
4. ✅ Should see: "Upgrade to add more"
5. ✅ Link should go to: `/merchant/subscription/plans`

### Test 2: Merchant on Max Plan

1. Login as merchant on Max plan ($20/month)
2. Add locations until limit reached
3. Go to: `http://localhost/truckers-africa/merchant/locations`
4. ✅ Should see: "Contact Support to add more location(s)"
5. ✅ Link should go to: `/merchant/help`

### Test 3: Verify Plan Detection

**SQL Query**:
```sql
-- Check which plan is highest
SELECT id, name, price 
FROM plans 
ORDER BY price DESC 
LIMIT 1;

-- Check merchant's plan
SELECT s.merchant_id, s.plan_id, p.name, p.price
FROM subscriptions s
JOIN plans p ON p.id = s.plan_id
WHERE s.merchant_id = [YOUR_MERCHANT_ID]
  AND s.status IN ('trial', 'active');
```

## Benefits

### For Merchants:
- ✅ Clear, accurate messaging
- ✅ No confusion about upgrade options
- ✅ Direct path to support for custom solutions

### For Business:
- ✅ Opportunity to discuss custom enterprise plans
- ✅ Better customer experience
- ✅ Reduced confusion and support tickets

### For Support:
- ✅ Merchants know to contact support
- ✅ Can offer custom pricing/limits
- ✅ Better upsell opportunities

## Future Enhancements

1. **Custom Enterprise Plans**: Create plans above Max for high-volume merchants
2. **Dynamic Limits**: Allow support to increase limits without plan changes
3. **Usage-Based Pricing**: Charge per location above base limit
4. **Bulk Discounts**: Offer discounts for merchants needing many locations

## Related Files

- `app/Models/MerchantLocationModel.php` - Location limit logic
- `app/Controllers/MerchantLocations.php` - Locations controller
- `app/Views/merchant/locations/index.php` - Locations list view
- `app/Views/merchant/help.php` - Support contact page

---

**Implementation Date**: 2025-11-19
**Status**: ✅ Complete and Ready for Testing
**Issue**: Resolved - Max plan merchants see "Contact Support" instead of "Upgrade"

