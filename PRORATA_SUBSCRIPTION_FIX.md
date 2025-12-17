# Prorata Subscription Plan Change Fix

## Problem

When merchants changed subscription plans via the prorata payment flow:
1. Click "Change Plan" → Select new plan → Preview prorata charges → "Proceed to PayFast"
2. Complete payment successfully on PayFast sandbox
3. Redirected back to `/merchant/subscription/prorata-success` with success message
4. **BUG**: Subscription plan did NOT actually change - still shows old plan

## Root Cause

The issue was in the `prorataSuccess()` method in `app/Controllers/Subscription.php:403-407`.

**Original Code:**
```php
public function prorataSuccess()
{
    session()->setFlashdata('success', 'Payment successful! Your plan has been upgraded.');
    return redirect()->to('merchant/subscription');
}
```

**The Problem:**
- The method only showed a success message but **didn't actually apply the plan change**
- It relied entirely on PayFast ITN (Instant Transaction Notification) to trigger the plan change via `Payment::notify()`
- PayFast ITN is **asynchronous** and may not fire immediately, especially in sandbox mode
- This left a gap where users completed payment but their subscription wasn't updated

## Solution

### 1. Enhanced `prorataSuccess()` Method

Updated the method to **apply the plan change immediately as a fallback** when the user returns from PayFast, rather than waiting for ITN:

```php
public function prorataSuccess()
{
    $merchantId = session()->get('merchant_id');
    if (!$merchantId) {
        return redirect()->to('/login');
    }

    // Get plan change details from query parameters (passed by PayFast return)
    $subscriptionId = $this->request->getGet('subscription_id');
    $newPlanId = $this->request->getGet('plan_id');

    // If we don't have the IDs from query params, try to get from session
    if (!$subscriptionId || !$newPlanId) {
        $preview = session()->get('plan_change_preview');
        if ($preview) {
            $subscriptionId = $preview['subscription_id'];
            $newPlanId = $preview['new_plan_id'];
        }
    }

    // Apply the plan change as a fallback (in case ITN hasn't been received yet)
    if ($subscriptionId && $newPlanId) {
        $subscription = $this->subscriptionModel->find($subscriptionId);

        // Only apply if subscription belongs to this merchant and plan hasn't changed yet
        if ($subscription && $subscription['merchant_id'] == $merchantId && $subscription['plan_id'] != $newPlanId) {
            $prorataService = new \App\Services\ProrataBillingService();
            $success = $prorataService->applyPlanChange($subscriptionId, $newPlanId);

            if ($success) {
                log_message('info', 'Prorata plan change applied immediately after payment success (fallback): Subscription ' . $subscriptionId . ' to plan ' . $newPlanId);

                // Clear session data
                session()->remove('plan_change_preview');

                session()->setFlashdata('success', 'Payment successful! Your plan has been upgraded to ' . $this->planModel->find($newPlanId)['name'] . '.');
            } else {
                log_message('error', 'Failed to apply prorata plan change after payment success: Subscription ' . $subscriptionId . ' to plan ' . $newPlanId);
                session()->setFlashdata('success', 'Payment successful! Your plan upgrade will be processed shortly.');
            }
        } else {
            // Plan already changed (ITN was faster) or invalid subscription
            session()->remove('plan_change_preview');
            session()->setFlashdata('success', 'Payment successful! Your plan has been upgraded.');
        }
    } else {
        // No plan change details available
        session()->setFlashdata('success', 'Payment successful! Your plan has been upgraded.');
    }

    return redirect()->to('merchant/subscription');
}
```

### 2. Updated PayFast Return URL

Modified `prorataPayment()` to include subscription_id and plan_id in the return URL:

**Before:**
```php
$data['return_url'] = site_url('merchant/subscription/prorata-success');
```

**After:**
```php
$data['return_url'] = site_url('merchant/subscription/prorata-success') . '?subscription_id=' . $subscriptionId . '&plan_id=' . $newPlanId;
```

This ensures the required IDs are available when the user returns from PayFast.

## How It Works Now

### Dual-Path Plan Change Application

The fix implements a **dual-path approach** for reliability:

1. **Immediate Path (NEW)**: When user returns from successful PayFast payment → `prorataSuccess()` applies the plan change immediately
2. **ITN Path (EXISTING)**: When PayFast sends ITN → `Payment::notify()` also applies the plan change

**Safety Mechanism:**
- The code checks if the plan has already been changed before applying
- If ITN arrives first, the immediate path skips the update
- If immediate path updates first, ITN will also skip (idempotent)
- This prevents double-updates while ensuring the change always happens

### Flow Diagram

```
User clicks "Proceed to PayFast"
    ↓
PayFast Payment Page
    ↓
Payment Success
    ├─→ Return URL (Immediate) → prorataSuccess() → Apply Plan Change ✓
    └─→ ITN (Async) → Payment::notify() → Apply Plan Change (skipped if already done)
```

## Files Changed

1. **app/Controllers/Subscription.php**
   - Line 325: Updated return URL to include subscription_id and plan_id
   - Lines 403-454: Rewrote `prorataSuccess()` method to apply plan change immediately

## Testing

To verify the fix:

1. Login as a merchant with an active subscription
2. Navigate to `/merchant/subscription`
3. Click "Change Plan"
4. Select a higher-tier plan (e.g., Small → Medium)
5. Review prorata breakdown and click "Proceed to Payment"
6. Complete payment on PayFast sandbox
7. **Verify**: Upon return, subscription should immediately show the new plan
8. Check logs for: `"Prorata plan change applied immediately after payment success"`

## Edge Cases Handled

1. **ITN arrives before return**: Plan already changed, return path skips update
2. **No query params in return URL**: Falls back to session data
3. **Subscription already updated**: Checks current plan_id before applying
4. **Invalid subscription/merchant**: Validates ownership before updating
5. **Database failure**: Logs error and shows appropriate message

## Benefits

- ✅ Immediate feedback to merchant (no waiting for ITN)
- ✅ Idempotent (safe to call multiple times)
- ✅ Backward compatible (ITN path still works)
- ✅ Better user experience (instant plan change)
- ✅ Handles PayFast sandbox ITN delays gracefully

## Related Files

- `app/Controllers/Payment.php:253-289` - ITN handler for prorata payments (unchanged, still works)
- `app/Services/ProrataBillingService.php:242-261` - `applyPlanChange()` method (unchanged)
- `SUBSCRIPTION_PLAN_CHANGE_FIX.md` - Earlier documentation (this complements it)
