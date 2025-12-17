<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
// $routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.

// --- API Routes for Mobile App (MUST BE FIRST) ---
$routes->group('api/v1', static function ($routes) {
    
    // Test endpoint
    $routes->get('test', 'ApiController::test');
    
    // Public Authentication Endpoints
    $routes->post('driver/login', 'ApiController::driverLogin');
    $routes->post('driver/register', 'ApiController::driverRegister');
    $routes->post('auth/refresh', 'ApiController::refreshToken');
    $routes->get('auth/google', 'ApiController::googleAuth');
    $routes->get('auth/google/callback', 'ApiController::googleAuth');

    // Public Discovery Endpoints
    $routes->get('services/nearby', 'ApiController::getNearbyServices');
    $routes->get('services/search', 'ApiController::searchServices');
    $routes->get('services/categories', 'ApiController::getServiceCategories');
    $routes->get('services/all', 'Services::all');
    $routes->get('services/merchants-with-services', 'Services::merchantsWithServices');
    $routes->get('listings/(:num)', 'ApiController::getListingDetails/$1');
    $routes->get('merchants/(:num)', 'ApiController::getMerchantDetails/$1');

    // Public Currency Endpoints
    $routes->get('currencies', 'ApiController::getSupportedCurrencies');
    $routes->get('currency/exchange-rate', 'ApiController::getExchangeRate');
    $routes->post('currency/convert', 'ApiController::convertCurrency');
    $routes->get('currency/info/(:segment)', 'ApiController::getCurrencyInfo/$1');

    // Protected routes (Bearer JWT) - temporarily disabled JWT filter for routes
    $routes->group('', [], static function ($routes) {
        // Logout (requires valid access token)
        $routes->post('auth/logout', 'ApiController::logout');

        // Driver Profile Endpoints
        $routes->get('driver/profile', 'ApiController::getDriverProfile');
        $routes->post('driver/profile/update', 'ApiController::updateDriverProfile');
        $routes->post('driver/password/update', 'ApiController::updateDriverPassword');

        // Route Planning Endpoints
        $routes->post('routes/create', 'ApiController::createRoute');
        $routes->get('routes', 'ApiController::getRoutes');
        $routes->get('routes/(:num)', 'ApiController::getRouteDetails/$1');
        $routes->delete('routes/(:num)', 'ApiController::deleteRoute/$1');
        $routes->post('routes/(:num)/toggle-saved', 'ApiController::toggleRouteSaved/$1');

        // Order Management Endpoints
        $routes->post('orders/place', 'ApiController::placeOrder');
        $routes->get('orders/my-history', 'ApiController::getOrderHistory');

        // Notification Endpoints
        $routes->get('notifications', 'ApiController::getNotifications');
        $routes->post('notifications/mark-read', 'ApiController::markNotificationRead');

        // Review Endpoints
        $routes->post('merchants/(:num)/review', 'ApiController::submitMerchantReview/$1');

        // Location Tracking
        $routes->post('driver/location', 'ApiController::updateDriverLocation');

        // Currency Management
        $routes->post('driver/currency/update', 'ApiController::updateDriverCurrencyPreference');

        // Dashboard Endpoints
        $routes->get('driver/dashboard/stats', 'ApiController::getDashboardStats');
        $routes->get('driver/recent-routes', 'ApiController::getRecentRoutes');

        // Location History
        $routes->get('driver/location/history', 'ApiController::getLocationHistory');

        // Driving Sessions
        $routes->post('driver/session/start', 'ApiController::startDrivingSession');
        $routes->get('driver/session/current', 'ApiController::getCurrentDrivingSession');
        $routes->post('driver/session/(:num)/status', 'ApiController::updateDrivingSessionStatus/$1');
    });
    
    // Legacy API endpoints (keeping for backward compatibility)
    $routes->post('geocode', 'RoutePlanner::geocode');
    $routes->post('calculate-route', 'RoutePlanner::calculateRoute');
    $routes->get('nearby-merchants', 'RoutePlanner::nearbyMerchants');
    $routes->get('driver/nearby-merchants', 'DriverDashboard::getNearbyMerchantsFeed');
    $routes->get('driver/nearby-listings', 'DriverDashboard::getNearbyListingsFeed');
    $routes->get('driver/location/latest/(:num)', 'DriverDashboard::latestLocation/$1');
});

// --- Public & Authentication Routes ---
$routes->get('/', 'Home::index'); // Displays the main landing page (index.html)
$routes->get('/about-us', 'Home::about');
$routes->get('/pricing', 'Home::pricing');
$routes->get('/packages', 'Home::packages');
$routes->get('/terms', 'Home::terms');
$routes->get('/contact-us', 'Home::contact'); // Contact Us page
$routes->post('/contact-us', 'Home::handleContactForm'); // Contact form submission
$routes->get('/listing/(:num)', 'Home::listingDetail/$1');
$routes->get('/merchant/profile/(:num)', 'Home::merchantProfile/$1');
$routes->post('order/place', 'Order::placeOrder', ['filter' => 'login']); // Handles the submission of a new order

