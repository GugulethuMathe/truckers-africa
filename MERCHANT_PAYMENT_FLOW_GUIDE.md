# Merchant Payment Flow - User Guide

## What Merchants Will See

### 1. Dashboard View (Unpaid Subscription)

When a merchant logs in with an unpaid subscription, they will see:

```
┌─────────────────────────────────────────────────────────────────┐
│ 🔒 Payment Required to Activate Your Subscription              │
│                                                                 │
│ Your subscription is pending payment. Please complete your     │
│ payment to activate your account and access all premium        │
│ features. You will be redirected to PayFast to complete the    │
│ payment securely.                                              │
│                                                                 │
│ [💳 Complete Payment Now →]                                    │
└─────────────────────────────────────────────────────────────────┘
```

**Key Features**:
- Red border and background (high visibility)
- Clear explanation of what's needed
- Large, prominent payment button
- Credit card icon for visual clarity

### 2. Subscription Management Page (Unpaid)

When merchants navigate to `/merchant/subscription`:

```
┌─────────────────────────────────────────────────────────────────┐
│ 🔒 Payment Required to Activate Your Subscription              │
│                                                                 │
│ Your subscription is pending payment. Please complete your     │
│ payment to activate your account and access all premium        │
│ features.                                                      │
│                                                                 │
│ [💳 Complete Payment Now]                                      │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│ Current Subscription                                            │
│                                                                 │
│ Professional Plan                                               │
│ Full access to all features                                    │
│ $29.99/month                                                   │
│                                                                 │
│ Status: [Payment Required]                                     │
│                                                                 │
│ [Complete Payment]  [Change Plan]                             │
└─────────────────────────────────────────────────────────────────┘
```

### 3. When Trying to Access Paid Features

If merchant tries to access listings, orders, or other paid features:

```
┌─────────────────────────────────────────────────────────────────┐
│ ⚠️ Access Restricted                                            │
│                                                                 │
│ Payment required! Please complete your payment to activate     │
│ your subscription and access premium features. Click           │
│ "Complete Payment" below to proceed to PayFast.               │
└─────────────────────────────────────────────────────────────────┘

→ Redirected to /merchant/subscription page
```

### 4. Payment Flow

```
Step 1: Merchant Dashboard
   ↓
   [Complete Payment Now] button clicked
   ↓
Step 2: Subscription::processPayment()
   ↓
Step 3: Payment::process($planId)
   ↓
Step 4: PayFast Payment Page
   ├─ Merchant enters payment details
   ├─ Confirms payment
   └─ PayFast processes payment
   ↓
Step 5: PayFast ITN (Instant Transaction Notification)
   ├─ Payment::notify() receives confirmation
   ├─ Updates subscription status to 'active'
   └─ Sets billing period dates
   ↓
Step 6: Merchant Redirected to Success Page
   ↓
Step 7: Full Access Granted ✅
```

## Subscription Statuses Explained

### For Merchants:

| Status | What It Means | What Merchant Sees | Action Required |
|--------|---------------|-------------------|-----------------|
| **new** | Plan selected, payment pending | "Payment Required" badge | Complete payment via PayFast |
| **trial_pending** | Trial plan selected, payment method needed | "Payment Setup Required" badge | Add payment method (no charge yet) |
| **trial** | Free trial active | "X-Day Free Trial (Y days left)" | None - enjoy trial period |
| **active** | Paid and active | "Active" badge | None - all features available |
| **past_due** | Billing period ended | "Past Due" badge | Renew subscription |
| **expired** | Trial ended without payment | "Expired" badge | Subscribe to continue |
| **cancelled** | Manually cancelled | "Cancelled" badge | Resubscribe if needed |

## Button Behavior by Status

### Dashboard Payment Button:
- **new**: "Complete Payment Now →" (redirects to PayFast)
- **trial_pending**: "Complete Payment Setup →" (redirects to PayFast)
- **trial**: No payment button (trial active)
- **active**: No payment button (subscription active)
- **past_due**: "Renew Subscription" (redirects to renewal payment)
- **expired**: "Subscribe Now" (redirects to plan selection)

### Subscription Page Buttons:
- **new/trial_pending**: 
  - Primary: "Complete Payment" (green, prominent)
  - Secondary: "Change Plan" (gray)
  - No "Cancel Subscription" button
- **active/trial**:
  - Primary: "Change Plan" (blue)
  - Secondary: "Cancel Subscription" (red)
- **past_due**:
  - Primary: "Renew Subscription" (green)

## Feature Access Control

### Blocked Features (Unpaid Subscriptions):
- ❌ Creating/editing listings
- ❌ Managing orders
- ❌ Adding branch locations
- ❌ Viewing analytics
- ❌ Accessing merchant tools

### Allowed Features (Unpaid Subscriptions):
- ✅ View dashboard (with payment alert)
- ✅ View/edit profile
- ✅ View subscription page
- ✅ Change plan selection
- ✅ Complete payment
- ✅ Logout

## Plan Change Behavior

### If Subscription is Unpaid (new/trial_pending):
1. Merchant can change plan at any time
2. System updates subscription with new plan_id
3. Redirects to PayFast with new plan amount
4. No prorata calculation (no previous payment)

### If Subscription is Active:
1. System calculates prorata amount
2. Shows breakdown preview
3. If upgrade: Redirects to PayFast for difference
4. If downgrade: Applies immediately with credit

## Error Messages

### When Blocked by SubscriptionFilter:
```
Payment required! Please complete your payment to activate your 
subscription and access premium features. Click "Complete Payment" 
below to proceed to PayFast.
```

### When No Plan Selected:
```
No plan selected. Please choose a plan first.
```

### When Payment Fails:
```
Payment failed. Please try again or contact support.
```

## Support Information

If merchants encounter issues:
- Phone/WhatsApp: +27687781223
- Email: support@truckersafrica.com
- Payment issues are handled by PayFast support

