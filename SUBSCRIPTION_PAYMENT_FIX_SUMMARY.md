# Subscription Payment Flow Fix - Summary

## Problem Statement
Merchants who selected plans without free trials during registration were not being properly redirected to PayFast for payment. The subscription status was set to `'new'` but there was no mechanism to:
1. Redirect them to PayFast to complete payment
2. Restrict access to paid features until payment was completed
3. Display clear payment prompts with actionable buttons

## Solution Implemented

### 1. **Fixed Subscription::processPayment() Method**
**File**: `app/Controllers/Subscription.php` (Lines 543-571)

**Changes**:
- Replaced placeholder implementation with actual PayFast redirect logic
- Method now accepts plan_id from POST or retrieves from current subscription
- Redirects to `Payment::process()` controller which handles PayFast integration
- Shows error if no plan is selected

**Flow**:
```
Merchant clicks "Complete Payment" 
→ Subscription::processPayment() 
→ Payment::process($planId) 
→ PayFast payment page
```

### 2. **Enhanced Merchant Dashboard Payment Alerts**
**File**: `app/Views/merchant/dashboard.php` (Lines 11-54)

**Changes**:
- Added prominent payment required alert for `'new'` and `'trial_pending'` statuses
- Changed from simple link to form submission with POST method
- Added payment icon and improved messaging
- Clarified that users will be redirected to PayFast

**Visual Impact**:
- Red bordered alert box at top of dashboard
- Large "Complete Payment Now →" button with credit card icon
- Clear explanation of what will happen next

### 3. **Updated Subscription Management Page**
**File**: `app/Views/merchant/subscription/index.php`

**Changes Made**:
- **Lines 23-62**: Added large payment required alert at top of page
- **Lines 81-111**: Updated status badge colors and labels
  - Added orange badges for `'new'` and `'trial_pending'`
  - Shows "Payment Required" and "Payment Setup Required" labels
- **Lines 136-168**: Updated action buttons
  - Shows "Complete Payment" button for unpaid subscriptions
  - Allows plan changes even with unpaid status
  - Hides "Cancel Subscription" for unpaid subscriptions

### 4. **Improved Plan Change Flow for Unpaid Subscriptions**
**File**: `app/Controllers/Subscription.php` (Lines 156-203)

**Changes**:
- Added special handling for `'new'` and `'trial_pending'` statuses in `changePlanPreview()`
- When merchants with unpaid subscriptions change plans:
  1. Updates subscription with new plan_id
  2. Shows info message
  3. Redirects directly to PayFast payment

**Benefit**: Merchants can change their mind about plan selection before paying

### 5. **Enhanced Subscription Filter Messages**
**File**: `app/Filters/SubscriptionFilter.php` (Lines 129-143)

**Changes**:
- Updated error messages for `'new'` and `'trial_pending'` statuses
- Added clear call-to-action in messages
- Explains PayFast redirect and trial period details

### 6. **Added Route for Start Trial**
**File**: `app/Config/Routes.php` (Line 493)

**Changes**:
- Added `$routes->post('start-trial', 'Subscription::startTrial');`
- Ensures trial start functionality is accessible

## How It Works Now

### Scenario 1: New Merchant Registration (No Trial Plan)
1. Merchant registers → Account created
2. Merchant selects plan without trial → Subscription created with status `'new'`
3. Merchant logs in → Dashboard shows red payment alert
4. Merchant clicks "Complete Payment Now" → Redirected to PayFast
5. After payment → PayFast ITN updates subscription to `'active'`
6. Merchant can now access all features

### Scenario 2: New Merchant Registration (With Trial Plan)
1. Merchant registers → Account created
2. Merchant selects plan with trial → Subscription created with status `'trial_pending'`
3. Merchant logs in → Dashboard shows payment setup alert
4. Merchant clicks "Complete Payment Setup" → Redirected to PayFast
5. PayFast captures payment method (no charge during trial)
6. Subscription status changes to `'trial'`
7. After trial ends → PayFast automatically charges and updates to `'active'`

### Scenario 3: Merchant Changes Plan Before Paying
1. Merchant has subscription with status `'new'`
2. Merchant goes to Subscription → Plans
3. Merchant selects different plan
4. System updates subscription plan_id
5. Merchant redirected to PayFast with new plan amount
6. After payment → Subscription activated with new plan

### Scenario 4: Merchant Tries to Access Paid Features Without Payment
1. Merchant with status `'new'` tries to access listings/orders/etc
2. SubscriptionFilter blocks access
3. Redirects to `/merchant/subscription` with error message
4. Page shows large "Complete Payment" button
5. Merchant clicks button → Redirected to PayFast

## Files Modified

1. `app/Controllers/Subscription.php` - Fixed processPayment(), updated changePlanPreview()
2. `app/Views/merchant/dashboard.php` - Enhanced payment alert
3. `app/Views/merchant/subscription/index.php` - Added payment alerts and buttons
4. `app/Filters/SubscriptionFilter.php` - Improved error messages
5. `app/Config/Routes.php` - Added start-trial route

## Testing Checklist

- [ ] Register new merchant and select plan without trial
- [ ] Verify dashboard shows payment alert
- [ ] Click "Complete Payment" and verify PayFast redirect
- [ ] Complete payment and verify subscription activates
- [ ] Try accessing paid features before payment (should be blocked)
- [ ] Change plan before paying and verify new amount
- [ ] Register with trial plan and verify payment setup flow
- [ ] Test plan changes for active subscriptions (existing flow)

## Notes

- All payment processing goes through existing `Payment::process()` controller
- PayFast ITN (Instant Transaction Notification) handles subscription activation
- No changes needed to PayFast integration or payment processing logic
- Subscription statuses remain: `'new'`, `'trial_pending'`, `'trial'`, `'active'`, `'past_due'`, `'expired'`, `'cancelled'`