// Merchant order management pages
$routes->get('merchant/orders', 'MerchantDashboard::signup/merchant/create'); // All orders (default)
$routes->get('merchant/orders/(:segment)', 'MerchantDashboard::orders/$1');
$routes->get('merchant/orders/view/(:num)', 'MerchantDashboard::viewOrder/$1');
$routes->get('order/accept/(:num)', 'Order::acceptOrder/$1');
$routes->get('order/reject/(:num)', 'Order::rejectOrder/$1');

// Login / Logout
$routes->get('login', 'Auth::login');

// --- Payment Gateway Routes ---
$routes->get('payment/process/(:num)', 'Payment::process/$1');
$routes->post('payment/process', 'Payment::process'); // For onboarding form POST
$routes->get('payment/success', 'Payment::success');
$routes->get('payment/cancel', 'Payment::cancel');
$routes->post('payment/notify', 'Payment::notify');
$routes->get('payment/test-activate/(:num)', 'Payment::testActivateSubscription/$1'); // TEST ONLY: Activate subscription manually
$routes->get('payment/test-activate', 'Payment::testActivateSubscription'); // TEST ONLY: Activate current merchant's subscription
$routes->get('payment/test-page', function() {
    return view('payment/test_activate');
}); // TEST ONLY: Test activation page
$routes->post('login', 'Auth::login'); // Handles the login form submission
$routes->get('logout', 'Auth::logout'); // Logs the user out

// Password Reset Routes
$routes->get('auth/forgot-password', 'Auth::forgotPassword');
$routes->post('auth/forgot-password', 'Auth::processForgotPassword');
$routes->get('auth/reset-password', 'Auth::resetPassword');
$routes->post('auth/reset-password', 'Auth::processResetPassword');

// Merchant Password Setup Routes (for admin-created merchants)
$routes->get('merchant/setup-password', 'Auth::setupMerchantPassword');
$routes->post('merchant/setup-password/process', 'Auth::processSetupMerchantPassword');

// Google OAuth Routes
$routes->get('auth/google', 'Auth::googleLogin'); // Redirects to Google for authentication
$routes->get('auth/google/callback', 'Auth::googleCallback'); // Handles the response from Google

// Signup Process
$routes->get('signup', 'Auth::chooseUserType'); // Shows the "Driver or Merchant?" page (signup-choose-type.html)

// --- Driver Registration & Profile ---
$routes->get('signup/driver', 'Auth::signupDriverForm'); // Shows the driver registration form (signup-driver.html)
$routes->post('signup/driver/create', 'Auth::createDriver'); // Processes the driver registration form

$routes->get('profile/driver/edit', 'Profile::editDriver'); // Shows the driver profile update form (driver-update-profile.html)
$routes->post('profile/driver/update', 'Profile::updateDriver'); // Processes the driver profile update

// --- Merchant Registration & Profile ---
$routes->get('signup/merchant', 'Auth::signupMerchantForm'); // Shows the merchant registration form (signup-merchant.html)
$routes->post('signup/merchant/create', 'Auth::createMerchant'); // Processes the merchant registration
$routes->get('auth/packages', 'Home::packages'); // Shows package selection page, consolidated to new view
$routes->get('auth/select-plan/(:segment)', 'Auth::selectPlan/$1'); // Processes plan selection

$routes->get('signup/subscription', 'Subscription::showPlans'); // Shows the subscription plan page (signup-subscription.html)
$routes->post('signup/subscription', 'Subscription::startTrial'); // Processes the plan selection

$routes->get('profile/merchant/edit', 'Profile::editMerchant'); // Shows merchant profile update form (merchant-update-profile.html)
$routes->post('profile/merchant/update', 'Profile::updateMerchant'); // Processes merchant profile update
$routes->get('profile/merchant/change-password', 'Profile::changePassword'); // Shows merchant change password form
$routes->post('profile/merchant/update-password', 'Profile::updatePassword'); // Processes merchant password change

$routes->get('approval/pending', 'MerchantDashboard::pending'); // Shows the "Pending Approval" page (merchant-pending-approval.html)

// --- Logged-In Merchant Routes ---


