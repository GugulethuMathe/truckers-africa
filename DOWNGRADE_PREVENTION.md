# Plan Downgrade Prevention - Implementation Summary

## Overview
Implemented a system to prevent merchants from downgrading their subscription plans directly through the platform. All downgrades now require contacting support to ensure a smooth transition and proper handling of data/features.

## Changes Made

### 1. Controller Logic Update ✅
**File**: `app/Controllers/Subscription.php` - `changePlanPreview()` method (Lines 205-222)

**Added Downgrade Detection and Blocking**:
```php
// Calculate prorata amount to check if this is a downgrade
$prorataService = new \App\Services\ProrataBillingService();
$breakdown = $prorataService->getProrataBreakdown($currentSubscription['id'], $newPlanId);

if (!$breakdown) {
    session()->setFlashdata('error', 'Unable to calculate prorata amount. Please try again.');
    return redirect()->back();
}

// Block all downgrades - users must contact support
if (!$breakdown['usd']['is_upgrade']) {
    $currentPlan = $this->planModel->find($currentSubscription['plan_id']);
    $message = 'Plan downgrades require assistance from our support team to ensure a smooth transition. ' .
               'Please <a href="' . site_url('merchant/help') . '" class="underline font-semibold text-blue-600 hover:text-blue-800">contact support</a> to downgrade from ' .
               esc($currentPlan['name']) . ' to ' . esc($newPlan['name']) . '.';
    session()->setFlashdata('error', $message);
    return redirect()->back();
}
```

**Key Features**:
- Calculates prorata breakdown to determine if plan change is an upgrade or downgrade
- Uses `$breakdown['usd']['is_upgrade']` flag from ProrataBillingService
- Blocks ALL downgrades (not just those with location conflicts)
- Shows user-friendly error message with clickable link to help page
- Redirects back to subscription plans page

### 2. Updated Location Limit Check ✅
**File**: `app/Controllers/Subscription.php` (Lines 236-244)

**Updated Comment and Message**:
- Changed from "downgrade" to "upgrade" in comment (since downgrades are already blocked)
- Updated error message to include link to help page
- Made link styling consistent (blue, underlined, bold)

## How It Works

### User Flow

**Before** (Old Behavior):
1. Merchant clicks "Switch to [Lower Plan]"
2. System shows prorata preview with credit
3. Merchant confirms downgrade
4. Plan downgraded immediately ❌

**After** (New Behavior):
1. Merchant clicks "Switch to [Lower Plan]"
2. System detects downgrade
3. Shows error message: "Plan downgrades require assistance from our support team..."
4. Provides clickable link to `/merchant/help`
5. Merchant contacts support for assistance ✅

### Upgrade Flow (Unchanged)
1. Merchant clicks "Switch to [Higher Plan]"
2. System detects upgrade
3. Shows prorata preview with payment amount
4. Merchant proceeds to payment
5. Plan upgraded after payment ✅

## Detection Logic

### How Downgrades Are Detected

The system uses the `ProrataBillingService::getProrataBreakdown()` method which returns:

```php
[
    'usd' => [
        'is_upgrade' => true/false,  // Based on price comparison
        'prorata_amount' => 10.50,   // Positive = charge, Negative = credit
        'current_plan_price' => 50.00,
        'new_plan_price' => 100.00,
        // ... other fields
    ],
    // ... other data
]
```

**Upgrade Detection**:
- `is_upgrade = true` when `new_plan_price > current_plan_price`
- `prorata_amount > 0` (merchant pays difference)

**Downgrade Detection**:
- `is_upgrade = false` when `new_plan_price <= current_plan_price`
- `prorata_amount <= 0` (merchant gets credit)

## Error Messages

### Downgrade Blocked Message
```
Plan downgrades require assistance from our support team to ensure a smooth transition. 
Please contact support to downgrade from [Current Plan] to [New Plan].
```

**Features**:
- Clear explanation of why downgrade is blocked
- Clickable "contact support" link to `/merchant/help`
- Shows both current and target plan names
- Professional and helpful tone

### Location Limit Exceeded Message
```
You cannot switch to this plan because you currently have X branch location(s), 
but the selected plan only allows Y. Please contact support for assistance.
```

**Features**:
- Explains the specific limitation
- Shows current vs. allowed location count
- Provides link to help page

## Support Page

**URL**: `http://localhost/truckers-africa/merchant/help`

**Controller**: `MerchantDashboard::help()`

**View**: `app/Views/merchant/help.php`

**Features**:
- Contact information (email, phone, WhatsApp)
- Business hours
- Common questions/FAQ
- Quick action buttons (WhatsApp, Email)

## Why Prevent Downgrades?

### Business Reasons
1. **Data Integrity**: Ensure proper handling of data that exceeds new plan limits
2. **Feature Migration**: Properly disable features not available in lower plan
3. **Customer Retention**: Opportunity to discuss concerns and offer alternatives
4. **Billing Accuracy**: Ensure credits are properly calculated and applied

### Technical Reasons
1. **Location Limits**: Merchant may have more locations than new plan allows
2. **Listing Limits**: May have more listings than new plan permits
3. **Feature Access**: Some features may need to be disabled
4. **Data Cleanup**: May need to archive or remove data

## Testing Scenarios

### Scenario 1: Attempt Downgrade ✅
**Steps**:
1. Login as merchant with active subscription (e.g., Premium plan)
2. Go to `/merchant/subscription/plans`
3. Click "Switch to [Lower Plan]" (e.g., Basic plan)
4. Observe error message

**Expected Result**:
- ❌ Downgrade blocked
- ✅ Error message displayed
- ✅ Link to help page shown
- ✅ Redirected back to plans page

### Scenario 2: Attempt Upgrade ✅
**Steps**:
1. Login as merchant with active subscription (e.g., Basic plan)
2. Go to `/merchant/subscription/plans`
3. Click "Switch to [Higher Plan]" (e.g., Premium plan)
4. Review prorata breakdown

**Expected Result**:
- ✅ Upgrade allowed
- ✅ Prorata preview shown
- ✅ Payment amount calculated
- ✅ Can proceed to payment

### Scenario 3: Same Plan ✅
**Steps**:
1. Login as merchant
2. Try to switch to current plan

**Expected Result**:
- ❌ Blocked with message: "You are already subscribed to this plan"

## Related Files

- `app/Controllers/Subscription.php` - Main subscription controller
- `app/Services/ProrataBillingService.php` - Prorata calculation service
- `app/Views/merchant/subscription/plans.php` - Plan selection page
- `app/Views/merchant/help.php` - Support/help page
- `app/Controllers/MerchantDashboard.php` - Help page controller

## Future Enhancements

1. **Admin Override**: Allow admins to manually downgrade merchants
2. **Downgrade Requests**: Add form to submit downgrade request with reason
3. **Automated Checks**: Automatically check if downgrade is safe (no data conflicts)
4. **Grace Period**: Allow downgrades with grace period to adjust data
5. **Downgrade Preview**: Show what will be lost/disabled in downgrade

## Notes

- Downgrades are completely blocked at the controller level
- No changes needed to views (error message displayed via flash data)
- Help page already exists with full contact information
- Error messages include HTML (clickable links) - rendered properly by views
- Upgrade flow remains unchanged and fully functional

---

**Implementation Date**: 2025-11-19
**Status**: ✅ Complete and Ready for Testing

