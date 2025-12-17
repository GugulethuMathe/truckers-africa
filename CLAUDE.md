# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Truckers Africa** is a location-aware web application serving truck drivers across Africa by connecting them with nearby merchants offering services like mechanical repairs, border clearing, rest stops, and meals. The platform uses CodeIgniter 4 with a three-tier user system: drivers (free), merchants (subscription-based), and administrators.

## Technology Stack

- **Framework**: CodeIgniter 4
- **Database**: MySQL (accessed via MySQLi driver)
- **Frontend**: HTML5, Tailwind CSS, Vanilla JavaScript
- **Server**: PHP 7.4+ / 8.0+
- **Dependency Management**: Composer
- **PDF Generation**: dompdf, tcpdf, fpdf
- **Authentication**: JWT for mobile API, session-based for web
- **Payment Gateway**: PayFast (sandbox for development, production credentials in .env)

## Essential Commands

### Development Server
```bash
# Start PHP development server (if not using XAMPP)
php spark serve

# Access via browser
http://localhost/truckers-africa/
```

### Database
```bash
# Run migrations
php spark migrate

# Rollback migrations
php spark migrate:rollback

# Create new migration
php spark make:migration MigrationName

# Run specific migration
php spark migrate --all
```

### Testing
```bash
# Run all tests
composer test
# OR
vendor/bin/phpunit

# Run specific test file
vendor/bin/phpunit tests/SomeTest.php

# Run with coverage
vendor/bin/phpunit --coverage-html build/logs/html
```

### Code Quality
```bash
# Run PHP CS Fixer
vendor/bin/php-cs-fixer fix

# Run with dry run
vendor/bin/php-cs-fixer fix --dry-run --diff
```

### Cache & Cleanup
```bash
# Clear cache
php spark cache:clear

# Clear routes cache
php spark route:clear
```

### Custom Commands
```bash
# Update currency exchange rates
php spark UpdateExchangeRates

# Send subscription expiry reminders
php spark SendSubscriptionReminders
```

## Architecture & Code Organization

### Multi-User System

The application has **three distinct user types** with separate authentication flows:

1. **Drivers** (free users)
   - Login: `/login`
   - Model: `TruckDriverModel`
   - Session key: `driver_id`, `user_role: 'driver'`
   - Dashboard: `DriverDashboard` controller

2. **Merchants** (subscription-based)
   - Login: `/login` (same route, different role)
   - Model: `MerchantModel`
   - Session key: `merchant_id`, `user_role: 'merchant'`
   - Dashboard: `MerchantDashboard` controller

3. **Branch Managers** (per-location merchant users)
   - Login: `/branch/login`
   - Model: `BranchUserModel`
   - Session key: `branch_user_id`, `branch_location_id`
   - Dashboard: `BranchDashboard` controller

4. **Administrators**
   - Login: `/admin/login`
   - Model: `AdminModel`
   - Filter: `AdminAuthFilter`
   - Dashboard: `Admin` controller

### Important: Login Route Consistency

**CRITICAL**: The standard login route is `/login`, NOT `/auth/login`. When redirecting unauthenticated users, always use:
```php
return redirect()->to('login')->with('error', 'Message');
```

### Multi-Location/Branch System

Merchants can have **multiple physical locations** (branches), each with:
- Independent login credentials for branch managers (`branch_users` table)
- Separate order queues per location
- Location-specific analytics and reporting
- One-to-one relationship: each location has ONE branch user

**Key Implementation Details**:
- Orders are grouped by `location_id`, NOT by `merchant_id`
- Cart items MUST include `location_id` for proper order separation
- When viewing orders as a merchant, aggregate across all locations
- When viewing orders as a branch manager, filter by specific `location_id`

### Mobile API Architecture

The mobile app API is versioned and JWT-protected:

- **Base Route**: `/api/v1/`
- **Authentication**: Bearer JWT tokens (configured in .env)
- **Public Endpoints**: Login, register, service discovery, nearby searches
- **Protected Endpoints**: Profile updates, route planning, order management
- **Controller**: `ApiController`

JWT Configuration (in .env):
```
JWT_SECRET_KEY = [key]
JWT_ALGORITHM = HS256
JWT_EXPIRATION_TIME = 2592000  # 30 days
JWT_ISSUER = truckers-africa-api
JWT_AUDIENCE = truckers-africa-mobile-app
```

