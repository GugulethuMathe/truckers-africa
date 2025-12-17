# Truckers Africa - Technical Documentation

## Technology Stack

| Component | Technology |
|-----------|------------|
| **Framework** | CodeIgniter 4 (PHP 8.0+) |
| **Database** | MySQL/MariaDB |
| **Frontend** | HTML5, Tailwind CSS, Vanilla JavaScript, Alpine.js |
| **Maps/Geocoding** | Geoapify API, Leaflet.js |
| **Payment Gateway** | PayFast (South Africa) |
| **Authentication** | Session-based (Web), JWT (Mobile API) |
| **Email** | SMTP via mail.truckersafrica.com |
| **PDF Generation** | dompdf, tcpdf, fpdf |
| **OAuth** | Google Sign-In |

---

## Architecture Overview

### User Types & Authentication

| User Type | Login Route | Session Keys | Controller |
|-----------|-------------|--------------|------------|
| **Drivers** | `/login` | `driver_id`, `user_role: 'driver'` | `DriverDashboard` |
| **Merchants** | `/login` | `merchant_id`, `user_role: 'merchant'` | `MerchantDashboard` |
| **Branch Managers** | `/branch/login` | `branch_user_id`, `branch_location_id` | `BranchDashboard` |
| **Administrators** | `/admin/login` | `admin_id` | `Admin` |

### Authentication Filters
- `MerchantAuth` - Protects merchant routes
- `AdminAuthFilter` - Protects admin routes
- `JwtAuthFilter` - Validates JWT for mobile API
- `SubscriptionFilter` - Ensures active subscription

---

## Database Schema (Key Tables)

### Core User Tables
```
admins              - Administrator accounts
truck_drivers       - Driver accounts (free users)
merchants           - Business accounts (subscription-based)
merchant_locations  - Physical branch locations
branch_users        - Branch manager accounts (1:1 with locations)
```

### Service & Listing Tables
```
service_categories      - Category hierarchy (Mechanical, Food, etc.)
services               - Individual services (Tyre Repair, Oil Change)
merchant_listings      - What merchants offer at locations
merchant_listing_images - Gallery images for listings
merchant_listing_services - Junction: listings ↔ services
```

### Order Management
```
master_orders    - Main order records
order_items      - Products in orders
order_services   - Services in orders
```

### Subscription System
```
subscription_plans  - Available plans (Basic, Standard, Premium)
plan_features      - Features included in each plan
plan_limitations   - Limits per plan (max listings, images, etc.)
subscriptions      - Active merchant subscriptions
payment_transactions - Payment history
```

### Email Marketing
```
email_marketing_leads  - Lead database
email_campaigns       - Campaign records
email_campaign_leads  - Junction with send status
```

---

## Key Relationships

```
merchants (1) ──────── (N) merchant_locations
merchant_locations (1) ── (1) branch_users
merchant_locations (1) ── (N) merchant_listings
merchant_listings (1) ─── (N) merchant_listing_images
master_orders (N) ──────── (1) merchant_locations
master_orders (N) ──────── (1) truck_drivers
subscriptions (N) ──────── (1) merchants
```

---

## API Architecture

### Mobile API (JWT Protected)
**Base URL**: `/api/v1/`

#### Public Endpoints
```
POST /api/v1/driver/login      - Driver authentication
POST /api/v1/driver/register   - New driver registration
GET  /api/v1/services          - List all services
GET  /api/v1/merchants/nearby  - Location-based search
```

#### Protected Endpoints (Require Bearer Token)
```
GET  /api/v1/driver/profile    - Get driver profile
PUT  /api/v1/driver/profile    - Update profile
GET  /api/v1/driver/orders     - Order history
POST /api/v1/orders            - Place new order
```

#### JWT Configuration (.env)
```
JWT_SECRET_KEY = [secret]
JWT_ALGORITHM = HS256
JWT_EXPIRATION_TIME = 2592000  # 30 days
```

---

## Controllers Reference

| Controller | Purpose |
|------------|---------|
| `Home` | Public pages (landing, about, contact, packages) |
| `Auth` | Login, logout, password reset |
| `Register` | User registration |
| `DriverDashboard` | Driver account management |
| `MerchantDashboard` | Merchant dashboard & orders |
| `MerchantListingsController` | CRUD for listings |
| `MerchantLocations` | Branch/location management |
| `BranchDashboard` | Branch manager interface |
| `Order` | Order placement & management |
| `Payment` | PayFast integration |
| `Subscription` | Subscription management |
| `Admin` | Admin panel functionality |
| `EmailMarketing` | Campaign management |
| `ApiController` | Mobile API endpoints |
| `Routes` | Route planning |
| `Search` | Search functionality |

