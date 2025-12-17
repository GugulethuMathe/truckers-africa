# Merchant Profile and Location Synchronization

## Overview

When a merchant updates their business profile at `http://localhost/truckers-africa/profile/merchant/edit`, the system now automatically updates the corresponding primary location's address and contact information to keep everything in sync.

## What Gets Updated

When a merchant updates their profile, the following fields are synchronized to the **primary location** (or first active location if no primary exists):

### Address Information
- **Physical Address** - The full business address
- **Latitude & Longitude** - GPS coordinates from the address autocomplete

### Contact Information
- **Contact Number** - Business contact number
- **WhatsApp Number** - Business WhatsApp number
- **Email** - Business email address

### Location Name
- **Location Name** - Automatically set to `{Business Name} - Main Branch`

## Implementation Details

### File Modified
`app/Controllers/Profile.php`

### New Method Added
```php
private function updatePrimaryLocationAddress(int $merchantId, array $merchantData): bool
```

This method:
1. Finds the merchant's primary location (or first active location)
2. Updates the location with the new address, coordinates, and contact info from the profile
3. Logs the update for tracking purposes
4. Handles cases where no location exists gracefully

### Update Flow

```
Merchant Profile Update
    ↓
Check if merchant has locations
    ↓
├─ No Locations → Auto-create primary location (existing behavior)
    ↓
└─ Has Locations → Update primary location address and contact info (NEW)
    ↓
Success message shown to merchant
```

## Key Features

### Smart Location Selection
- First tries to find the **primary location** (is_primary = 1)
- Falls back to the first active location if no primary exists
- Logs info message if no locations found (non-critical)

### Selective Updates
- Only updates fields that have been provided in the profile update
- Doesn't overwrite location data with empty values
- Returns false if no data needs updating

### Error Handling
- Profile update succeeds even if location update fails
- All errors are logged but don't break the user experience
- Non-critical failures are handled gracefully

### Automatic Synchronization
- No user action required
- Happens transparently during profile update
- Keeps profile and location data consistent

## Benefits

1. **Data Consistency** - Profile address always matches primary location address
2. **User Convenience** - Merchants don't need to update address in two places
3. **Driver Experience** - Drivers always see accurate location information
4. **Reduced Errors** - Eliminates address mismatches between profile and location

## Testing

To test the functionality:

1. Log in as a merchant with an existing location
2. Go to `http://localhost/truckers-africa/profile/merchant/edit`
3. Update the physical address (use autocomplete to select from dropdown)
4. Update business name, contact number, or WhatsApp number
5. Submit the form
6. Check the merchant_locations table to verify the primary location was updated

### SQL Query to Verify
```sql
SELECT
    m.id as merchant_id,
    m.business_name,
    m.physical_address as profile_address,
    ml.location_name,
    ml.physical_address as location_address,
    ml.latitude,
    ml.longitude,
    ml.is_primary
FROM merchants m
LEFT JOIN merchant_locations ml ON m.id = ml.merchant_id
WHERE m.id = [YOUR_MERCHANT_ID]
ORDER BY ml.is_primary DESC;
```

## Logging

The system logs the following events:
- **Success**: `"Updated primary location (ID: X) address for merchant Y"`
- **No locations found**: `"No locations found for merchant X, skipping location update"`
- **Failure**: `"Failed to update primary location address: [error message]"`

All logs can be found in `writable/logs/log-[date].log`

## Edge Cases Handled

1. **Merchant has no locations** - Skips update, doesn't fail
2. **Merchant has locations but no primary** - Updates first active location
3. **Empty fields in profile update** - Doesn't overwrite location data
4. **Location update fails** - Profile update still succeeds
5. **Business name changes** - Location name updates to match

## Related Files

- `app/Controllers/Profile.php` - Main implementation
- `app/Models/MerchantLocationModel.php` - Location model with helper methods
- `app/Views/merchant/profile.php` - Profile edit form

## Future Enhancements

Potential improvements for future versions:
- Add option to exclude certain locations from auto-sync
- Batch update all merchant locations (not just primary)
- Add notification to merchant when location is auto-updated
- Create audit trail for location address changes
