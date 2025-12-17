# Auto-Create Primary Location - Implementation Summary

## Overview
Implemented automatic creation of a primary business location when a merchant updates their profile for the first time with a physical address. This eliminates the redundant step of requiring merchants to enter their address twice (once in profile, once when creating a location).

## Problem Solved
**Before**: Merchants had to:
1. Update their business profile with physical address (required field)
2. Create a branch/location with the same address (required before creating listings)
3. Enter the same information twice

**After**: Merchants only need to:
1. Update their business profile with physical address
2. System automatically creates primary location
3. Can immediately create listings

## Changes Made

### 1. Profile Controller Enhancement ✅
**File**: `app/Controllers/Profile.php`

#### Modified `updateMerchant()` Method (Lines 185-215)
Added logic to auto-create primary location after successful profile update:
```php
// Auto-create primary location if this is the first time updating profile with address
// and merchant has no locations yet
$locationModel = new \App\Models\MerchantLocationModel();
$existingLocations = $locationModel->getLocationsByMerchant($merchantId, false);

if (empty($existingLocations) && !empty($data['physical_address'])) {
    try {
        $this->autoCreatePrimaryLocation($merchantId, $data);
    } catch (\Exception $e) {
        log_message('error', 'Failed to auto-create primary location: ' . $e->getMessage());
        // Don't fail the profile update if location creation fails
    }
}
```

**Key Features**:
- Only triggers if merchant has NO existing locations
- Only triggers if physical address is provided
- Fails gracefully - profile update succeeds even if location creation fails
- Logs errors for debugging

#### Added `autoCreatePrimaryLocation()` Method (Lines 218-318)
Private helper method that creates the primary location and branch user account.

**What It Does**:
1. Creates location record with merchant's profile data
2. Sets `is_primary = 1` and `is_active = 1`
3. Creates branch user account for the location
4. Generates unique email: `merchant+branch{locationId}@domain.com`
5. Generates password setup token (valid for 7 days)
6. Updates merchant's location count
7. Sends email notification to merchant

**Data Mapping**:
- Location name: `{Business Name} - Main Branch`
- Address: From merchant profile
- Contact: From merchant profile
- Email: Merchant's email
- Coordinates: From merchant profile (if available)

#### Added `generateBranchUserEmail()` Method (Lines 320-332)
Generates unique email for auto-created branch user using email aliasing:
- Format: `localpart+branch{locationId}@domain.com`
- Example: `john@example.com` → `john+branch1@example.com`
- Works with most email providers (Gmail, Outlook, etc.)

#### Added `sendPrimaryLocationCreatedEmail()` Method (Lines 334-356)
Sends notification email to merchant about the auto-created location.

### 2. Email Template Created ✅
**File**: `app/Views/emails/primary_location_created.php`

**Email Content**:
- Confirms primary location was created
- Shows location details (name, address, contact)
- Explains what this means for the merchant
- Provides branch manager account email
- Lists next steps (create listings, add more locations, etc.)
- Links to merchant dashboard

**Styling**: Matches existing email templates with Truckers Africa branding

## How It Works

### User Flow

1. **Merchant registers** → Account created (no address required initially)
2. **Merchant updates profile** → Enters business address (required field)
3. **System checks**: Does merchant have any locations?
   - **NO** → Auto-create primary location ✅
   - **YES** → Skip (location already exists)
4. **Location created** with:
   - Name: `{Business Name} - Main Branch`
   - Address from profile
   - Marked as primary (`is_primary = 1`)
   - Active (`is_active = 1`)
5. **Branch user created** with:
   - Email: `merchant+branch{id}@domain.com`
   - Password setup token (7 days validity)
   - Linked to the location
6. **Email sent** to merchant confirming creation
7. **Merchant can now**:
   - Create service listings immediately
   - Add more locations if needed
   - Manage the auto-created location

### Technical Flow

```
Profile Update
    ↓
Check: Has locations?
    ↓ NO
Start Transaction
    ↓
Create Location Record
    ↓
Create Branch User
    ↓
Update Location Count
    ↓
Commit Transaction
    ↓
Send Email (optional)
    ↓
Success
```

## Database Changes

### merchant_locations Table
New record created with:
- `merchant_id`: Current merchant
- `location_name`: `{Business Name} - Main Branch`
- `physical_address`: From merchant profile
- `contact_number`: From merchant profile
- `whatsapp_number`: From merchant profile (if provided)
- `email`: Merchant's email
- `latitude`: From merchant profile (if provided)
- `longitude`: From merchant profile (if provided)
- `is_primary`: `1` (this is the primary location)
- `is_active`: `1` (immediately active)

### branch_users Table
New record created with:
- `location_id`: ID of created location
- `merchant_id`: Current merchant
- `email`: Generated unique email (`merchant+branch{id}@domain.com`)
- `full_name`: Merchant's owner name
- `phone_number`: Business contact number
- `password_hash`: Temporary random password
- `password_reset_token`: Setup token (valid 7 days)
- `is_active`: `1`
- `created_by`: Merchant ID

## Benefits

### For Merchants
✅ **No duplicate data entry** - Enter address once
✅ **Faster onboarding** - One less step
✅ **Immediate listing creation** - No "create location first" error
✅ **Clear communication** - Email explains what happened
✅ **Flexibility** - Can still add more locations manually

### For System
✅ **Better UX** - Removes friction point
✅ **Data consistency** - Profile address matches location address
✅ **Automatic setup** - No manual intervention needed
✅ **Graceful failure** - Profile update succeeds even if location creation fails

## Edge Cases Handled

1. **Merchant already has locations**: Skip auto-creation
2. **No address provided**: Skip auto-creation
3. **Location creation fails**: Log error, profile update still succeeds
4. **Email sending fails**: Log warning, location still created
5. **Transaction failure**: Rollback, no partial data
6. **Duplicate email**: Uses email aliasing to ensure uniqueness

## Testing Checklist

- [ ] New merchant registers
- [ ] Merchant updates profile with address (first time)
- [ ] Verify location created automatically
- [ ] Verify location marked as primary
- [ ] Verify branch user created
- [ ] Verify email sent to merchant
- [ ] Verify merchant can create listings immediately
- [ ] Test: Update profile again (should NOT create duplicate location)
- [ ] Test: Merchant with existing location updates profile (should NOT create new location)
- [ ] Test: Update profile without address (should NOT create location)

## Future Enhancements

1. **Option to skip auto-creation**: Add checkbox in profile form
2. **Use profile address as default**: Pre-fill location form with profile data
3. **Sync updates**: When profile address changes, update primary location
4. **Branch user activation**: Send setup email to branch user separately

## Related Files

- `app/Controllers/Profile.php` - Main implementation
- `app/Views/emails/primary_location_created.php` - Email template
- `app/Models/MerchantLocationModel.php` - Location model
- `app/Models/BranchUserModel.php` - Branch user model
- `app/Controllers/MerchantLocations.php` - Manual location creation
- `app/Controllers/MerchantListingsController.php` - Listing creation (checks for locations)

## Notes

- Auto-creation only happens **once** (first profile update with address)
- Merchant can still manually add more locations
- Branch user email uses aliasing (`+branch{id}`) for uniqueness
- Password setup token valid for 7 days (longer than manual creation's 48 hours)
- Email notification is optional - location created even if email fails
- Transaction ensures data consistency (all or nothing)

---

**Implementation Date**: 2025-11-19
**Status**: ✅ Complete and Ready for Testing