// --- Logged-In Driver Routes ---
$routes->get('dashboard/driver', 'DriverDashboard::index'); // Shows the main driver dashboard (driver-dashboard.html)
// $routes->get('dashboard', 'DriverDashboard::index'); // Commented out - too broad, conflicts with API routing
$routes->get('driver/service/(:num)', 'DriverDashboard::service_view/$1');
$routes->get('driver/merchant/(:num)', 'DriverDashboard::merchant_profile/$1'); // Displays individual service listings
$routes->get('driver/location/(:num)', 'DriverDashboard::location_view/$1'); // Displays individual branch location with listings
$routes->get('driver/services', 'Services::index'); // Shows all services and products page

// Route Planning
$routes->get('driver/routes', 'Routes::index'); // Route planning page
$routes->get('driver/routes/view/(:num)', 'Routes::view/$1'); // View route details page
$routes->post('routes/create', 'Routes::create'); // Create new route
$routes->get('routes/get/(:num)', 'Routes::getRoute/$1'); // Get route details
$routes->post('routes/merchants-along-route', 'Routes::getMerchantsAlongRoute'); // Get merchants along route
$routes->post('routes/toggle-saved/(:num)', 'Routes::toggleSaved/$1'); // Toggle saved status
$routes->delete('routes/delete/(:num)', 'Routes::delete/$1'); // Delete route

// Driver Profile
$routes->get('profile/driver', 'Profile::editDriver'); // Shows driver profile edit form
$routes->post('profile/driver/update', 'Profile::updateDriver'); // Updates driver profile
$routes->get('profile/driver/change-password', 'Profile::changePasswordDriver'); // Shows driver change password form
$routes->post('profile/driver/update-password', 'Profile::updatePasswordDriver'); // Processes driver password change

// Driver Settings
$routes->get('driver/settings/currency', 'DriverDashboard::currencySettings'); // Currency preference settings
$routes->post('driver/settings/update-currency', 'DriverDashboard::updateCurrencyPreference'); // Update currency preference
$routes->get('debug/session', 'DriverDashboard::debugSession'); // Debug session data

// Temporary public currency settings for testing
$routes->get('test-currency-settings', 'DriverDashboard::testCurrencySettings'); // Public test version

// Cron Jobs
$routes->get('cron/update-currency-rates', 'CronController::updateCurrencyRates'); // Update exchange rates
$routes->get('cron/process-expired-subscriptions', 'CronController::processExpiredSubscriptions'); // Process expired subscriptions
$routes->get('cron/test', 'CronController::test'); // Test cron system
$routes->get('cron/currency-status', 'CronController::currencyStatus'); // Check currency system status

// Temporary public test endpoint (remove after testing)
$routes->get('test-currency-update', 'CronController::publicTestUpdate'); // Public test for currency updates

// Search and Filtering
// Public search (homepage) remains at /search using Search::find below
$routes->get('driver/search', 'Search::index'); // Driver search with category filtering

// Order Management
$routes->get('order/checkout', 'Order::checkout'); // Order checkout/completion page
$routes->post('order/complete', 'Order::completeOrder'); // Process order completion
$routes->get('order/receipt/(:num)', 'Order::receipt/$1'); // View single order receipt
$routes->get('order/multi-receipt/(:any)', 'Order::multiReceipt/$1'); // View multi-order receipt
$routes->get('order/my-orders', 'Order::myOrders'); // View driver's order history
$routes->get('order/accept/(:num)', 'Order::acceptOrder/$1'); // Accept order (merchant)
$routes->post('order/reject/(:num)', 'Order::rejectOrder/$1'); // Reject order (merchant)

// Backward/compat or deep link for driver order view links in emails/notifications
$routes->get('driver/orders/view/(:num)', 'Order::receipt/$1');

// Notifications
$routes->get('notifications', 'Notifications::index'); // AJAX endpoint for notifications
$routes->get('notifications/count', 'Notifications::unreadCount'); // AJAX endpoint for unread count
$routes->post('notifications/read/(:num)', 'Notifications::markAsRead/$1'); // Mark notification as read
$routes->post('notifications/read-all', 'Notifications::markAllAsRead'); // Mark all as read
$routes->get('driver/notifications', 'Notifications::driverNotifications'); // Driver notifications page
$routes->get('merchant/notifications', 'Notifications::merchantNotifications'); // Merchant notifications page

// Route Planning (Legacy - using RoutePlanner controller for other features)
// Main driver route planning uses Routes controller (configured above in driver section)

// Route Planning API endpoints
$routes->post('api/geocode', 'RoutePlanner::geocode'); // Geocode address to coordinates
$routes->post('api/calculate-route', 'RoutePlanner::calculateRoute'); // Calculate route with merchants
$routes->get('api/nearby-merchants', 'RoutePlanner::nearbyMerchants'); // Get nearby merchants

