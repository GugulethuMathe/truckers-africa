# Dashboard Warning Messages - Implementation Summary

## Overview
Added prominent payment warning messages to both Merchant and Branch dashboards to alert users when payment is required.

## Changes Made

### 1. Merchant Dashboard ✅
**File**: `app/Views/merchant/dashboard.php` (Lines 11-47)

**Added Alert Banner**:
- Shows when subscription status is `'new'` or `'trial_pending'`
- Red banner with lock icon
- Clear call-to-action button
- Different messages for each status

**Messages**:
- **trial_pending**:
  - Title: "🔒 Payment Method Required to Start Your Free Trial"
  - Text: "Please provide your payment method to start your free trial and access all features. Your card will not be charged during the trial period."
  - Button: "Complete Payment Setup →"

- **new**:
  - Title: "🔒 Payment Required to Activate Your Subscription"
  - Text: "Please complete your payment to activate your subscription and access premium features."
  - Button: "Complete Payment →"

### 2. Branch Dashboard ✅
**File**: `app/Views/branch/dashboard.php` (Lines 8-35)
**File**: `app/Controllers/BranchDashboard.php` (Lines 94-100)

**Added Alert Banner**:
- Shows when parent merchant's subscription is `'new'` or `'trial_pending'`
- Orange banner with warning icon
- Informs branch user to contact merchant
- Different messages for each status

**Controller Changes**:
- Added `merchant_subscription` to data passed to view
- Fetches parent merchant's subscription status

**Messages**:
- **trial_pending**:
  - Title: "🔒 Merchant Payment Required"
  - Text: "Your merchant account needs to provide payment method to start the free trial and access features. Please contact your business owner to complete the payment setup."

- **new**:
  - Title: "🔒 Merchant Payment Required"
  - Text: "Your merchant account needs to complete payment to activate the subscription. Please contact your business owner to complete the payment."

## Visual Design

### Merchant Dashboard Alert
```
┌────────────────────────────────────────────────────────────┐
│ 🚫  🔒 Payment Method Required to Start Your Free Trial   │
│                                                            │
│ Please provide your payment method to start your free     │
│ trial and access all features. Your card will not be      │
│ charged during the trial period.                          │
│                                                            │
│ [Complete Payment Setup →]                                 │
└────────────────────────────────────────────────────────────┘
   Red background (#FEF2F2), Red border (#FCA5A5)
```

### Branch Dashboard Alert
```
┌────────────────────────────────────────────────────────────┐
│ ⚠️  🔒 Merchant Payment Required                           │
│                                                            │
│ Your merchant account needs to provide payment method to   │
│ start the free trial and access features. Please contact  │
│ your business owner to complete the payment setup.        │
│                                                            │
│ ℹ️  Contact your business owner to resolve this issue     │
└────────────────────────────────────────────────────────────┘
   Orange background (#FFFBEB), Orange border (#FCD34D)
```

## User Flow

### For Merchants with 'trial_pending' Status:
1. Log in to merchant dashboard
2. See prominent red alert at top
3. Message: "Payment Method Required to Start Your Free Trial"
4. Click "Complete Payment Setup →"
5. Redirected to subscription page
6. Complete R0.00 payment (capture card)
7. Status changes to 'trial'
8. Alert disappears, full access granted

### For Merchants with 'new' Status:
1. Log in to merchant dashboard
2. See prominent red alert at top
3. Message: "Payment Required to Activate Your Subscription"
4. Click "Complete Payment →"
5. Redirected to subscription page
6. Complete full payment
7. Status changes to 'active'
8. Alert disappears, full access granted

### For Branch Users (Parent Merchant Pending):
1. Log in to branch dashboard
2. See prominent orange alert at top
3. Message: "Merchant Payment Required"
4. Info: Contact business owner
5. Cannot complete payment themselves
6. Must wait for merchant to complete payment
7. Once merchant pays, alert disappears

## Alert Positioning

Both alerts appear:
- **After** the subscription warning component
- **Before** the welcome header
- At the **top of the content area**
- With prominent styling to catch attention

## Conditional Display

Alerts only show when:
```php
isset($subscription['status']) && in_array($subscription['status'], ['new', 'trial_pending'])
```

Alerts do NOT show when:
- Status is `'trial'` (trial active)
- Status is `'active'` (subscription active)
- Status is `'expired'` (different warning)
- Status is `'past_due'` (different warning)
- Status is `'cancelled'` (different warning)

## Testing

### Test Merchant Dashboard:
1. Create merchant with Lite plan (no trial) → Status should be `'new'`
2. Log in to merchant dashboard
3. **Expected**: Red alert with "Payment Required to Activate Your Subscription"
4. Click button
5. **Expected**: Redirected to `/merchant/subscription`

### Test Merchant Dashboard (Trial):
1. Create merchant with Plus plan (30-day trial) → Status should be `'trial_pending'`
2. Log in to merchant dashboard
3. **Expected**: Red alert with "Payment Method Required to Start Your Free Trial"
4. Click button
5. **Expected**: Redirected to `/merchant/subscription`

### Test Branch Dashboard:
1. Create branch user under merchant with `'new'` status
2. Log in to branch dashboard
3. **Expected**: Orange alert with "Merchant Payment Required"
4. Should not have button (can't complete payment)

## Related Files

- `app/Views/merchant/dashboard.php` - Merchant dashboard view
- `app/Views/branch/dashboard.php` - Branch dashboard view
- `app/Controllers/BranchDashboard.php` - Branch dashboard controller
- `app/Filters/SubscriptionFilter.php` - Access control filter
- `app/Config/Filters.php` - Route protection

## Integration with Access Control

These dashboard warnings work in conjunction with:
1. **SubscriptionFilter** - Blocks access to protected routes
2. **Route Protection** - Prevents feature access without payment
3. **Redirect Logic** - Sends users to subscription page when needed

**Combined Effect**:
- User sees warning on dashboard ✅
- User tries to access branches → Blocked by filter ✅
- User redirected to subscription page ✅
- User completes payment ✅
- Warning disappears, access granted ✅

## CSS Classes Used

**Tailwind CSS classes**:
- Layout: `rounded-md`, `p-4`, `mb-6`, `border-2`
- Colors: `bg-red-50`, `border-red-400`, `text-red-800`, `text-red-700`
- Flexbox: `flex`, `flex-shrink-0`, `flex-1`
- Typography: `text-sm`, `font-semibold`, `font-medium`
- Buttons: `inline-flex`, `items-center`, `px-4`, `py-2`

All styling is responsive and mobile-friendly.

## Date: November 19, 2025