### Service & Listing System

**Two-level hierarchy**:
1. **Service Categories** (`service_categories`) - e.g., "Mechanical Repairs", "Accommodation"
2. **Services** (`services`) - e.g., "Tyre Repair", "Oil Change"
3. **Merchant Listings** (`merchant_listings`) - Merchant-specific offerings of services at locations

**Merchant Listings** represent what a merchant offers at a specific location:
- Linked to `merchant_locations.id` (NOT merchant_id directly)
- Can have multiple images (`merchant_listing_images`)
- Can offer multiple services (`merchant_listing_services` junction table)
- Approval workflow: `listing_status` (pending, approved, rejected)

### Subscription & Payment System

**Subscription Flow**:
1. Merchant registers → account created but inactive
2. Merchant selects a plan from `subscription_plans`
3. Plan has features (`plan_features`) and limitations (`plan_limitations`)
4. Payment via PayFast integration
5. Subscription activated → listings become visible to drivers

**PayFast Integration**:
- Sandbox mode for development (merchant ID: 10000100)
- Production credentials configured in .env
- Payment controller: `Payment`
- Routes: `/payment/process`, `/payment/success`, `/payment/cancel`, `/payment/notify`

**Prorated Billing**: When merchants change plans mid-cycle, prorated amounts are calculated and processed.

### Currency System

Multi-currency support for African markets:
- Driver currency preferences stored in session and `truck_drivers.preferred_currency`
- Exchange rates stored in `currencies` table
- Automatic conversion on the fly for listings/orders
- Cron job updates rates: `/cron/update-currency-rates`
- API endpoints: `/api/v1/currencies`, `/api/v1/currency/convert`

### Route Planning System

Drivers can plan multi-stop routes and discover merchants along the way:
- Controller: `Routes`, `RoutePlanner`
- Models: `PlannedRouteModel`, `RouteStopModel`, `RouteMerchantModel`
- Geolocation API: Geoapify (API key in .env)
- Routes can be saved for future reference (`is_saved` flag)

**Key Files**:
- `app/Controllers/Routes.php` - Web route planning
- `app/Controllers/RoutePlanner.php` - Route calculation logic
- `app/Views/routes/index.php` - Route planning interface
- `app/Views/routes/view.php` - View saved route

### Order Management System

**Order Creation Flow**:
1. Driver adds items to cart (localStorage on frontend)
2. Cart items include `location_id` for branch separation
3. Checkout groups items by `location_id` (NOT merchant_id)
4. Creates separate `orders` records for each location
5. Each order gets unique booking reference
6. Order items stored in `order_items` (products) and `order_services` (services)

**Order States**: pending, accepted, completed, rejected

**Key Controllers**:
- `Order::checkout()` - Display checkout page
- `Order::completeOrder()` - Process order submission (groups by location_id)
- `Order::receipt()` - Single order receipt
- `Order::multiReceipt()` - Multi-order receipt (one checkout = multiple orders)

**Important**: Always group orders by `location_id` in checkout to ensure branch separation.

### Authentication Filters

- `MerchantAuth` - Protects merchant routes, redirects to `/login`
- `AdminAuthFilter` - Protects admin routes, redirects to `/admin/login`
- `JwtAuthFilter` - Validates JWT Bearer tokens for mobile API routes
- `SubscriptionFilter` - Ensures merchant has active subscription before accessing features
- `CorsFilter` - Handles CORS for API endpoints
- No explicit filter for drivers - checks done in controller via `session()->get('user_role')`

## Important Database Relationships

### Core Tables

- `truck_drivers` - Driver accounts
- `merchants` - Merchant business accounts
- `merchant_locations` - Physical locations/branches
- `branch_users` - Branch manager accounts (1:1 with locations)
- `merchant_listings` - Services/products offered at locations
- `orders` - Order records (linked to location_id)
- `subscription_plans` - Available subscription tiers
- `subscriptions` - Active merchant subscriptions

### Key Foreign Keys

- `merchant_locations.merchant_id` → `merchants.id`
- `branch_users.location_id` → `merchant_locations.id` (UNIQUE)
- `merchant_listings.location_id` → `merchant_locations.id`
- `orders.location_id` → `merchant_locations.id`
- `orders.driver_id` → `truck_drivers.id`
- `subscriptions.merchant_id` → `merchants.id`