// Driver Dashboard API endpoints
$routes->get('api/driver/nearby-merchants', 'DriverDashboard::getNearbyMerchantsFeed'); // Get nearby merchants for feed
$routes->post('api/driver/location', 'DriverDashboard::updateLocation'); // Update driver location
$routes->get('api/driver/nearby-listings', 'DriverDashboard::getNearbyListingsFeed'); // Get nearby listings for feed
// Latest location for a specific driver (for viewer apps to poll)
$routes->get('api/driver/location/latest/(:num)', 'DriverDashboard::latestLocation/$1');

// Driving Sessions
$routes->post('driving/start', 'DriverDashboard::startDrivingSession'); // Starts tracking driving time
$routes->post('driving/pause', 'DriverDashboard::pauseDrivingSession'); // Pauses tracking
$routes->post('driving/stop', 'DriverDashboard::stopDrivingSession'); // Stops tracking

// --- Logged-In Merchant Routes ---
$routes->group('merchant', [], static function ($routes) {
    $routes->get('dashboard', 'MerchantDashboard::index');
    $routes->get('verification', 'MerchantDashboard::verification');
    $routes->post('upload-document', 'MerchantDashboard::uploadDocument');
    $routes->get('orders/(all)', 'MerchantDashboard::orders/all');
    $routes->get('orders/(pending)', 'MerchantDashboard::orders/pending');
    $routes->get('orders/(approved)', 'MerchantDashboard::orders/approved');
    $routes->get('driver/profile/(:num)', 'MerchantDashboard::viewDriverProfile/$1');
    $routes->get('help', 'MerchantDashboard::help');

    // Route planning page
    $routes->get('route-planning', 'RouteController::index');

    // Merchant Locations Management
    $routes->get('locations', 'MerchantLocations::index');
    $routes->get('locations/create', 'MerchantLocations::create');
    $routes->post('locations/store', 'MerchantLocations::store');
    $routes->get('locations/edit/(:num)', 'MerchantLocations::edit/$1');
    $routes->post('locations/update/(:num)', 'MerchantLocations::update/$1');
    $routes->post('locations/set-primary/(:num)', 'MerchantLocations::setPrimary/$1');
    $routes->post('locations/deactivate/(:num)', 'MerchantLocations::deactivate/$1');
    $routes->post('locations/activate/(:num)', 'MerchantLocations::activate/$1');
    $routes->get('locations/delete/(:num)', 'MerchantLocations::delete/$1');
    $routes->get('locations/details/(:num)', 'MerchantLocations::details/$1');
    $routes->get('locations/for-listing', 'MerchantLocations::getForListing');

    // Merchant Service Listings (CRUD)
    $routes->get('listings', 'MerchantListingsController::index', ['as' => 'merchant_listings']);
    $routes->get('listings/view/(:num)', 'MerchantListingsController::view/$1', ['as' => 'view_merchant_listing']);
    $routes->get('listings/new', 'MerchantListingsController::new', ['as' => 'new_merchant_listing']);
    $routes->post('listings/create', 'MerchantListingsController::create', ['as' => 'create_merchant_listing']);
    $routes->get('listings/edit/(:num)', 'MerchantListingsController::edit/$1', ['as' => 'edit_merchant_listing']);
    $routes->post('listings/update/(:num)', 'MerchantListingsController::update/$1', ['as' => 'update_merchant_listing']);
    $routes->post('listings/delete-gallery-image/(:num)', 'MerchantListingsController::deleteGalleryImage/$1', ['as' => 'delete_gallery_image']);
    $routes->get('listings/delete/(:num)', 'MerchantListingsController::delete/$1', ['as' => 'delete_merchant_listing']);

    // Listing Requests (from branches)
    $routes->get('listing-requests', 'MerchantListingRequests::index');
    $routes->get('listing-requests/view/(:num)', 'MerchantListingRequests::view/$1');
    $routes->post('listing-requests/approve/(:num)', 'MerchantListingRequests::approve/$1');
    $routes->post('listing-requests/reject/(:num)', 'MerchantListingRequests::reject/$1');
    $routes->post('listing-requests/convert/(:num)', 'MerchantListingRequests::convert/$1');

    // This route now points to the new listings page, repurposing the sidebar link.
    $routes->get('services', 'MerchantListingsController::index', ['filter' => 'merchantauth']);
});

// --- Universal Search & Discovery Routes ---
$routes->get('search', 'Search::find'); // Public search: /search?q=tyre+repair
$routes->get('merchants/view/(:num)', 'Search::viewMerchant/$1'); // Displays a single public merchant profile
$routes->post('driver/login', 'ApiController::driverLogin');
$routes->post('api/driver/login', 'ApiController::driverLogin');


