# Route Save Functionality Fix

## Problem

When drivers planned routes on `/driver/routes`, the routes were automatically appearing in the "Saved Routes" section without the user explicitly saving them. This happened because:

1. **Auto-save on route planning**: When a route was planned, it was automatically saved to the database with `is_saved = 1`
2. **No distinction between planned and saved**: There was no difference between a route that was just planned vs. a route the user wanted to keep

This caused confusion because:
- Every route planned appeared in "Saved Routes"
- Users couldn't distinguish between temporary route planning and routes they wanted to save
- The "Save Route" button didn't have a clear purpose

## Solution Implemented

### 1. Changed Default `is_saved` Value

**File**: `app/Controllers/Routes.php` - Line 208

Changed from:
```php
'is_saved' => 1, // Mark newly created routes as saved
```

To:
```php
'is_saved' => isset($json['is_saved']) ? (int) $json['is_saved'] : 0,
```

**Result**: Routes are now created with `is_saved = 0` by default (not saved), unless explicitly specified.

### 2. Updated Save Route Button Behavior

**File**: `app/Views/driver/routes.php` - Lines 2790-2908

The `saveCurrentRoute()` function now:

**If route was auto-saved** (has `savedRouteId`):
- Calls `/routes/toggle-saved/{routeId}` to update `is_saved` to `1`
- Changes button to "Saved" with success styling
- Disables button to prevent duplicate saves
- Reloads page to show in "Saved Routes" section

**If route doesn't exist yet**:
- Creates new route with `is_saved = 1` explicitly set
- Saves with all route data and stops
- Changes button to "Saved" with success styling
- Stores route ID for future reference

### 3. Added Success Button Styling

**File**: `app/Views/driver/routes.php` - Lines 331-343

Added `.btn-success` class:
```css
.btn-success {
    background: #10b981; /* Success green */
    color: white;
    border: none;
    padding: 14px 24px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: not-allowed;
    opacity: 0.8;
    width: 100%;
}
```

This provides visual feedback when a route is saved.

## How It Works Now

### Route Planning Flow

1. **Driver plans a route**:
   - Enters start and end addresses
   - Optionally adds stops
   - Clicks "Plan Route"

2. **Route is auto-saved** (for persistence):
   - Saved to database with `is_saved = 0`
   - Route ID stored in `currentRoute.savedRouteId`
   - Route does NOT appear in "Saved Routes" section
   - Allows driver to navigate away and come back without losing route

3. **Driver clicks "Save Route"**:
   - If route was auto-saved: Updates `is_saved` to `1` via toggle endpoint
   - If route wasn't auto-saved: Creates new route with `is_saved = 1`
   - Button changes to "Saved" with green styling
   - Button becomes disabled
   - Page reloads after 1.5 seconds

4. **Route appears in "Saved Routes"**:
   - Only routes with `is_saved = 1` appear in this section
   - Driver can view, use, or remove saved routes
   - Clicking bookmark icon toggles `is_saved` status

### Database Schema

**Table**: `planned_routes`

Key field:
- `is_saved` (tinyint): 
  - `0` = Route planned but not saved (temporary)
  - `1` = Route explicitly saved by user (appears in "Saved Routes")

### API Endpoints Used

1. **POST `/routes/create`**
   - Creates new route
   - Accepts `is_saved` parameter (defaults to 0)
   - Returns `route_id` on success

2. **POST `/routes/toggle-saved/{routeId}`**
   - Toggles `is_saved` status for existing route
   - Returns new `is_saved` status
   - Only works for routes owned by logged-in driver

## User Experience

### Before Fix
- ❌ Every planned route appeared in "Saved Routes"
- ❌ No way to distinguish temporary vs. saved routes
- ❌ "Save Route" button had unclear purpose
- ❌ Saved Routes section cluttered with temporary routes

### After Fix
- ✅ Only explicitly saved routes appear in "Saved Routes"
- ✅ Clear distinction between planning and saving
- ✅ "Save Route" button has clear purpose and feedback
- ✅ Saved Routes section only shows routes user wants to keep
- ✅ Auto-save still works for persistence (doesn't clutter saved routes)

## Testing

### Test Scenario 1: Plan and Save Route
1. Go to `/driver/routes`
2. Enter start and end addresses
3. Click "Plan Route"
4. Verify route appears on map
5. Navigate away (e.g., to dashboard)
6. Come back to `/driver/routes`
7. Verify "Saved Routes" section is empty (or doesn't show new route)
8. Plan the route again
9. Click "Save Route" button
10. Verify button changes to "Saved" with green styling
11. Wait for page reload
12. Verify route now appears in "Saved Routes" section

### Test Scenario 2: Plan Without Saving
1. Go to `/driver/routes`
2. Enter start and end addresses
3. Click "Plan Route"
4. Navigate away WITHOUT clicking "Save Route"
5. Come back to `/driver/routes`
6. Verify "Saved Routes" section doesn't show the route

### Test Scenario 3: Toggle Saved Status
1. Go to `/driver/routes`
2. Find a saved route in "Saved Routes" section
3. Click the bookmark icon
4. Verify route is removed from "Saved Routes"
5. Verify success notification appears

## Files Modified

1. ✅ `app/Controllers/Routes.php`
   - Line 208: Accept `is_saved` parameter from request

2. ✅ `app/Views/driver/routes.php`
   - Lines 327-343: Added `.btn-success` CSS class
   - Lines 2790-2908: Updated `saveCurrentRoute()` function

## Database Changes

No migration needed - `is_saved` column already exists in `planned_routes` table.

**To clean up existing routes** (optional):
```sql
-- Mark all existing routes as not saved
UPDATE planned_routes SET is_saved = 0;

-- Or delete routes older than 7 days that aren't saved
DELETE FROM planned_routes 
WHERE is_saved = 0 
AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY);
```

## Future Enhancements

Potential improvements:
1. Add "Recent Routes" section for unsaved routes (last 5-10)
2. Add route naming functionality
3. Add route sharing between drivers
4. Add route templates for common trips
5. Auto-delete unsaved routes after X days
6. Add route categories/tags

## Notes

- Auto-save functionality is preserved for persistence across page navigation
- Routes are still saved to database immediately (for crash recovery)
- Only the `is_saved` flag determines visibility in "Saved Routes"
- This approach balances persistence with user control