## Configuration Files

### Environment (.env)

**Database Configuration**:
- For localhost: `database.tests.*` settings (username: root, no password)
- For production: `database.default.*` settings (commented out)

**API Keys**:
- `GEOAPIFY_API_KEY` - Address autocomplete and geocoding
- `GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET` - OAuth login

**PayFast Credentials**:
- Sandbox for development (merchant ID: 10000100)
- Production credentials commented out

**Email Configuration**:
- SMTP settings for transactional emails
- Using mail.truckersafrica.com

### Routes (app/Config/Routes.php)

**Route Organization**:
1. API routes MUST come first (`/api/v1/` group)
2. Public routes (home, search, listing views)
3. Authentication routes (login, register, password reset)
4. Driver routes (`/driver/*`)
5. Merchant routes (`/merchant/*` group)
6. Branch routes (`/branch/*` group)
7. Admin routes (`/admin/*` group)

**Common Pitfalls**:
- Duplicate route definitions exist (old and new patterns) - newer routes take precedence
- Admin routes defined twice (in group and outside) - use the grouped version
- Some legacy API endpoints exist outside `/api/v1/` for backward compatibility

## Development Workflow

### Adding a New Feature

1. **Create migration** if database changes needed:
   ```bash
   php spark make:migration AddFeatureToTable
   ```

2. **Create/update model** in `app/Models/`:
   ```php
   namespace App\Models;
   use CodeIgniter\Model;

   class FeatureModel extends Model {
       protected $table = 'table_name';
       protected $primaryKey = 'id';
       protected $allowedFields = ['field1', 'field2'];
   }
   ```

3. **Create/update controller** in `app/Controllers/`:
   ```php
   namespace App\Controllers;

   class Feature extends BaseController {
       public function index() {
           // Implementation
       }
   }
   ```

4. **Add routes** in `app/Config/Routes.php`:
   ```php
   $routes->get('feature', 'Feature::index');
   ```

5. **Create views** in `app/Views/`:
   - Use appropriate template: `driver/templates/`, `merchant/templates/`, `admin/templates/`, or `branch/templates/`

6. **Test functionality** before committing

### Common Development Tasks

**Adding a new merchant service**:
1. Add to `services` table via admin panel (`/admin/services/add`)
2. Assign to service category
3. Merchant can then add it to their listings

**Creating a new subscription plan**:
1. Use admin panel: `/admin/plans/create`
2. Define features and limitations
3. Set pricing and trial period
4. Plan becomes available in merchant signup flow

**Adding a new currency**:
1. Insert into `currencies` table with exchange rate
2. Cron job will update rates automatically
3. Drivers can select in settings

## Common Issues & Solutions

### "404 Not Found" on Login Redirect

**Symptom**: Redirecting to `/auth/login` shows 404
**Solution**: Use `/login` instead - the auth prefix is only for password reset routes

### Orders Not Separating by Branch

**Symptom**: Multiple branch orders grouped into one
**Solution**: Ensure cart items include `location_id` field and checkout groups by `location_id`, not `merchant_id`

### JWT Authentication Failing

**Symptom**: API returns 401 Unauthorized
**Solution**: Check JWT secret in .env matches token generation, verify token hasn't expired (30-day default)

### PayFast Payment Failing

**Symptom**: Payment redirect fails or shows error
**Solution**:
- Verify using sandbox credentials for localhost
- Check passphrase matches .env setting
- Ensure notify URL is accessible

### Session Lost After Login

**Symptom**: User logged out immediately
**Solution**: Check session configuration in `app/Config/Session.php`, verify writable directory permissions

## File Upload Locations

- **Merchant Listing Images**: `uploads/listings/`
- **Merchant Documents**: `uploads/documents/`
- **Driver Documents**: `uploads/driver-documents/`

## Important Constants

**User Roles** (stored in session `user_role`):
- `'driver'` - Truck driver
- `'merchant'` - Merchant owner
- `'branch'` - Branch manager
- `'admin'` - Administrator

**Merchant Status**:
- `pending` - Awaiting approval
- `approved` - Verified and active
- `rejected` - Application denied
- `suspended` - Temporarily deactivated

**Listing Status**:
- `pending` - Awaiting admin approval
- `approved` - Visible to drivers
- `rejected` - Not approved