// --- Admin Panel Routes ---
$routes->group('admin', static function ($routes) {
    // Admin Login & Dashboard
    $routes->get('login', 'Admin::login');
    $routes->post('login', 'Admin::login');
    $routes->get('logout', 'Admin::logout');
    $routes->get('dashboard', 'Admin::dashboard', ['filter' => 'adminauth']);
    $routes->get('register', 'Admin::register');
    $routes->post('register', 'Admin::register');

    // Admin Password Reset
    $routes->get('forgot-password', 'Admin::forgotPassword');
    $routes->post('forgot-password', 'Admin::processForgotPassword');
    $routes->get('reset-password', 'Admin::resetPassword');
    $routes->post('reset-password', 'Admin::processResetPassword');

    // User Management
    $routes->get('drivers/all', 'Admin::allDrivers', ['filter' => 'adminauth']);
    $routes->get('drivers/add', 'Admin::addDriver', ['filter' => 'adminauth']);
    $routes->post('drivers/add', 'Admin::addDriver', ['filter' => 'adminauth']);
    $routes->get('drivers/edit/(:num)', 'Admin::editDriver/$1', ['filter' => 'adminauth']);
    $routes->post('drivers/edit/(:num)', 'Admin::editDriver/$1', ['filter' => 'adminauth']);
    $routes->get('drivers/delete/(:num)', 'Admin::deleteDriver/$1', ['filter' => 'adminauth']);
    $routes->get('merchants/all', 'Admin::allMerchants', ['filter' => 'adminauth']);
    $routes->get('merchants/pending', 'Admin::pendingMerchants', ['filter' => 'adminauth']);
    $routes->get('merchants/view/(:num)', 'Admin::viewMerchant/$1', ['filter' => 'adminauth']);
    $routes->get('merchants/add', 'Admin::addMerchant', ['filter' => 'adminauth']);
    $routes->post('merchants/add', 'Admin::addMerchant', ['filter' => 'adminauth']);
    $routes->get('merchants/edit/(:num)', 'Admin::editMerchant/$1', ['filter' => 'adminauth']);
    $routes->post('merchants/edit/(:num)', 'Admin::editMerchant/$1', ['filter' => 'adminauth']);
    $routes->get('merchants/delete/(:num)', 'Admin::deleteMerchant/$1', ['filter' => 'adminauth']);
    $routes->get('merchants/disable/(:num)', 'Admin::disableMerchant/$1', ['filter' => 'adminauth']);
    $routes->get('merchants/suspend/(:num)', 'Admin::suspendMerchant/$1', ['filter' => 'adminauth']);
    $routes->get('merchants/reject/(:num)', 'Admin::rejectMerchant/$1', ['filter' => 'adminauth']);
    $routes->get('merchants/approve/(:num)', 'Admin::approveMerchant/$1', ['filter' => 'adminauth']);
    $routes->get('merchants/verify/(:num)', 'Admin::verifyMerchant/$1', ['filter' => 'adminauth']);
    $routes->post('documents/approve/(:num)', 'Admin::approveDocument/$1', ['filter' => 'adminauth']);
    $routes->post('documents/reject/(:num)', 'Admin::rejectDocument/$1', ['filter' => 'adminauth']);

    // Listing Management
    $routes->get('listings/pending', 'Admin::pendingListings', ['filter' => 'adminauth']);
    $routes->get('listings/approve/(:num)', 'Admin::approveListing/$1', ['filter' => 'adminauth']);
    $routes->get('listings/approved', 'Admin::approvedListings', ['filter' => 'adminauth']);
    $routes->get('listings/all', 'Admin::allListings', ['filter' => 'adminauth']);
    $routes->get('listings/view/(:num)', 'Admin::viewListing/$1', ['filter' => 'adminauth']);
    $routes->get('listings/reject/(:num)', 'Admin::rejectListing/$1', ['filter' => 'adminauth']); 
     $routes->get('listings/relist/(:num)', 'Admin::relistListing/$1', ['filter' => 'adminauth']);

    // Service & Category Management
    $routes->get('services/all', 'Admin::allServices', ['filter' => 'adminauth']);
    $routes->get('services/add', 'Admin::addService', ['filter' => 'adminauth']);
    $routes->post('services/create', 'Admin::createService', ['filter' => 'adminauth']);
    $routes->get('services/categories', 'Admin::serviceCategories', ['filter' => 'adminauth']);
    $routes->get('services/category/add', 'Admin::addCategory', ['filter' => 'adminauth']);
    $routes->post('services/category/create', 'Admin::createCategory', ['filter' => 'adminauth']);

    // Subscription & Plan Management
    $routes->get('subscriptions', 'Admin::subscriptions', ['filter' => 'adminauth']);

    // Plan Management
    $routes->get('plans', 'Admin::plans', ['filter' => 'adminauth']);
    $routes->get('plans/create', 'Admin::createPlan', ['filter' => 'adminauth']);
    $routes->post('plans/store', 'Admin::storePlan', ['filter' => 'adminauth']);
    $routes->get('plans/edit/(:num)', 'Admin::editPlan/$1', ['filter' => 'adminauth']);
    $routes->post('plans/update/(:num)', 'Admin::updatePlan/$1', ['filter' => 'adminauth']);
    $routes->get('plans/delete/(:num)', 'Admin::deletePlan/$1', ['filter' => 'adminauth']);

    // Feature Management
    $routes->get('features', 'Admin::features', ['filter' => 'adminauth']);
    $routes->get('features/create', 'Admin::createFeature', ['filter' => 'adminauth']);
    $routes->post('features/store', 'Admin::storeFeature', ['filter' => 'adminauth']);
    $routes->get('features/edit/(:num)', 'Admin::editFeature/$1', ['filter' => 'adminauth']);
    $routes->post('features/update/(:num)', 'Admin::updateFeature/$1', ['filter' => 'adminauth']);
    $routes->get('features/delete/(:num)', 'Admin::deleteFeature/$1', ['filter' => 'adminauth']);

    // Plan-Feature Association
    $routes->get('plans/manage/(:num)', 'Admin::managePlanFeatures/$1', ['filter' => 'adminauth']);
    $routes->post('plans/manage/(:num)', 'Admin::updatePlanFeatures/$1', ['filter' => 'adminauth']);
    $routes->post('plans/update-feature-order/(:num)', 'Admin::updateFeatureOrder/$1', ['filter' => 'adminauth']);

    // Settings
    $routes->get('settings', 'Admin::settings');
    $routes->get('route-planning', 'RouteController::index', ['filter' => 'adminauth']);
    
    // Debug route (temporary)
    $routes->get('debug-session', 'Admin::debugSession');

    // Email Marketing
    $routes->get('email-marketing', 'Admin::emailMarketing', ['filter' => 'adminauth']);
    $routes->get('email-marketing/create', 'Admin::createEmailCampaign', ['filter' => 'adminauth']);
    $routes->post('email-marketing/store', 'Admin::storeEmailCampaign', ['filter' => 'adminauth']);
    $routes->get('email-marketing/send/(:num)', 'Admin::sendEmailCampaign/$1', ['filter' => 'adminauth']);

});
$routes->post('merchants/review/(:num)', 'Review::create/$1'); // Submits a review for a merchant