---

## Payment Integration (PayFast)

### Flow
1. Merchant selects plan → `Onboarding::selectPlan()`
2. Redirect to PayFast → `Payment::process()`
3. PayFast callback → `Payment::notify()` (ITN)
4. Success redirect → `Payment::success()`

### Configuration (.env)
```
PAYFAST_MERCHANT_ID = 10000100      # Sandbox
PAYFAST_MERCHANT_KEY = [key]
PAYFAST_PASSPHRASE = [passphrase]
PAYFAST_SANDBOX = true
```

---

## Email System

### Configuration
- **SMTP Host**: mail.truckersafrica.com
- **Port**: 587
- **Encryption**: TLS

### Email Types
- Order confirmations
- Merchant approval notifications
- Password reset
- Subscription reminders
- Marketing campaigns (batch processed)

### Batch Email Sending
Campaigns are sent in batches of 10 to prevent server timeout:
```
POST /admin/email-marketing/campaigns/send-batch/{id}
```

---

## File Structure

```
app/
├── Config/
│   ├── Routes.php         # All route definitions
│   ├── Filters.php        # Auth filters
│   └── Email.php          # SMTP configuration
├── Controllers/           # 28 controllers
├── Models/               # 46 models
├── Views/
│   ├── admin/            # Admin panel views
│   ├── merchant/         # Merchant dashboard
│   ├── driver/           # Driver dashboard
│   ├── branch/           # Branch manager views
│   ├── front-end/        # Public pages
│   ├── templates/        # Header/footer templates
│   └── emails/           # Email templates
├── Filters/              # Auth middleware
└── Helpers/              # Utility functions

uploads/
├── listings/             # Merchant listing images
├── documents/            # Merchant documents
└── driver-documents/     # Driver documents
```

---

## Commands

```bash
# Development
php spark serve                    # Start dev server
php spark migrate                  # Run migrations
php spark cache:clear             # Clear cache

# Cron Jobs
php spark UpdateExchangeRates     # Update currency rates
php spark SendSubscriptionReminders  # Expiry reminders
```

---

## Environment Variables (.env)

```bash
# App
CI_ENVIRONMENT = development
app.baseURL = 'http://localhost/truckers-africa/'

# Database
database.default.hostname = localhost
database.default.database = app_truckers_africa
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi

# API Keys
GEOAPIFY_API_KEY = [key]
GOOGLE_CLIENT_ID = [id]
GOOGLE_CLIENT_SECRET = [secret]

# JWT
JWT_SECRET_KEY = [key]
JWT_EXPIRATION_TIME = 2592000

# Email
email.fromEmail = admin@truckersafrica.com
email.fromName = Truckers Africa
email.SMTPHost = mail.truckersafrica.com
email.SMTPUser = [user]
email.SMTPPass = [pass]
email.SMTPPort = 587
```

---

## Security Features

- **Password Hashing**: `password_hash()` with `PASSWORD_DEFAULT`
- **CSRF Protection**: Enabled by default, use `<?= csrf_field() ?>`
- **SQL Injection**: Query Builder with parameter binding
- **XSS Protection**: `esc()` helper in views
- **JWT Tokens**: Secure API authentication with expiration

---

## Key URLs

| Route | Description |
|-------|-------------|
| `/` | Landing page |
| `/login` | Driver/Merchant login |
| `/signup` | Registration |
| `/branch/login` | Branch manager login |
| `/admin/login` | Admin login |
| `/contact-us` | Contact page |
| `/packages` | Subscription plans |
| `/merchant/dashboard` | Merchant dashboard |
| `/driver/dashboard` | Driver dashboard |
| `/api/v1/*` | Mobile API |

---

## Subscription Statuses

| Status | Description |
|--------|-------------|
| `trial_pending` | Plan selected, awaiting payment details |
| `trial` | Active trial period |
| `active` | Paid and active |
| `expired` | Subscription ended |
| `cancelled` | Manually cancelled |

---

## Order Statuses

| Status | Description |
|--------|-------------|
| `pending` | New order, awaiting merchant |
| `accepted` | Merchant accepted |
| `completed` | Service delivered |
| `rejected` | Merchant rejected |

---

## Multi-Location System

Orders are grouped by `location_id`, not `merchant_id`:
- Each location has one branch manager
- Branch managers only see their location's orders
- Merchant owners see all locations' orders

---

*Last Updated: December 2025*