**Subscription Status**:
- `trial` - Free trial period
- `active` - Paid and active
- `expired` - Subscription ended
- `cancelled` - Manually cancelled

## Debugging Tips

**View Current Session Data**:
```php
// Add to any controller
dd(session()->get());
```

**Check Database Connection**:
```bash
php spark db:table users
```

**Enable Debug Mode** (in .env):
```
CI_ENVIRONMENT = development
```

**View Routes**:
```bash
php spark routes
```

**Check Logs**:
- Location: `writable/logs/`
- Format: `log-YYYY-MM-DD.log`

## Third-Party Libraries

**Composer Dependencies**:
- `firebase/php-jwt` - JWT token handling
- `league/oauth2-google` - Google OAuth
- `dompdf/dompdf` - PDF generation (receipts, reports)
- `phpoffice/phpspreadsheet` - Excel exports
- `predis/predis` - Redis caching (if enabled)

## Testing Guidelines

**Test Database**:
- Uses `app_truckers_africa` database
- Configured in .env under `database.tests.*`
- Keep separate from production data

**Writing Tests**:
- Place in `tests/` directory
- Extend `CodeIgniter\Test\CIUnitTestCase`
- Use `phpunit.xml.dist` configuration

## Security Considerations

**Password Hashing**:
- Use `password_hash()` with `PASSWORD_DEFAULT`
- Verify with `password_verify()`
- All user tables use `password_hash` column

**CSRF Protection**:
- Enabled by default in CodeIgniter 4
- Forms must include `<?= csrf_field() ?>`

**SQL Injection**:
- Use Query Builder methods, never raw queries
- Use bindings for user input: `$builder->where('id', $id)`

**XSS Protection**:
- Use `esc()` helper in views: `<?= esc($data) ?>`
- Enabled by default in CodeIgniter 4

## Geolocation & Mapping

**API Used**: Geoapify
- Address autocomplete in forms
- Geocoding (address → coordinates)
- Reverse geocoding (coordinates → address)
- API key in .env: `GEOAPIFY_API_KEY`

**Key Features**:
- Driver location tracking (`driver_location_history`)
- Nearby merchant search (distance-based queries)
- Route calculation with waypoints
- Archive old location data (>30 days) to `driver_location_history_archive`

## Email System

**Configuration** (in .env):
- SMTP host: mail.truckersafrica.com
- Port: 587
- Format: HTML emails

**Email Use Cases**:
- Order confirmations
- Merchant approval notifications
- Password reset emails
- Subscription reminders
- Marketing campaigns (via `email_campaigns` table)

## Cron Jobs

**Currency Rate Updates**:
```bash
curl http://localhost/truckers-africa/cron/update-currency-rates
```

**Schedule**: Daily or as needed

**Location History Archival** (if implemented):
```bash
php spark archive:locations
```

## Performance Optimization

**Database Indexing**:
- Ensure indexes on foreign keys
- Add indexes on frequently queried columns (location_id, merchant_id, status fields)

**Caching**:
- Use CodeIgniter's cache for service categories and currencies
- Cache merchant listings per location

**Query Optimization**:
- Use joins instead of multiple queries
- Limit result sets with pagination
- Use `select()` to fetch only needed columns

## Production Deployment Checklist

1. Set `CI_ENVIRONMENT = production` in .env
2. Switch to production database credentials
3. Enable PayFast production credentials
4. Update `app.baseURL` to production domain
5. Run database migrations
6. Set proper file permissions on `writable/` (775)
7. Enable Redis/Memcached for caching
8. Configure email SMTP for production
9. Set up SSL certificate
10. Configure cron jobs for currency updates
11. Test payment flow end-to-end
12. Verify Google OAuth redirect URIs

## Additional Documentation

For detailed setup and troubleshooting on specific features, see:
- `BRANCH_SYSTEM_SETUP.md` - Branch management system
- `BRANCH_SYSTEM_SUMMARY.md` - Branch system overview
- `ORDER_CHECKOUT_FIX_SUMMARY.md` - Order checkout flow
- `PAYFAST_TESTING_GUIDE.md` - Payment integration testing
- `MULTI_LOCATION_IMPLEMENTATION_GUIDE.md` - Multi-location setup
- `QUICK_START_BRANCH_SYSTEM.md` - Quick start for branch system