// --- Admin Routes ---
$routes->get('admin/login', 'Admin::login');
$routes->post('admin/login', 'Admin::login');
$routes->get('admin/register', 'Admin::register');
$routes->post('admin/register', 'Admin::register');

$routes->get('admin/dashboard', 'Admin::dashboard', ['filter' => 'adminAuth']);
$routes->get('admin/merchants/pending', 'Admin::pendingMerchants', ['filter' => 'adminAuth']);
    $routes->get('admin/merchants/all', 'Admin::allMerchants', ['filter' => 'adminAuth']);
    $routes->get('admin/merchants/view/(:num)', 'Admin::viewMerchant/$1', ['filter' => 'adminAuth']);
    $routes->get('admin/merchants/add', 'Admin::addMerchant', ['filter' => 'adminAuth']);
    $routes->post('admin/merchants/add', 'Admin::addMerchant', ['filter' => 'adminAuth']);
    $routes->get('admin/merchants/edit/(:num)', 'Admin::editMerchant/$1', ['filter' => 'adminAuth']);
    $routes->post('admin/merchants/edit/(:num)', 'Admin::editMerchant/$1', ['filter' => 'adminAuth']);
    $routes->get('admin/merchants/delete/(:num)', 'Admin::deleteMerchant/$1', ['filter' => 'adminAuth']);
    $routes->get('admin/merchants/disable/(:num)', 'Admin::disableMerchant/$1', ['filter' => 'adminAuth']);
    $routes->get('admin/merchants/suspend/(:num)', 'Admin::suspendMerchant/$1', ['filter' => 'adminAuth']);
    $routes->get('admin/merchants/reject/(:num)', 'Admin::rejectMerchant/$1', ['filter' => 'adminAuth']);
    $routes->get('admin/merchants/approve/(:num)', 'Admin::approveMerchant/$1', ['filter' => 'adminAuth']);
    $routes->get('admin/merchants/verify/(:num)', 'Admin::verifyMerchant/$1', ['filter' => 'adminAuth']);
    $routes->get('admin/merchants/analytics/(:num)', 'Admin::merchantAnalytics/$1', ['filter' => 'adminAuth']);
    $routes->get('admin/merchants/reset-password/(:num)', 'Admin::resetMerchantPassword/$1', ['filter' => 'adminAuth']);
    $routes->get('admin/merchants/extend-trial/(:num)', 'Admin::extendMerchantTrial/$1', ['filter' => 'adminAuth']);
    $routes->post('admin/documents/approve/(:num)', 'Admin::approveDocument/$1', ['filter' => 'adminAuth']);
    $routes->post('admin/documents/reject/(:num)', 'Admin::rejectDocument/$1', ['filter' => 'adminAuth']);
    $routes->get('admin/drivers/all', 'Admin::allDrivers', ['filter' => 'adminAuth']);
    $routes->get('admin/drivers/add', 'Admin::addDriver', ['filter' => 'adminAuth']);
    $routes->post('admin/drivers/add', 'Admin::addDriver', ['filter' => 'adminAuth']);
    $routes->get('admin/drivers/edit/(:num)', 'Admin::editDriver/$1', ['filter' => 'adminAuth']);
    $routes->post('admin/drivers/edit/(:num)', 'Admin::editDriver/$1', ['filter' => 'adminAuth']);
    $routes->get('admin/drivers/delete/(:num)', 'Admin::deleteDriver/$1', ['filter' => 'adminAuth']);
    $routes->get('admin/subscriptions', 'Admin::subscriptions', ['filter' => 'adminAuth']);
    $routes->get('admin/subscriptions/manage/(:num)', 'Admin::manageSubscription/$1', ['filter' => 'adminAuth']);
    $routes->get('admin/settings', 'Admin::settings', ['filter' => 'adminAuth']);
    $routes->get('admin/services/all', 'Admin::allServices', ['filter' => 'adminAuth']);
    $routes->get('admin/services/add', 'Admin::addService', ['filter' => 'adminAuth']);
    $routes->post('admin/services/create', 'Admin::createService', ['filter' => 'adminAuth']);
    $routes->get('admin/services/categories', 'Admin::serviceCategories', ['filter' => 'adminAuth']);
    $routes->get('admin/services/categories/add', 'Admin::addCategory', ['filter' => 'adminAuth']);
    $routes->post('admin/services/categories/create', 'Admin::createCategory', ['filter' => 'adminAuth']);
    $routes->get('admin/services/categories/edit/(:num)', 'Admin::editCategory/$1', ['filter' => 'adminAuth']);
    $routes->post('admin/services/categories/update/(:num)', 'Admin::updateCategory/$1', ['filter' => 'adminAuth']);
    $routes->get('admin/services/categories/delete/(:num)', 'Admin::deleteCategory/$1', ['filter' => 'adminAuth']);

// Email Marketing routes
$routes->get('admin/email-marketing/leads', 'EmailMarketing::leads', ['filter' => 'adminAuth']);
$routes->get('admin/email-marketing/leads/add', 'EmailMarketing::addLead', ['filter' => 'adminAuth']);
$routes->post('admin/email-marketing/leads/store', 'EmailMarketing::storeLead', ['filter' => 'adminAuth']);
$routes->get('admin/email-marketing/leads/edit/(:num)', 'EmailMarketing::editLead/$1', ['filter' => 'adminAuth']);
$routes->post('admin/email-marketing/leads/update/(:num)', 'EmailMarketing::updateLead/$1', ['filter' => 'adminAuth']);
$routes->get('admin/email-marketing/leads/view/(:num)', 'EmailMarketing::viewLead/$1', ['filter' => 'adminAuth']);
$routes->get('admin/email-marketing/leads/delete/(:num)', 'EmailMarketing::deleteLead/$1', ['filter' => 'adminAuth']);
$routes->get('admin/email-marketing/campaigns', 'EmailMarketing::campaigns', ['filter' => 'adminAuth']);
$routes->get('admin/email-marketing/campaigns/sent', 'EmailMarketing::sentCampaigns', ['filter' => 'adminAuth']);
$routes->get('admin/email-marketing/campaigns/create', 'EmailMarketing::createCampaign', ['filter' => 'adminAuth']);
$routes->post('admin/email-marketing/campaigns/store', 'EmailMarketing::storeCampaign', ['filter' => 'adminAuth']);
$routes->get('admin/email-marketing/campaigns/edit/(:num)', 'EmailMarketing::editCampaign/$1', ['filter' => 'adminAuth']);
$routes->post('admin/email-marketing/campaigns/update/(:num)', 'EmailMarketing::updateCampaign/$1', ['filter' => 'adminAuth']);
$routes->get('admin/email-marketing/campaigns/view/(:num)', 'EmailMarketing::viewCampaign/$1', ['filter' => 'adminAuth']);
$routes->get('admin/email-marketing/campaigns/delete/(:num)', 'EmailMarketing::deleteCampaign/$1', ['filter' => 'adminAuth']);
$routes->get('admin/email-marketing/campaigns/send/(:num)', 'EmailMarketing::sendCampaign/$1', ['filter' => 'adminAuth']);
$routes->post('admin/email-marketing/campaigns/send-batch/(:num)', 'EmailMarketing::sendBatch/$1', ['filter' => 'adminAuth']);

$routes->get('admin/logout', 'Admin::logout');

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
// Merchant onboarding routes (for admin-created merchants)
$routes->group('merchant/onboarding', function($routes) {
    $routes->get('/', 'Onboarding::index'); // Step 1: Profile completion
    $routes->post('update-profile', 'Onboarding::updateProfile'); // Process profile update
    $routes->get('plans', 'Onboarding::plans'); // Step 2: Plan selection
    $routes->post('select-plan', 'Onboarding::selectPlan'); // Process plan selection
    $routes->get('payment', 'Onboarding::payment'); // Step 3: Payment (if no trial)
    $routes->get('complete', 'Onboarding::complete'); // Complete onboarding
});

// Subscription routes for merchants
$routes->group('merchant/subscription', function($routes) {
    $routes->get('/', 'Subscription::index');
    $routes->get('plans', 'Subscription::showPlans');
    $routes->post('start-trial', 'Subscription::startTrial'); // Start free trial
    $routes->post('change-plan-preview', 'Subscription::changePlanPreview'); // Show prorata breakdown
    $routes->post('change-plan', 'Subscription::changePlan'); // Process plan change
    $routes->get('prorata-payment/(:num)/(:num)', 'Subscription::prorataPayment/$1/$2'); // Prorata payment page
    $routes->get('prorata-success', 'Subscription::prorataSuccess'); // Prorata payment success
    $routes->get('select-branches', 'Subscription::selectBranches'); // Branch activation selection
    $routes->post('activate-branches', 'Subscription::activateBranches'); // Process branch activation
    $routes->get('test-renewal/(:num)', 'Subscription::testRenewal/$1'); // TEST ONLY: Simulate renewal
    $routes->get('test-renewal', 'Subscription::testRenewal'); // TEST ONLY: Simulate renewal (auto-detect subscription)
    $routes->get('test-expiry', 'Subscription::testExpiry'); // TEST ONLY: Simulate subscription expiry
    $routes->get('renew/(:num)', 'Subscription::renew/$1'); // Renewal payment page
    $routes->get('renewal-success', 'Subscription::renewalSuccess'); // Renewal payment success
    $routes->get('payment-method', 'Subscription::updatePaymentMethod');
    $routes->get('update-payment-method', 'Subscription::savePaymentMethod');
    $routes->get('payment-update-success', 'Subscription::paymentUpdateSuccess');
    $routes->get('payment-update-cancel', 'Subscription::paymentUpdateCancel');
    $routes->get('transaction-history', 'Subscription::transactionHistory');
    $routes->post('cancel', 'Subscription::cancel');
    $routes->post('process-payment', 'Subscription::processPayment'); // Process payment for new subscriptions
});

// Branch User Routes
$routes->group('branch', function($routes) {
    // Authentication
    $routes->get('login', 'BranchAuth::login');
    $routes->post('login', 'BranchAuth::processLogin');
    $routes->get('logout', 'BranchAuth::logout');
    $routes->get('forgot-password', 'BranchAuth::forgotPassword');
    $routes->post('forgot-password', 'BranchAuth::processForgotPassword');
    $routes->get('reset-password/(:any)', 'BranchAuth::resetPassword/$1');
    $routes->post('reset-password', 'BranchAuth::processResetPassword');

    // Password Setup (for newly created branch users)
    $routes->get('setup-password', 'BranchAuth::setupPassword');
    $routes->post('setup-password/process', 'BranchAuth::processSetupPassword');

    // Dashboard (requires authentication)
    $routes->get('dashboard', 'BranchDashboard::index');
    $routes->get('/', 'BranchDashboard::index');

    // Orders
    $routes->get('orders', 'BranchDashboard::orders');
    $routes->get('orders/view/(:num)', 'BranchDashboard::viewOrder/$1');
    $routes->post('orders/update-status/(:num)', 'BranchDashboard::updateOrderStatus/$1');
    $routes->get('orders/accept/(:num)', 'BranchDashboard::acceptOrder/$1');
    $routes->post('orders/reject/(:num)', 'BranchDashboard::rejectOrder/$1');

    // Profile Management
    $routes->get('profile', 'BranchDashboard::profile');
    $routes->post('profile/update', 'BranchDashboard::updateProfile');
    $routes->post('profile/change-password', 'BranchDashboard::changePassword');

    // Listing Requests
    $routes->get('listing-requests', 'BranchDashboard::listingRequests');
    $routes->get('listing-requests/new', 'BranchDashboard::newListingRequest');
    $routes->post('listing-requests/submit', 'BranchDashboard::submitListingRequest');
    $routes->get('listing-requests/view/(:num)', 'BranchDashboard::viewListingRequest/$1');
});


if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}