<?php

namespace App\Controllers;

use App\Models\AdminModel;
use App\Models\PlanModel;
use App\Models\FeatureModel;
use App\Models\PlanFeatureModel;
use App\Models\EmailCampaignModel;
use App\Models\MerchantModel;
use App\Models\TruckDriverModel;
use App\Models\UserLoginModel;
use App\Models\PasswordResetTokenModel;
use App\Models\ServiceCategoryModel;
use App\Services\NotificationService;

/**
 * @property \CodeIgniter\HTTP\IncomingRequest $request
 */
class Admin extends BaseController
{
    /**
     * Displays the admin login page or processes the login attempt.
     */
    public function login()
    {
        if ($this->request->getMethod() === 'post') {
            $rules = [
                'email'    => 'required|valid_email',
                'password' => 'required',
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $email = $this->request->getVar('email');
            $password = $this->request->getVar('password');

            $model = new AdminModel();
            $admin = $model->verifyPassword($email, $password);

            if ($admin) {
                session()->set([
                    'admin_id'   => $admin['id'],
                    'admin_name' => $admin['name'],
                    'isAdminLoggedIn' => true,
                ]);
                return redirect()->to('admin/dashboard');
            }

            return redirect()->back()->withInput()->with('error', 'Invalid credentials.');
        }

        return view('admin/login'); // We will create this view next
    }

    /**
     * Show admin forgot password form
     */
    public function forgotPassword()
    {
        return view('admin/forgot_password');
    }

    /**
     * Process admin forgot password request
     */
    public function processForgotPassword()
    {
        if (!$this->request->is('post')) {
            return redirect()->to('admin/forgot-password');
        }

        $rules = [
            'email' => 'required|valid_email'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email = $this->request->getPost('email');

        // Check if email exists in admins table
        $adminModel = new AdminModel();
        $admin = $adminModel->where('email', $email)->first();

        if (!$admin) {
            // Don't reveal if email exists or not for security
            return redirect()->back()->with('message', 'If your email is registered, you will receive a password reset link shortly.');
        }

        // Create password reset token
        $tokenModel = new PasswordResetTokenModel();
        $token = $tokenModel->createToken($email, 'admin');

        if ($token) {
            // Send password reset email
            try {
                $this->sendAdminPasswordResetEmail($email, $token, $admin);

                return redirect()->back()->with('message', 'If your email is registered, you will receive a password reset link shortly.');
            } catch (\Exception $e) {
                log_message('error', 'Failed to send admin password reset email: ' . $e->getMessage());
                return redirect()->back()->with('error', 'Failed to send password reset email. Please try again.');
            }
        }

        return redirect()->back()->with('error', 'Failed to process password reset request. Please try again.');
    }

    /**
     * Show admin reset password form
     */
    public function resetPassword()
    {
        $token = $this->request->getGet('token');
        $email = $this->request->getGet('email');

        if (!$token || !$email) {
            return redirect()->to('admin/login')->with('error', 'Invalid password reset link.');
        }

        // Validate token
        $tokenModel = new PasswordResetTokenModel();
        $tokenRecord = $tokenModel->validateToken($token, $email, 'admin');

        if (!$tokenRecord) {
            return redirect()->to('admin/login')->with('error', 'Invalid or expired password reset link.');
        }

        return view('admin/reset_password', [
            'token' => $token,
            'email' => $email
        ]);
    }

    /**
     * Process admin password reset
     */
    public function processResetPassword()
    {
        if (!$this->request->is('post')) {
            return redirect()->to('admin/login');
        }

        $rules = [
            'token' => 'required',
            'email' => 'required|valid_email',
            'password' => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $token = $this->request->getPost('token');
        $email = $this->request->getPost('email');
        $newPassword = $this->request->getPost('password');

        // Validate token
        $tokenModel = new PasswordResetTokenModel();
        $tokenRecord = $tokenModel->validateToken($token, $email, 'admin');

        if (!$tokenRecord) {
            return redirect()->to('admin/login')->with('error', 'Invalid or expired password reset link.');
        }

        // Update password in admins table
        $adminModel = new AdminModel();
        $admin = $adminModel->where('email', $email)->first();

        if (!$admin) {
            return redirect()->to('admin/login')->with('error', 'Admin not found.');
        }

        // Update password
        $success = $adminModel->update($admin['id'], [
            'password' => $newPassword // AdminModel should handle hashing
        ]);

        if ($success) {
            // Mark token as used
            $tokenModel->markTokenAsUsed($tokenRecord['id']);

            return redirect()->to('admin/login')->with('message', 'Password reset successfully. You can now log in with your new password.');
        }

        return redirect()->back()->with('error', 'Failed to reset password. Please try again.');
    }

    /**
     * Send admin password reset email
     */
    private function sendAdminPasswordResetEmail(string $email, string $token, array $admin)
    {
        $resetUrl = site_url("admin/reset-password?token={$token}&email=" . urlencode($email));

        $emailService = \Config\Services::email();
        $subject = 'Admin Password Reset Request - Truckers Africa';

        $message = view('emails/admin_password_reset', [
            'admin' => $admin,
            'reset_url' => $resetUrl,
            'token_expires' => '1 hour'
        ]);

        $emailService->setTo($email);
        $emailService->setSubject($subject);
        $emailService->setMessage($message);

        return $emailService->send();
    }

    /**
     * Generate a random password
     */
    private function generateRandomPassword(int $length = 12): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';
        $charactersLength = strlen($characters);

        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[rand(0, $charactersLength - 1)];
        }

        return $password;
    }

    /**
     * Send welcome email to admin-created driver
     */
    private function sendDriverWelcomeEmail(array $driver, string $password)
    {
        $emailService = \Config\Services::email();
        $subject = 'Welcome to Truckers Africa - Your Account Details';

        $message = view('emails/admin_driver_welcome', [
            'driver' => $driver,
            'password' => $password,
            'login_url' => site_url('login')
        ]);

        $emailService->setTo($driver['email']);
        $emailService->setSubject($subject);
        $emailService->setMessage($message);

        return $emailService->send();
    }

    /**
     * Send welcome email to admin-created merchant (deprecated - use sendMerchantAccountSetupEmail instead)
     */
    private function sendMerchantWelcomeEmail(array $merchant, string $password)
    {
        $emailService = \Config\Services::email();
        $subject = 'Welcome to Truckers Africa - Your Merchant Account Details';

        $message = view('emails/admin_merchant_welcome', [
            'merchant' => $merchant,
            'password' => $password,
            'login_url' => site_url('login')
        ]);

        $emailService->setTo($merchant['email']);
        $emailService->setSubject($subject);
        $emailService->setMessage($message);

        return $emailService->send();
    }

    /**
     * Send account setup email to admin-created merchant with password setup link
     */
    private function sendMerchantAccountSetupEmail(array $merchant, string $passwordToken): bool
    {
        $emailService = \Config\Services::email();
        $subject = 'Complete Your Truckers Africa Merchant Account Setup';

        $setupUrl = site_url("merchant/setup-password?token={$passwordToken}&email=" . urlencode($merchant['email']));

        $message = view('emails/merchant_account_setup', [
            'merchant' => $merchant,
            'setup_url' => $setupUrl,
            'token_expires' => '48 hours'
        ]);

        $emailService->setTo($merchant['email']);
        $emailService->setSubject($subject);
        $emailService->setMessage($message);

        return $emailService->send();
    }

    /**
     * Debug admin session - temporary method to check admin login status
     */
    public function debugSession()
    {
        $sessionData = [
            'isAdminLoggedIn' => session()->get('isAdminLoggedIn'),
            'admin_id' => session()->get('admin_id'),
            'admin_name' => session()->get('admin_name'),
            'all_session_data' => session()->get()
        ];
        
        echo "<h2>Admin Session Debug</h2>";
        echo "<pre>" . print_r($sessionData, true) . "</pre>";
        
        if (!session()->get('isAdminLoggedIn')) {
            echo "<p style='color: red;'><strong>Issue Found:</strong> You are not logged in as admin.</p>";
            echo "<p><a href='" . site_url('admin/login') . "'>Click here to login as admin</a></p>";
        } else {
            echo "<p style='color: green;'><strong>Admin session is active.</strong></p>";
            echo "<p><a href='" . site_url('admin/drivers/add') . "'>Try accessing Add Driver page</a></p>";
        }
        
        exit;
    }

    /**
     * Admin dashboard, accessible only after login.
     */
    public function addService()
    {
        $categoryModel = new \App\Models\ServiceCategoryModel();
        $data['categories'] = $categoryModel->getAllCategories();

        return view('admin/services/add_service', $data);
    }

    public function createService()
    {
        $rules = [
            'name' => 'required|is_unique[services.name]',
            'category_id' => 'required|is_not_unique[service_categories.id]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $serviceModel = new \App\Models\ServiceModel();
        $serviceModel->save([
            'name' => $this->request->getPost('name'),
            'category_id' => $this->request->getPost('category_id')
        ]);

        return redirect()->to('admin/services/all')->with('success', 'Service added successfully.');
    }

    public function allServices()
    {
        $serviceModel = new \App\Models\ServiceModel();

        // Get search parameters
        $search = $this->request->getGet('search') ?? '';
        $category = $this->request->getGet('category') ?? '';
        $page = $this->request->getGet('page') ?? 1;
        $perPage = 10; // Number of services per page

        // Build query with search and filters
        $builder = $serviceModel->select('services.*, service_categories.name as category_name')
                               ->join('service_categories', 'service_categories.id = services.category_id', 'left');

        // Apply search filter
        if (!empty($search)) {
            $builder->groupStart()
                   ->like('services.name', $search)
                   ->orLike('service_categories.name', $search)
                   ->groupEnd();
        }

        // Apply category filter
        if (!empty($category)) {
            $builder->where('services.category_id', $category);
        }

        // Get total count for pagination
        $totalServices = $builder->countAllResults(false);

        // Get paginated results
        $services = $builder->orderBy('services.name', 'ASC')
                           ->paginate($perPage, 'default', $page);

        // Get all categories for filter dropdown
        $categoryModel = new \App\Models\ServiceCategoryModel();
        $categories = $categoryModel->orderBy('name', 'ASC')->findAll();

        // Calculate pagination info
        $totalPages = ceil($totalServices / $perPage);
        $currentPage = (int) $page;

        $data = [
            'services' => $services,
            'categories' => $categories,
            'search' => $search,
            'selectedCategory' => $category,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'totalServices' => $totalServices,
            'perPage' => $perPage,
            'pager' => $serviceModel->pager
        ];

        return view('admin/services/all', $data);
    }

    public function addCategory()
    {
        return view('admin/services/add_category');
    }

    public function createCategory()
    {
        $rules = [
            'name' => 'required|is_unique[service_categories.name]',
            'description' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $categoryModel = new \App\Models\ServiceCategoryModel();
        $categoryModel->save([
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description')
        ]);

        return redirect()->to('admin/services/categories')->with('success', 'Category added successfully.');
    }

    public function serviceCategories()
    {
        $categoryModel = new \App\Models\ServiceCategoryModel();
        $data['categories'] = $categoryModel->getAllCategories();

        return view('admin/services/categories', $data);
    }

    public function editCategory($id)
    {
        $categoryModel = new \App\Models\ServiceCategoryModel();
        $category = $categoryModel->find($id);

        if (!$category) {
            return redirect()->to('admin/services/categories')->with('error', 'Category not found.');
        }

        $data['category'] = $category;
        return view('admin/services/edit_category', $data);
    }

    public function updateCategory($id)
    {
        $categoryModel = new \App\Models\ServiceCategoryModel();
        $category = $categoryModel->find($id);

        if (!$category) {
            return redirect()->to('admin/services/categories')->with('error', 'Category not found.');
        }

        $rules = [
            'name' => "required|is_unique[service_categories.name,id,{$id}]",
            'description' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $categoryModel->update($id, [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description')
        ]);

        return redirect()->to('admin/services/categories')->with('success', 'Category updated successfully.');
    }

    public function deleteCategory($id)
    {
        $categoryModel = new \App\Models\ServiceCategoryModel();
        $category = $categoryModel->find($id);

        if (!$category) {
            return redirect()->to('admin/services/categories')->with('error', 'Category not found.');
        }

        // Check if category is in use
        $db = \Config\Database::connect();
        $builder = $db->table('services');
        $servicesCount = $builder->where('category_id', $id)->countAllResults();

        if ($servicesCount > 0) {
            return redirect()->to('admin/services/categories')->with('error', 'Cannot delete category. It is being used by ' . $servicesCount . ' service(s).');
        }

        $categoryModel->delete($id);
        return redirect()->to('admin/services/categories')->with('success', 'Category deleted successfully.');
    }

    public function subscriptions()
    {
        $subscriptionModel = new \App\Models\SubscriptionModel();
        
        // Get search and filter parameters
        $search = $this->request->getGet('search') ?? '';
        $status = $this->request->getGet('status') ?? '';
        $page = $this->request->getGet('page') ?? 1;
        $perPage = 10; // Number of subscriptions per page

        // Build query with search and filters
        $builder = $subscriptionModel->select('subscriptions.*, merchants.business_name, plans.name as plan_name')
                                     ->join('merchants', 'merchants.id = subscriptions.merchant_id', 'left')
                                     ->join('plans', 'plans.id = subscriptions.plan_id', 'left');

        // Apply search filter
        if (!empty($search)) {
            $builder->groupStart()
                   ->like('merchants.business_name', $search)
                   ->orLike('plans.name', $search)
                   ->orLike('subscriptions.status', $search)
                   ->groupEnd();
        }

        // Apply status filter
        if (!empty($status)) {
            $builder->where('subscriptions.status', $status);
        }

        // Get total count for pagination
        $totalSubscriptions = $builder->countAllResults(false);

        // Get paginated results
        $subscriptions = $builder->orderBy('subscriptions.created_at', 'DESC')
                                ->paginate($perPage, 'default', $page);

        // Calculate pagination info
        $totalPages = ceil($totalSubscriptions / $perPage);
        $currentPage = (int) $page;

        $data = [
            'subscriptions' => $subscriptions,
            'search' => $search,
            'selectedStatus' => $status,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'totalSubscriptions' => $totalSubscriptions,
            'perPage' => $perPage,
            'pager' => $subscriptionModel->pager
        ];

        return view('admin/subscriptions/index', $data);
    }

    public function manageSubscription($subscriptionId)
    {
        $subscriptionModel = new \App\Models\SubscriptionModel();
        $merchantModel = new MerchantModel();
        $planModel = new PlanModel();
        $currencyModel = new \App\Models\CurrencyModel();

        // Get subscription details with merchant and plan information
        $subscription = $subscriptionModel
            ->select('subscriptions.*,
                     merchants.business_name,
                     merchants.email as merchant_email,
                     merchants.business_contact_number,
                     merchants.owner_name,
                     plans.name as plan_name,
                     plans.price as plan_price,
                     plans.billing_interval')
            ->join('merchants', 'merchants.id = subscriptions.merchant_id', 'left')
            ->join('plans', 'plans.id = subscriptions.plan_id', 'left')
            ->where('subscriptions.id', $subscriptionId)
            ->first();

        if (!$subscription) {
            session()->setFlashdata('error', 'Subscription not found.');
            return redirect()->to('admin/subscriptions');
        }

        // Get all available plans for potential plan changes
        $availablePlans = $planModel->findAll();

        // Get USD currency symbol (subscription plans are priced in USD)
        $planCurrency = $currencyModel->where('currency_code', 'USD')->first();
        $planCurrencySymbol = $planCurrency['currency_symbol'] ?? '$';

        $data = [
            'subscription' => $subscription,
            'available_plans' => $availablePlans,
            'planCurrencySymbol' => $planCurrencySymbol
        ];

        return view('admin/subscriptions/manage', $data);
    }

    public function settings()
    {
        // In the future, you would load existing settings from the database here.
        return view('admin/settings');
    }

    public function allDrivers()
    {
        $driverModel = new \App\Models\TruckDriverModel();

        // Get search parameters
        $search = $this->request->getGet('search') ?? '';
        $status = $this->request->getGet('status') ?? '';
        $vehicleType = $this->request->getGet('vehicle_type') ?? '';
        $page = $this->request->getGet('page') ?? 1;
        $perPage = 15; // Number of drivers per page

        // Build query with search and filters
        $builder = $driverModel->select('truck_drivers.*,
                                        CASE
                                            WHEN last_location_update > DATE_SUB(NOW(), INTERVAL 30 DAY) THEN "Active"
                                            WHEN last_location_update > DATE_SUB(NOW(), INTERVAL 90 DAY) THEN "Inactive"
                                            ELSE "Dormant"
                                        END as status');

        // Apply search filter
        if (!empty($search)) {
            $builder->groupStart()
                   ->like('name', $search)
                   ->orLike('surname', $search)
                   ->orLike('email', $search)
                   ->orLike('contact_number', $search)
                   ->orLike('license_number', $search)
                   ->orLike('vehicle_registration', $search)
                   ->groupEnd();
        }

        // Apply vehicle type filter
        if (!empty($vehicleType)) {
            $builder->where('vehicle_type', $vehicleType);
        }

        // Get total count for pagination
        $totalDrivers = $builder->countAllResults(false);

        // Get paginated results
        $drivers = $builder->orderBy('created_at', 'DESC')
                          ->paginate($perPage, 'default', $page);

        // Get unique vehicle types for filter dropdown
        $vehicleTypes = $driverModel->select('vehicle_type')
                                  ->distinct()
                                  ->where('vehicle_type IS NOT NULL')
                                  ->where('vehicle_type !=', '')
                                  ->orderBy('vehicle_type', 'ASC')
                                  ->findAll();

        // Calculate pagination info
        $totalPages = ceil($totalDrivers / $perPage);
        $currentPage = (int) $page;

        $data = [
            'drivers' => $drivers,
            'vehicleTypes' => $vehicleTypes,
            'search' => $search,
            'selectedStatus' => $status,
            'selectedVehicleType' => $vehicleType,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'totalDrivers' => $totalDrivers,
            'perPage' => $perPage,
            'pager' => $driverModel->pager
        ];

        return view('admin/drivers/all', $data);
    }

    public function addDriver()
    {
        log_message('info', 'addDriver method called with method: ' . $this->request->getMethod());
        echo "DEBUG: addDriver method reached with method: " . $this->request->getMethod() . "<br>";
        
        if ($this->request->getMethod() === 'POST') {
            log_message('info', 'addDriver POST method called');
            
            // Log the posted data for debugging
            $postedData = $this->request->getPost();
            log_message('info', 'Posted data: ' . json_encode($postedData));
            
            // Add validation rules - make email validation more flexible
            $rules = [
                'name' => 'required|min_length[2]|max_length[255]',
                'surname' => 'permit_empty|max_length[255]',
                'email' => 'required|valid_email|max_length[255]',
                'contact_number' => 'required|max_length[50]',
                'whatsapp_number' => 'permit_empty|max_length[50]',
                'country_of_residence' => 'permit_empty|max_length[100]',
                'vehicle_type' => 'permit_empty|max_length[100]',
                'vehicle_registration' => 'permit_empty|max_length[50]',
                'license_number' => 'permit_empty|max_length[100]',
                'preferred_search_radius_km' => 'permit_empty|integer|greater_than[0]'
            ];

            
            // Check for duplicate email manually since is_unique might be causing issues
            $driverModel = new \App\Models\TruckDriverModel();
            $userLoginModel = new UserLoginModel();
            
            $email = $this->request->getPost('email');
            $existingDriver = $driverModel->where('email', $email)->first();
            $existingLogin = $userLoginModel->where('email', $email)->first();
            
            if ($existingDriver || $existingLogin) {
                log_message('error', 'Email already exists: ' . $email);
                return redirect()->back()->withInput()->with('errors', ['email' => 'Email address is already in use.']);
            }

            // Generate a random password or use provided one
            $providedPassword = $this->request->getPost('password');
            $generatedPassword = !empty($providedPassword) ? $providedPassword : $this->generateRandomPassword();

            $driverData = [
                'name' => $this->request->getPost('name'),
                'surname' => $this->request->getPost('surname'),
                'email' => $this->request->getPost('email'),
                'contact_number' => $this->request->getPost('contact_number'),
                'whatsapp_number' => $this->request->getPost('whatsapp_number'),
                'country_of_residence' => $this->request->getPost('country_of_residence'),
                'vehicle_type' => $this->request->getPost('vehicle_type'),
                'vehicle_registration' => $this->request->getPost('vehicle_registration'),
                'license_number' => $this->request->getPost('license_number'),
                'preferred_search_radius_km' => $this->request->getPost('preferred_search_radius_km') ?: 50
            ];
print_r($driverData);
            // Start database transaction
            $db = \Config\Database::connect();
            $db->transStart();

            try {
                log_message('info', 'Attempting to insert driver data: ' . json_encode($driverData));
                
                // Insert driver
                $driverId = $driverModel->insert($driverData);
                if (!$driverId) {
                    $dbError = $driverModel->errors();
                    log_message('error', 'Driver model insert failed. Errors: ' . json_encode($dbError));
                    throw new \Exception('Failed to create driver record: ' . json_encode($dbError));
                }
                
                log_message('info', 'Driver inserted successfully with ID: ' . $driverId);

                // Create user login entry
                $userLoginData = [
                    'user_id' => $driverId,
                    'user_type' => 'driver',
                    'email' => $this->request->getPost('email'),
                    'password_hash' => password_hash($generatedPassword, PASSWORD_DEFAULT),
                    'is_active' => 1,
                    'login_attempts' => 0
                ];
                
                log_message('info', 'Attempting to insert user login data: ' . json_encode(array_merge($userLoginData, ['password_hash' => '[HIDDEN]'])));

                $userLoginId = $userLoginModel->insert($userLoginData);
                if (!$userLoginId) {
                    $loginError = $userLoginModel->errors();
                    log_message('error', 'User login model insert failed. Errors: ' . json_encode($loginError));
                    throw new \Exception('Failed to create user login record: ' . json_encode($loginError));
                }
                
                log_message('info', 'User login inserted successfully with ID: ' . $userLoginId);

                $db->transComplete();

                if ($db->transStatus() === false) {
                    $dbErrors = $db->error();
                    log_message('error', 'Database transaction failed: ' . json_encode($dbErrors));
                    throw new \Exception('Transaction failed: ' . json_encode($dbErrors));
                }

                // Get the created driver data
                $newDriver = $driverModel->find($driverId);

                // Send welcome email with credentials
                try {
                    $this->sendDriverWelcomeEmail($newDriver, $generatedPassword);
                } catch (\Exception $e) {
                    log_message('error', 'Failed to send welcome email to driver: ' . $e->getMessage());
                    // Don't fail the creation if email fails
                }

                log_message('info', 'Driver created successfully with ID: ' . $driverId);
                session()->setFlashdata('success', 'Driver added successfully. Welcome email sent with login credentials.');
                return redirect()->to('admin/drivers/all');

            } catch (\Exception $e) {
                $db->transRollback();
                log_message('error', 'Failed to create driver: ' . $e->getMessage());
                session()->setFlashdata('error', 'Failed to add driver: ' . $e->getMessage());
                return redirect()->back()->withInput();
            }
        }

        return view('admin/drivers/add', [
            'geoapify_api_key' => getenv('GEOAPIFY_API_KEY')
        ]);
    }

    public function editDriver($id)
    {
        $driverModel = new \App\Models\TruckDriverModel();
        $driver = $driverModel->find($id);

        if (!$driver) {
            session()->setFlashdata('error', 'Driver not found.');
            return redirect()->to('admin/drivers/all');
        }

        if ($this->request->getMethod() === 'POST') {
            $data = [
                'name' => $this->request->getPost('name'),
                'surname' => $this->request->getPost('surname'),
                'email' => $this->request->getPost('email'),
                'contact_number' => $this->request->getPost('contact_number'),
                'whatsapp_number' => $this->request->getPost('whatsapp_number'),
                'country_of_residence' => $this->request->getPost('country_of_residence'),
                'vehicle_type' => $this->request->getPost('vehicle_type'),
                'vehicle_registration' => $this->request->getPost('vehicle_registration'),
                'license_number' => $this->request->getPost('license_number'),
                'preferred_search_radius_km' => $this->request->getPost('preferred_search_radius_km') ?: 50
            ];

            // Only update password if provided
            $password = $this->request->getPost('password');
            if (!empty($password)) {
                $data['password'] = $password; // Will be hashed by the model
            }

            if ($driverModel->update($id, $data)) {
                session()->setFlashdata('success', 'Driver updated successfully.');
                return redirect()->to('admin/drivers/all');
            } else {
                session()->setFlashdata('error', 'Failed to update driver.');
            }
        }

        $data = [
            'driver' => $driver
        ];

        return view('admin/drivers/edit', $data);
    }

    public function deleteDriver($id)
    {
        $driverModel = new \App\Models\TruckDriverModel();
        $driver = $driverModel->find($id);

        if (!$driver) {
            session()->setFlashdata('error', 'Driver not found.');
            return redirect()->to('admin/drivers/all');
        }

        if ($driverModel->delete($id)) {
            session()->setFlashdata('success', 'Driver deleted successfully.');
        } else {
            session()->setFlashdata('error', 'Failed to delete driver.');
        }

        return redirect()->to('admin/drivers/all');
    }

    public function allMerchants()
    {
        $merchantModel = new \App\Models\MerchantModel();
        $subscriptionModel = new \App\Models\SubscriptionModel();

        // Get search parameters
        $search = $this->request->getGet('search') ?? '';
        $status = $this->request->getGet('status') ?? '';
        $page = $this->request->getGet('page') ?? 1;
        $perPage = 20;

        // Build query with search and filters
        $builder = $merchantModel->select('merchants.*,
                                          subscriptions.status as subscription_status,
                                          plans.name as plan_name,
                                          plans.price as plan_price')
                                ->join('subscriptions', 'subscriptions.merchant_id = merchants.id', 'left')
                                ->join('plans', 'plans.id = subscriptions.plan_id', 'left');

        // Apply search filter
        if (!empty($search)) {
            $builder->groupStart()
                   ->like('merchants.business_name', $search)
                   ->orLike('merchants.owner_name', $search)
                   ->orLike('merchants.email', $search)
                   ->orLike('merchants.business_contact_number', $search)
                   ->orLike('merchants.physical_address', $search)
                   ->groupEnd();
        }

        // Apply status filter
        if (!empty($status)) {
            $builder->where('merchants.verification_status', $status);
        }

        // Get total count for pagination
        $totalMerchants = $builder->countAllResults(false);

        // Get paginated results
        $merchants = $builder->orderBy('merchants.created_at', 'DESC')
                            ->paginate($perPage, 'default', $page);

        // Get merchant services for each merchant
        $merchantServiceModel = new \App\Models\MerchantServiceModel();
        foreach ($merchants as &$merchant) {
            $merchant['services'] = $merchantServiceModel->select('services.name')
                                                         ->join('services', 'services.id = merchant_services.service_id')
                                                         ->where('merchant_services.merchant_id', $merchant['id'])
                                                         ->findAll();
        }

        // Calculate pagination info
        $totalPages = ceil($totalMerchants / $perPage);
        $currentPage = (int) $page;

        $data = [
            'merchants' => $merchants,
            'search' => $search,
            'selectedStatus' => $status,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'totalMerchants' => $totalMerchants,
            'perPage' => $perPage
        ];

        return view('admin/merchants/all', $data);
    }

    public function pendingMerchants()
    {
        $merchantModel = new \App\Models\MerchantModel();
        $merchantServiceModel = new \App\Models\MerchantServiceModel();
        $currencyModel = new \App\Models\CurrencyModel();

        // Get search parameters
        $search = $this->request->getGet('search') ?? '';
        $page = $this->request->getGet('page') ?? 1;
        $perPage = 15;

        // Build query for pending merchants with subscription and plan info
        $builder = $merchantModel->select('merchants.*, plans.name as plan_name, plans.price as plan_price')
                                ->join('subscriptions', 'subscriptions.merchant_id = merchants.id', 'left')
                                ->join('plans', 'plans.id = subscriptions.plan_id', 'left')
                                ->where('merchants.verification_status', 'pending');

        // Apply search filter
        if (!empty($search)) {
            $builder->groupStart()
                   ->like('merchants.business_name', $search)
                   ->orLike('merchants.owner_name', $search)
                   ->orLike('merchants.email', $search)
                   ->orLike('merchants.business_contact_number', $search)
                   ->groupEnd();
        }

        // Get total count for pagination
        $totalMerchants = $builder->countAllResults(false);

        // Get paginated results
        $merchants = $builder->orderBy('merchants.created_at', 'DESC')

                            ->paginate($perPage, 'default', $page);

        // Get merchant services for each merchant
        foreach ($merchants as &$merchant) {
            $merchant['services'] = $merchantServiceModel->select('services.name')
                                                         ->join('services', 'services.id = merchant_services.service_id')
                                                         ->where('merchant_services.merchant_id', $merchant['id'])
                                                         ->findAll();
        }

        // Calculate pagination info
        $totalPages = ceil($totalMerchants / $perPage);
        $currentPage = (int) $page;

        // Get USD currency symbol (subscription plans are priced in USD)
        $planCurrency = $currencyModel->where('currency_code', 'USD')->first();
        $planCurrencySymbol = $planCurrency['currency_symbol'] ?? '$';

        $data = [
            'merchants' => $merchants,
            'search' => $search,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'totalMerchants' => $totalMerchants,
            'perPage' => $perPage,
            'planCurrencySymbol' => $planCurrencySymbol
        ];

        return view('admin/merchants/pending', $data);
    }

    public function viewMerchant($merchantId)
    {
        $merchantModel = new \App\Models\MerchantModel();
        $subscriptionModel = new \App\Models\SubscriptionModel();
        $merchantServiceModel = new \App\Models\MerchantServiceModel();
        $documentModel = new \App\Models\MerchantDocumentModel();
        $verificationModel = new \App\Models\VerificationRequirementModel();
        $currencyModel = new \App\Models\CurrencyModel();

        // Get comprehensive merchant information
        $merchant = $merchantModel->select('merchants.*,
                                          subscriptions.id as subscription_id,
                                          subscriptions.status as subscription_status,
                                          subscriptions.trial_ends_at,
                                          subscriptions.created_at as subscription_date,
                                          plans.name as plan_name,
                                          plans.price as plan_price,
                                          plans.trial_days')
                                  ->join('subscriptions', 'subscriptions.merchant_id = merchants.id', 'left')
                                  ->join('plans', 'plans.id = subscriptions.plan_id', 'left')
                                  ->where('merchants.id', $merchantId)
                                  ->first();

        // Get USD currency symbol (subscription plans are priced in USD)
        $planCurrency = $currencyModel->where('currency_code', 'USD')->first();
        $planCurrencySymbol = $planCurrency['currency_symbol'] ?? '$';

        if (!$merchant) {
            return redirect()->to('admin/merchants/all')->with('error', 'Merchant not found.');
        }

        // Get merchant services
        $merchant['services'] = $merchantServiceModel->select('services.name, services.id, service_categories.name as category_name')
                                                     ->join('services', 'services.id = merchant_services.service_id')
                                                     ->join('service_categories', 'service_categories.id = services.category_id', 'left')
                                                     ->where('merchant_services.merchant_id', $merchantId)
                                                     ->findAll();

        // Get subscription history
        $subscriptionHistory = $subscriptionModel->select('subscriptions.*, plans.name as plan_name, plans.price')
                                                 ->join('plans', 'plans.id = subscriptions.plan_id', 'left')
                                                 ->where('merchant_id', $merchantId)
                                                 ->orderBy('created_at', 'DESC')
                                                 ->findAll();

        // Get verification documents and progress
        $businessType = $merchant['business_type'] ?? 'individual';
        $verificationProgress = $documentModel->getVerificationProgress($merchantId, $businessType);
        $requiredDocuments = $verificationModel->getRequiredDocuments($businessType);
        $uploadedDocuments = $documentModel->getDocumentsByMerchant($merchantId);

        // Get merchant statistics (if available)
        // You can add more statistics here based on your database structure
        $stats = [
            'total_services' => count($merchant['services']),
            'account_age_days' => floor((time() - strtotime($merchant['created_at'])) / (24 * 60 * 60)),
            'last_login' => $merchant['last_login'] ?? null,
            'profile_completion' => $this->calculateProfileCompletion($merchant),
            'verification_completion' => $verificationProgress['completion_percentage'],
            'documents_approved' => $verificationProgress['approved'],
            'documents_pending' => $verificationProgress['pending']
        ];

        $data = [
            'merchant' => $merchant,
            'subscriptionHistory' => $subscriptionHistory,
            'stats' => $stats,
            'verificationProgress' => $verificationProgress,
            'requiredDocuments' => $requiredDocuments,
            'uploadedDocuments' => $uploadedDocuments,
            'businessType' => $businessType,
            'planCurrencySymbol' => $planCurrencySymbol
        ];

        return view('admin/merchants/view', $data);
    }

    private function calculateProfileCompletion($merchant)
    {
        $fields = [
            'business_name', 'owner_name', 'email', 'business_contact_number',
            'physical_address', 'main_service', 'business_description', 'profile_description'
        ];

        $completed = 0;
        foreach ($fields as $field) {
            if (!empty($merchant[$field])) {
                $completed++;
            }
        }

        return round(($completed / count($fields)) * 100);
    }

    public function verifyMerchant($merchantId)
    {
        $merchantModel = new \App\Models\MerchantModel();
        $documentModel = new \App\Models\MerchantDocumentModel();

        $merchant = $merchantModel->find($merchantId);
        if (!$merchant) {
            return redirect()->to('admin/merchants/all')->with('error', 'Merchant not found.');
        }

        // Check if all required documents are approved
        $businessType = $merchant['business_type'] ?? 'individual';
        $verificationProgress = $documentModel->getVerificationProgress($merchantId, $businessType);

        if ($verificationProgress['approved'] < $verificationProgress['total_required']) {
            return redirect()->to("admin/merchants/view/{$merchantId}")
                           ->with('error', 'Cannot verify merchant. Not all required documents are approved.');
        }

        // Update merchant verification status
        $adminId = session()->get('admin_id') ?? 1; // Get current admin ID
        $merchantModel->updateVerificationStatus($merchantId, 'verified', $adminId);

        return redirect()->to("admin/merchants/view/{$merchantId}")
                       ->with('success', 'Merchant has been successfully verified!');
    }

    public function approveDocument($documentId)
    {
        $documentModel = new \App\Models\MerchantDocumentModel();

        $document = $documentModel->find($documentId);
        if (!$document) {
            return redirect()->back()->with('error', 'Document not found.');
        }

        $adminId = session()->get('admin_id') ?? 1; // Get current admin ID
        $documentModel->updateVerificationStatus($documentId, 'approved', $adminId);

        // Send approval email to merchant
        try {
            $notifier = new NotificationService();
            $adminName = session()->get('admin_name');
            $notifier->notifyMerchantDocumentApproved((int) $documentId, $adminName ?: null);
        } catch (\Throwable $t) {
            log_message('error', 'Failed to send document approval notification: ' . $t->getMessage());
        }

        return redirect()->back()->with('success', 'Document approved successfully! Merchant has been notified.');
    }

    public function rejectDocument($documentId)
    {
        $documentModel = new \App\Models\MerchantDocumentModel();

        $document = $documentModel->find($documentId);
        if (!$document) {
            return redirect()->back()->with('error', 'Document not found.');
        }

        $rejectionReason = $this->request->getPost('rejection_reason') ?? 'Document does not meet requirements';
        $adminId = session()->get('admin_id') ?? 1; // Get current admin ID

        $documentModel->updateVerificationStatus($documentId, 'rejected', $adminId, $rejectionReason);

        // Send rejection email to merchant
        try {
            $notifier = new NotificationService();
            $adminName = session()->get('admin_name');
            $notifier->notifyMerchantDocumentRejected((int) $documentId, $rejectionReason, $adminName ?: null);
        } catch (\Throwable $t) {
            log_message('error', 'Failed to send document rejection notification: ' . $t->getMessage());
        }

        return redirect()->back()->with('success', 'Document rejected. Merchant has been notified.');
    }

    /**
     * Admin verification dashboard - shows all merchants pending verification
     */
    public function verificationDashboard()
    {
        $merchantModel = new \App\Models\MerchantModel();
        $documentModel = new \App\Models\MerchantDocumentModel();

        // Get merchants with submitted documents
        $pendingMerchants = $merchantModel->select('merchants.*,
                                                  COUNT(md.id) as total_documents,
                                                  SUM(CASE WHEN md.is_verified = "approved" THEN 1 ELSE 0 END) as approved_documents,
                                                  SUM(CASE WHEN md.is_verified = "pending" THEN 1 ELSE 0 END) as pending_documents,
                                                  SUM(CASE WHEN md.is_verified = "rejected" THEN 1 ELSE 0 END) as rejected_documents')
                                          ->join('merchant_documents md', 'md.merchant_id = merchants.id', 'left')
                                          ->where('merchants.verification_submitted_at IS NOT NULL')
                                          ->where('merchants.is_verified', 'not_verified')
                                          ->groupBy('merchants.id')
                                          ->orderBy('merchants.verification_submitted_at', 'ASC')
                                          ->findAll();

        // Get verification statistics
        $stats = [
            'total_pending' => count($pendingMerchants),
            'ready_for_verification' => 0,
            'awaiting_documents' => 0,
            'documents_pending_review' => 0
        ];

        foreach ($pendingMerchants as &$merchant) {
            $businessType = $merchant['business_type'] ?? 'individual';
            $verificationProgress = $documentModel->getVerificationProgress($merchant['id'], $businessType);
            $merchant['verification_progress'] = $verificationProgress;

            if ($verificationProgress['approval_percentage'] === 100) {
                $stats['ready_for_verification']++;
            } elseif ($verificationProgress['uploaded'] === 0) {
                $stats['awaiting_documents']++;
            } else {
                $stats['documents_pending_review']++;
            }
        }

        $data = [
            'pendingMerchants' => $pendingMerchants,
            'stats' => $stats,
            'page_title' => 'Merchant Verification Dashboard'
        ];

        return view('admin/verification/dashboard', $data);
    }

    public function approveMerchant($id)
    {
        $merchantModel = new \App\Models\MerchantModel();
        $updateData = [
            'verification_status' => 'approved',
            'status' => 'approved',
            'is_visible' => 1
        ];

        if ($merchantModel->update($id, $updateData)) {
            // Send approval email/notification to merchant
            try {
                $notifier = new NotificationService();
                $adminName = session()->get('admin_name');
                $notifier->notifyMerchantApproved((int) $id, $adminName ?: null);

                // Send approval email
                $emailService = new \App\Helpers\EmailService();
                $merchant = $merchantModel->find($id);

                if ($merchant && isset($merchant['email'])) {
                    $merchantData = [
                        'email' => $merchant['email'],
                        'business_name' => $merchant['business_name'],
                        'contact_person' => $merchant['owner_name'] ?? 'Merchant'
                    ];
                    $emailService->sendMerchantApprovalNotification($merchantData, 'approved');
                }

            } catch (\Throwable $t) {
                log_message('error', 'Failed to send merchant approval notification: ' . $t->getMessage());
            }
            session()->setFlashdata('success', 'Merchant approved successfully.');
        } else {
            session()->setFlashdata('error', 'Failed to approve merchant.');
        }

        return redirect()->to('admin/merchants/pending');
    }

    public function addMerchant()
    {
        log_message('info', 'addMerchant() called - Method: ' . $this->request->getMethod());

        if ($this->request->getMethod() === 'post') {
            log_message('info', 'Add Merchant POST received');
            log_message('debug', 'POST data: ' . json_encode($this->request->getPost()));

            // Add validation rules
            $rules = [
                'owner_name' => 'required|min_length[2]|max_length[255]',
                'email' => 'required|valid_email|max_length[255]|is_unique[merchants.email]|is_unique[user_logins.email]',
                'business_name' => 'required|min_length[2]|max_length[255]',
                'business_contact_number' => 'required|max_length[50]',
                'business_whatsapp_number' => 'permit_empty|max_length[50]',
                'physical_address' => 'required|max_length[500]',
                'main_service' => 'permit_empty|max_length[255]',
                'business_description' => 'permit_empty|max_length[1000]',
                'profile_description' => 'permit_empty|max_length[1000]',
                'latitude' => 'permit_empty|decimal',
                'longitude' => 'permit_empty|decimal',
                'address_selected' => 'permit_empty|in_list[1]'
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            // Additional validation: ensure address was selected from dropdown if address is provided
            if (!empty($this->request->getPost('physical_address'))) {
                if (empty($this->request->getPost('address_selected')) || $this->request->getPost('address_selected') !== '1') {
                    return redirect()->back()->withInput()->with('error', 'Please select an address from the dropdown suggestions.');
                }
            }

            $merchantModel = new \App\Models\MerchantModel();
            $userLoginModel = new UserLoginModel();
            $subscriptionModel = new \App\Models\SubscriptionModel();

            // Generate password setup token instead of password
            $passwordToken = bin2hex(random_bytes(32));
            $tokenExpires = date('Y-m-d H:i:s', strtotime('+48 hours'));

            $merchantData = [
                'owner_name' => $this->request->getPost('owner_name'),
                'email' => $this->request->getPost('email'),
                'business_name' => $this->request->getPost('business_name'),
                'business_contact_number' => $this->request->getPost('business_contact_number'),
                'business_whatsapp_number' => $this->request->getPost('business_whatsapp_number'),
                'physical_address' => $this->request->getPost('physical_address'),
                'latitude' => $this->request->getPost('latitude'),
                'longitude' => $this->request->getPost('longitude'),
                'main_service' => $this->request->getPost('main_service'),
                'business_description' => $this->request->getPost('business_description'),
                'profile_description' => $this->request->getPost('profile_description'),
                'verification_status' => $this->request->getPost('verification_status') ?: 'approved',
                'status' => $this->request->getPost('verification_status') ?: 'approved',
                'is_visible' => $this->request->getPost('is_visible') ? 1 : 0,
                'password_reset_token' => $passwordToken,
                'password_reset_expires' => $tokenExpires,
                'onboarding_completed' => 0 // Admin-created merchants need to complete onboarding
            ];

            // Start database transaction
            $db = \Config\Database::connect();
            $db->transStart();

            try {
                log_message('info', 'Attempting to create merchant: ' . $this->request->getPost('email'));

                // Insert merchant
                $merchantId = $merchantModel->insert($merchantData);
                if (!$merchantId) {
                    $errors = $merchantModel->errors();
                    log_message('error', 'Merchant model insert failed. Errors: ' . json_encode($errors));
                    throw new \Exception('Failed to create merchant record: ' . json_encode($errors));
                }

                log_message('info', 'Merchant inserted successfully with ID: ' . $merchantId);

                // Create user login entry with temporary password (will be set by merchant)
                $tempPassword = bin2hex(random_bytes(16)); // Temporary, merchant will set their own
                $userLoginData = [
                    'user_id' => $merchantId,
                    'user_type' => 'merchant',
                    'email' => $this->request->getPost('email'),
                    'password_hash' => password_hash($tempPassword, PASSWORD_DEFAULT),
                    'is_active' => 1,
                    'login_attempts' => 0
                ];

                $userLoginId = $userLoginModel->insert($userLoginData);
                if (!$userLoginId) {
                    $errors = $userLoginModel->errors();
                    log_message('error', 'User login model insert failed. Errors: ' . json_encode($errors));
                    throw new \Exception('Failed to create user login record: ' . json_encode($errors));
                }

                log_message('info', 'User login inserted successfully with ID: ' . $userLoginId);

                // Create a default trial subscription for the new merchant
                // Get the default plan (you can adjust this to get a specific plan)
                $planModel = new \App\Models\PlanModel();
                $defaultPlan = $planModel->where('has_trial', 1)->first();

                if ($defaultPlan) {
                    $subscriptionData = [
                        'merchant_id' => $merchantId,
                        'plan_id' => $defaultPlan['id'],
                        'status' => 'trial',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    $subscriptionId = $subscriptionModel->insert($subscriptionData);
                    if ($subscriptionId) {
                        log_message('info', 'Trial subscription created for merchant ID: ' . $merchantId);
                    } else {
                        log_message('warning', 'Failed to create trial subscription for merchant ID: ' . $merchantId);
                    }
                } else {
                    log_message('warning', 'No default trial plan found. Merchant created without subscription.');
                }

                // Auto-create primary location if physical address is provided
                if (!empty($this->request->getPost('physical_address'))) {
                    try {
                        $locationModel = new \App\Models\MerchantLocationModel();

                        $locationData = [
                            'merchant_id' => $merchantId,
                            'location_name' => $this->request->getPost('business_name') . ' - Main Branch',
                            'physical_address' => $this->request->getPost('physical_address'),
                            'contact_number' => $this->request->getPost('business_contact_number'),
                            'whatsapp_number' => $this->request->getPost('business_whatsapp_number'),
                            'email' => $this->request->getPost('email'),
                            'latitude' => $this->request->getPost('latitude'),
                            'longitude' => $this->request->getPost('longitude'),
                            'operating_hours' => null,
                            'is_primary' => 1,
                            'is_active' => 1
                        ];

                        $locationId = $locationModel->insert($locationData);

                        if ($locationId) {
                            log_message('info', 'Primary location created automatically for merchant ID: ' . $merchantId);
                        } else {
                            log_message('warning', 'Failed to auto-create primary location for merchant ID: ' . $merchantId);
                        }
                    } catch (\Exception $e) {
                        log_message('error', 'Exception while auto-creating primary location: ' . $e->getMessage());
                        // Don't fail the merchant creation if location creation fails
                    }
                }

                $db->transComplete();

                if ($db->transStatus() === false) {
                    $dbErrors = $db->error();
                    log_message('error', 'Database transaction failed: ' . json_encode($dbErrors));
                    throw new \Exception('Transaction failed: ' . json_encode($dbErrors));
                }

                // Get the created merchant data
                $newMerchant = $merchantModel->find($merchantId);

                if (!$newMerchant) {
                    log_message('error', 'Could not retrieve newly created merchant with ID: ' . $merchantId);
                    throw new \Exception('Merchant created but could not be retrieved');
                }

                // Send account setup email with password setup link
                $emailSent = false;
                try {
                    $emailSent = $this->sendMerchantAccountSetupEmail($newMerchant, $passwordToken);
                    if ($emailSent) {
                        log_message('info', 'Account setup email sent successfully to: ' . $newMerchant['email']);
                    } else {
                        log_message('warning', 'Account setup email failed to send to: ' . $newMerchant['email']);
                    }
                } catch (\Exception $e) {
                    log_message('error', 'Exception while sending account setup email to merchant: ' . $e->getMessage());
                    // Don't fail the creation if email fails
                }

                log_message('info', 'Merchant created successfully with ID: ' . $merchantId);

                $successMessage = 'Merchant added successfully.';
                if ($emailSent) {
                    $successMessage .= ' Account setup email sent with password setup link.';
                } else {
                    $successMessage .= ' However, the setup email could not be sent. Please resend the setup link manually.';
                }

                session()->setFlashdata('success', $successMessage);
                return redirect()->to('admin/merchants/all');

            } catch (\Exception $e) {
                $db->transRollback();
                log_message('error', 'Failed to create merchant: ' . $e->getMessage());
                session()->setFlashdata('error', 'Failed to add merchant: ' . $e->getMessage());
                return redirect()->back()->withInput();
            }
        }

        $serviceCategoryModel = new ServiceCategoryModel();

        return view('admin/merchants/add', [
            'geoapify_api_key' => getenv('GEOAPIFY_API_KEY'),
            'service_categories' => $serviceCategoryModel->getAllCategories()
        ]);
    }

    public function editMerchant($id)
    {
        $merchantModel = new \App\Models\MerchantModel();
        $merchant = $merchantModel->find($id);

        if (!$merchant) {
            session()->setFlashdata('error', 'Merchant not found.');
            return redirect()->to('admin/merchants/all');
        }

        if ($this->request->getMethod() === 'POST') {
            $data = [
                'owner_name' => $this->request->getPost('owner_name'),
                'email' => $this->request->getPost('email'),
                'business_name' => $this->request->getPost('business_name'),
                'business_contact_number' => $this->request->getPost('business_contact_number'),
                'business_whatsapp_number' => $this->request->getPost('business_whatsapp_number'),
                'physical_address' => $this->request->getPost('physical_address'),
                'main_service' => $this->request->getPost('main_service'),
                'business_description' => $this->request->getPost('business_description'),
                'profile_description' => $this->request->getPost('profile_description'),
                'verification_status' => $this->request->getPost('verification_status'),
                'status' => $this->request->getPost('verification_status'),
                'is_visible' => $this->request->getPost('is_visible') ? 1 : 0
            ];

            if ($merchantModel->update($id, $data)) {
                session()->setFlashdata('success', 'Merchant updated successfully.');
                return redirect()->to('admin/merchants/all');
            } else {
                session()->setFlashdata('error', 'Failed to update merchant.');
            }
        }

        $data = [
            'merchant' => $merchant
        ];

        return view('admin/merchants/edit', $data);
    }

    public function deleteMerchant($id)
    {
        $merchantModel = new \App\Models\MerchantModel();
        $listingModel = new \App\Models\MerchantListingModel();
        $listingImageModel = new \App\Models\MerchantListingImageModel();
        $documentModel = new \App\Models\MerchantDocumentModel();
        $userLoginModel = new UserLoginModel();

        $merchant = $merchantModel->find($id);

        if (!$merchant) {
            session()->setFlashdata('error', 'Merchant not found.');
            return redirect()->to('admin/merchants/all');
        }

        // Start database transaction
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // 1. Delete all merchant listings and their images
            $listings = $listingModel->where('merchant_id', $id)->findAll();
            foreach ($listings as $listing) {
                // Delete listing gallery images
                $galleryImages = $listingImageModel->findByListingId($listing['id']);
                if ($galleryImages) {
                    foreach ($galleryImages as $image) {
                        // Delete physical image file
                        if (!empty($image['image_path']) && file_exists(FCPATH . $image['image_path'])) {
                            @unlink(FCPATH . $image['image_path']);
                        }
                    }
                    $listingImageModel->where('listing_id', $listing['id'])->delete();
                }

                // Delete listing main image
                if (!empty($listing['main_image_path']) && file_exists(FCPATH . $listing['main_image_path'])) {
                    @unlink(FCPATH . $listing['main_image_path']);
                }

                // Delete listing categories
                $db->table('merchant_listing_categories')->where('listing_id', $listing['id'])->delete();

                // Delete listing services
                $db->table('merchant_listing_services')->where('listing_id', $listing['id'])->delete();
            }

            // Delete all listings for this merchant
            $listingModel->where('merchant_id', $id)->delete();

            // 2. Delete merchant documents
            $documents = $documentModel->getDocumentsByMerchant($id);
            foreach ($documents as $document) {
                // Delete physical document file
                if (!empty($document['file_path']) && file_exists(FCPATH . $document['file_path'])) {
                    @unlink(FCPATH . $document['file_path']);
                }
            }
            $documentModel->where('merchant_id', $id)->delete();

            // 3. Delete merchant services
            $db->table('merchant_services')->where('merchant_id', $id)->delete();

            // 4. Delete subscriptions
            $db->table('subscriptions')->where('merchant_id', $id)->delete();

            // 5. Delete user login record
            $userLoginModel->where('user_id', $id)->where('user_type', 'merchant')->delete();

            // 6. Delete merchant profile images
            if (!empty($merchant['business_image_url']) && file_exists(FCPATH . $merchant['business_image_url'])) {
                @unlink(FCPATH . $merchant['business_image_url']);
            }
            if (!empty($merchant['profile_image_url']) && file_exists(FCPATH . $merchant['profile_image_url'])) {
                @unlink(FCPATH . $merchant['profile_image_url']);
            }

            // 7. Finally, delete the merchant record
            $merchantModel->delete($id);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            session()->setFlashdata('success', 'Merchant and all associated data deleted successfully.');
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Failed to delete merchant: ' . $e->getMessage());
            session()->setFlashdata('error', 'Failed to delete merchant: ' . $e->getMessage());
        }

        return redirect()->to('admin/merchants/all');
    }

    public function disableMerchant($id)
    {
        $merchantModel = new \App\Models\MerchantModel();
        $userLoginModel = new UserLoginModel();

        $merchant = $merchantModel->find($id);

        if (!$merchant) {
            session()->setFlashdata('error', 'Merchant not found.');
            return redirect()->to('admin/merchants/all');
        }

        // Disable merchant account
        $updateData = [
            'verification_status' => 'suspended',
            'status' => 'suspended',
            'is_visible' => 0
        ];

        // Also disable user login
        $userLoginModel->where('user_id', $id)
            ->where('user_type', 'merchant')
            ->set(['is_active' => 0])
            ->update();

        if ($merchantModel->update($id, $updateData)) {
            log_message('info', "Merchant ID {$id} disabled by admin");
            session()->setFlashdata('success', 'Merchant disabled successfully. Their account is now inactive and hidden from drivers.');
        } else {
            session()->setFlashdata('error', 'Failed to disable merchant.');
        }

        return redirect()->to('admin/merchants/all');
    }

    public function suspendMerchant($id)
    {
        $merchantModel = new \App\Models\MerchantModel();
        $merchant = $merchantModel->find($id);

        if (!$merchant) {
            session()->setFlashdata('error', 'Merchant not found.');
            return redirect()->to('admin/merchants/all');
        }

        $newStatus = $merchant['verification_status'] === 'suspended' ? 'approved' : 'suspended';
        $updateData = [
            'verification_status' => $newStatus,
            'status' => $newStatus,
            'is_visible' => $newStatus === 'approved' ? 1 : 0
        ];

        if ($merchantModel->update($id, $updateData)) {
            $action = $newStatus === 'suspended' ? 'suspended' : 'reactivated';
            session()->setFlashdata('success', "Merchant {$action} successfully.");
        } else {
            session()->setFlashdata('error', 'Failed to update merchant status.');
        }

        return redirect()->to('admin/merchants/all');
    }

    public function rejectMerchant($id)
    {
        $merchantModel = new \App\Models\MerchantModel();
        $merchant = $merchantModel->find($id);

        if (!$merchant) {
            session()->setFlashdata('error', 'Merchant not found.');
            return redirect()->to('admin/merchants/all');
        }

        $updateData = [
            'verification_status' => 'rejected',
            'status' => 'rejected',
            'is_visible' => 0,
            'rejection_reason' => $this->request->getPost('rejection_reason') ?: 'Rejected by admin'
        ];

        if ($merchantModel->update($id, $updateData)) {
            // Send rejection email
            try {
                $emailService = new \App\Helpers\EmailService();
                $merchantData = [
                    'email' => $merchant['email'],
                    'business_name' => $merchant['business_name'],
                    'contact_person' => $merchant['owner_name'] ?? 'Merchant'
                ];
                $emailService->sendMerchantApprovalNotification($merchantData, 'rejected');
            } catch (\Exception $e) {
                log_message('error', 'Failed to send merchant rejection email: ' . $e->getMessage());
            }

            session()->setFlashdata('success', 'Merchant rejected successfully.');
        } else {
            session()->setFlashdata('error', 'Failed to reject merchant.');
        }

        return redirect()->to('admin/merchants/all');
    }

    /**
     * View merchant analytics
     */
    public function merchantAnalytics($merchantId)
    {
        $merchantModel = new \App\Models\MerchantModel();
        $listingModel = new \App\Models\MerchantListingModel();
        $locationModel = new \App\Models\MerchantLocationModel();

        $merchant = $merchantModel->find($merchantId);

        if (!$merchant) {
            session()->setFlashdata('error', 'Merchant not found.');
            return redirect()->to('admin/merchants/all');
        }

        // Get merchant statistics
        $data['merchant'] = $merchant;
        $data['total_listings'] = $listingModel->where('merchant_id', $merchantId)->countAllResults();
        $data['approved_listings'] = $listingModel->where('merchant_id', $merchantId)->where('status', 'approved')->countAllResults();
        $data['pending_listings'] = $listingModel->where('merchant_id', $merchantId)->where('status', 'pending')->countAllResults();
        $data['rejected_listings'] = $listingModel->where('merchant_id', $merchantId)->where('status', 'rejected')->countAllResults();
        $data['total_locations'] = $locationModel->where('merchant_id', $merchantId)->countAllResults();
        $data['active_locations'] = $locationModel->where('merchant_id', $merchantId)->where('is_active', 1)->countAllResults();

        // Get recent listings
        $data['recent_listings'] = $listingModel->where('merchant_id', $merchantId)
                                                ->orderBy('created_at', 'DESC')
                                                ->limit(10)
                                                ->findAll();

        // Get all locations
        $data['locations'] = $locationModel->where('merchant_id', $merchantId)->findAll();

        $data['page_title'] = 'Analytics - ' . $merchant['business_name'];

        return view('admin/merchants/analytics', $data);
    }

    /**
     * Reset merchant password
     */
    public function resetMerchantPassword($merchantId)
    {
        $merchantModel = new \App\Models\MerchantModel();
        $merchant = $merchantModel->find($merchantId);

        if (!$merchant) {
            session()->setFlashdata('error', 'Merchant not found.');
            return redirect()->to('admin/merchants/all');
        }

        // Generate random password reset token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Update merchant with reset token
        $merchantModel->update($merchantId, [
            'password_reset_token' => $token,
            'password_reset_expires' => $expires
        ]);

        // Send password reset email
        $resetLink = site_url('password/reset/' . $token);

        $email = \Config\Services::email();
        $email->setFrom('noreply@truckersafrica.com', 'Truckers Africa');
        $email->setTo($merchant['email']);
        $email->setSubject('Password Reset Request');

        $message = "Hello {$merchant['owner_name']},\n\n";
        $message .= "An administrator has initiated a password reset for your Truckers Africa account.\n\n";
        $message .= "Click the link below to reset your password:\n";
        $message .= $resetLink . "\n\n";
        $message .= "This link will expire in 1 hour.\n\n";
        $message .= "If you didn't request this, please ignore this email.\n\n";
        $message .= "Best regards,\n";
        $message .= "Truckers Africa Team";

        $email->setMessage($message);

        if ($email->send()) {
            session()->setFlashdata('success', 'Password reset email sent to ' . $merchant['email']);
        } else {
            session()->setFlashdata('error', 'Failed to send password reset email.');
            log_message('error', 'Failed to send password reset email: ' . $email->printDebugger());
        }

        return redirect()->to('admin/merchants/view/' . $merchantId);
    }

    /**
     * Extend merchant trial period
     */
    public function extendMerchantTrial($merchantId)
    {
        $merchantModel = new \App\Models\MerchantModel();
        $subscriptionModel = new \App\Models\SubscriptionModel();

        $merchant = $merchantModel->find($merchantId);

        if (!$merchant) {
            session()->setFlashdata('error', 'Merchant not found.');
            return redirect()->to('admin/merchants/all');
        }

        // Get active subscription
        $subscription = $subscriptionModel->where('merchant_id', $merchantId)
                                         ->where('status', 'trial')
                                         ->first();

        if (!$subscription) {
            session()->setFlashdata('error', 'No active trial subscription found for this merchant.');
            return redirect()->to('admin/merchants/view/' . $merchantId);
        }

        // Extend trial by 7 days
        $currentTrialEnd = $subscription['trial_ends_at'] ?? date('Y-m-d H:i:s');
        $newTrialEnd = date('Y-m-d H:i:s', strtotime($currentTrialEnd . ' +7 days'));

        if ($subscriptionModel->update($subscription['id'], ['trial_ends_at' => $newTrialEnd])) {
            session()->setFlashdata('success', 'Trial period extended by 7 days until ' . date('M j, Y', strtotime($newTrialEnd)));
        } else {
            session()->setFlashdata('error', 'Failed to extend trial period.');
        }

        return redirect()->to('admin/merchants/view/' . $merchantId);
    }

    public function pendingListings()
    {
        $listingModel = new \App\Models\MerchantListingModel();

        // Get search parameters
        $search = $this->request->getGet('search') ?? '';
        $merchantId = $this->request->getGet('merchant_id') ?? '';
        $page = $this->request->getGet('page') ?? 1;
        $perPage = 15; // Number of listings per page

        // Build query with search and filters
        $builder = $listingModel->select('merchant_listings.*, merchants.business_name, merchants.owner_name')
                                ->join('merchants', 'merchants.id = merchant_listings.merchant_id')
                                ->where('merchant_listings.status', 'pending');

        // Apply search filter
        if (!empty($search)) {
            $builder->groupStart()
                    ->like('merchant_listings.title', $search)
                    ->orLike('merchant_listings.description', $search)
                    ->orLike('merchants.business_name', $search)
                    ->orLike('merchants.owner_name', $search)
                    ->groupEnd();
        }

        // Apply merchant filter
        if (!empty($merchantId)) {
            $builder->where('merchant_listings.merchant_id', $merchantId);
        }

        // Get total count for pagination
        $totalListings = $builder->countAllResults(false);

        // Get paginated results
        $listings = $builder->orderBy('merchant_listings.created_at', 'DESC')
                           ->limit($perPage, ($page - 1) * $perPage)
                           ->get()
                           ->getResultArray();

        // Get unique merchants for filter dropdown
        $merchantModel = new \App\Models\MerchantModel();
        $merchants = $merchantModel->select('id, business_name, owner_name')
                                  ->where('verification_status', 'approved')
                                  ->orderBy('business_name', 'ASC')
                                  ->findAll();

        // Calculate pagination info
        $totalPages = ceil($totalListings / $perPage);
        $currentPage = (int) $page;

        $data = [
            'listings' => $listings,
            'merchants' => $merchants,
            'search' => $search,
            'selectedMerchantId' => $merchantId,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'totalListings' => $totalListings,
            'perPage' => $perPage,
            'pager' => $listingModel->pager
        ];

        return view('admin/listings/pending', $data);
    }


    public function approveListing($id)
    {
        $listingModel = new \App\Models\MerchantListingModel();
        $updateData = ['status' => 'approved'];

        if ($listingModel->update($id, $updateData)) {
            // Send approval email and SMS to merchant
            try {
                $notifier = new NotificationService();
                $adminName = session()->get('admin_name');
                $notifier->notifyMerchantListingApproved((int) $id, $adminName ?: null);
            } catch (\Throwable $t) {
                log_message('error', 'Failed to send listing approval notification: ' . $t->getMessage());
            }

            // If listing has a location_id, send email to the branch manager who created it
            try {
                $listing = $listingModel->find($id);

                if ($listing && !empty($listing['location_id'])) {
                    $branchUserModel = new \App\Models\BranchUserModel();
                    $locationModel = new \App\Models\MerchantLocationModel();
                    $emailService = new \App\Helpers\EmailService();

                    // Get the branch user for this location
                    $branchUser = $branchUserModel->where('location_id', $listing['location_id'])->first();

                    // Get the location details
                    $location = $locationModel->find($listing['location_id']);

                    if ($branchUser && $location && !empty($branchUser['email'])) {
                        // Send approval email to branch manager
                        $emailService->sendBranchListingApprovalNotification($listing, $branchUser, $location);
                        log_message('info', 'Branch listing approval email sent for listing ID: ' . $id . ' to branch user: ' . $branchUser['email']);
                    }
                }
            } catch (\Throwable $t) {
                log_message('error', 'Failed to send branch listing approval email: ' . $t->getMessage());
            }

            session()->setFlashdata('success', 'Listing approved successfully. Merchant and branch manager have been notified via email.');
        } else {
            session()->setFlashdata('error', 'Failed to approve listing.');
        }

        return redirect()->to('admin/listings/pending');
    }

    public function approvedListings()
    {
        $listingModel = new \App\Models\MerchantListingModel();

        // Get search parameters
        $search = $this->request->getGet('search') ?? '';
        $merchantId = $this->request->getGet('merchant_id') ?? '';
        $page = $this->request->getGet('page') ?? 1;
        $perPage = 15; // Number of listings per page

        // Build query with search and filters
        $builder = $listingModel->select('merchant_listings.*, merchants.business_name, merchants.owner_name')
                                ->join('merchants', 'merchants.id = merchant_listings.merchant_id')
                                ->where('merchant_listings.status', 'approved');

        // Apply search filter
        if (!empty($search)) {
            $builder->groupStart()
                    ->like('merchant_listings.title', $search)
                    ->orLike('merchant_listings.description', $search)
                    ->orLike('merchants.business_name', $search)
                    ->orLike('merchants.owner_name', $search)
                    ->groupEnd();
        }

        // Apply merchant filter
        if (!empty($merchantId)) {
            $builder->where('merchant_listings.merchant_id', $merchantId);
        }

        // Get total count for pagination
        $totalListings = $builder->countAllResults(false);

        // Get paginated results
        $listings = $builder->orderBy('merchant_listings.updated_at', 'DESC')
                           ->limit($perPage, ($page - 1) * $perPage)
                           ->get()
                           ->getResultArray();

        // Get unique merchants for filter dropdown
        $merchantModel = new \App\Models\MerchantModel();
        $merchants = $merchantModel->select('id, business_name, owner_name')
                                  ->where('verification_status', 'approved')
                                  ->orderBy('business_name', 'ASC')
                                  ->findAll();

        // Calculate pagination info
        $totalPages = ceil($totalListings / $perPage);
        $currentPage = (int) $page;

        // Create pager manually
        $pager = \Config\Services::pager();
        $pager->store('default', $totalListings, $perPage, $currentPage);

        $data = [
            'listings' => $listings,
            'merchants' => $merchants,
            'search' => $search,
            'selectedMerchantId' => $merchantId,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'totalListings' => $totalListings,
            'perPage' => $perPage,
            'pager' => $pager
        ];

        return view('admin/listings/approved', $data);
    }

    public function allListings()
    {
        $listingModel = new \App\Models\MerchantListingModel();

        // Get search parameters
        $search = $this->request->getGet('search') ?? '';
        $status = $this->request->getGet('status') ?? '';
        $merchantId = $this->request->getGet('merchant_id') ?? '';
        $page = $this->request->getGet('page') ?? 1;
        $perPage = 15; // Number of listings per page

        // Build query with search and filters
        $builder = $listingModel->select('merchant_listings.*, merchants.business_name, merchants.owner_name')
                                ->join('merchants', 'merchants.id = merchant_listings.merchant_id');

        // Apply search filter
        if (!empty($search)) {
            $builder->groupStart()
                    ->like('merchant_listings.title', $search)
                    ->orLike('merchant_listings.description', $search)
                    ->orLike('merchants.business_name', $search)
                    ->orLike('merchants.owner_name', $search)
                    ->groupEnd();
        }

        // Apply status filter
        if (!empty($status)) {
            $builder->where('merchant_listings.status', $status);
        }

        // Apply merchant filter
        if (!empty($merchantId)) {
            $builder->where('merchant_listings.merchant_id', $merchantId);
        }

        // Get total count for pagination
        $totalListings = $builder->countAllResults(false);

        // Get paginated results
        $listings = $builder->orderBy('merchant_listings.updated_at', 'DESC')
                           ->limit($perPage, ($page - 1) * $perPage)
                           ->get()
                           ->getResultArray();

        // Get unique merchants for filter dropdown
        $merchantModel = new \App\Models\MerchantModel();
        $merchants = $merchantModel->select('id, business_name, owner_name')
                                  ->where('verification_status', 'approved')
                                  ->orderBy('business_name', 'ASC')
                                  ->findAll();

        // Calculate pagination info
        $totalPages = ceil($totalListings / $perPage);
        $currentPage = (int) $page;

        $data = [
            'listings' => $listings,
            'merchants' => $merchants,
            'search' => $search,
            'selectedStatus' => $status,
            'selectedMerchantId' => $merchantId,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'totalListings' => $totalListings,
            'perPage' => $perPage,
            'pager' => $listingModel->pager
        ];

        return view('admin/listings/all', $data);
    }

    public function rejectListing($id)
    {
        $listingModel = new \App\Models\MerchantListingModel();
        $updateData = ['status' => 'rejected'];

        if ($listingModel->update($id, $updateData)) {
            session()->setFlashdata('success', 'Listing rejected successfully.');
        } else {
            session()->setFlashdata('error', 'Failed to reject listing.');
        }

        return redirect()->back();
    }


    public function relistListing($id)
    {
        $listingModel = new \App\Models\MerchantListingModel();
        // Relisting should set status to 'pending' for admin review
        $updateData = ['status' => 'pending'];

        if ($listingModel->update($id, $updateData)) {
            session()->setFlashdata('success', 'Listing has been relisted and is pending approval.');
        } else {
            session()->setFlashdata('error', 'Failed to relist listing.');
        }

        return redirect()->back();
    }

    public function dashboard()
    {
        // Simple check to protect the dashboard
        if (!session()->get('isAdminLoggedIn')) {
            return redirect()->to('admin/login');
        }

        $merchantModel = new \App\Models\MerchantModel();
        $driverModel = new \App\Models\TruckDriverModel();
        $subscriptionModel = new \App\Models\SubscriptionModel();

        $data = [
            'pending_merchants' => $merchantModel->where('verification_status', 'pending')->countAllResults(),
            'active_merchants' => $merchantModel->where('verification_status', 'approved')->countAllResults(),
            'registered_drivers' => $driverModel->countAllResults(),
            'active_subscriptions' => $subscriptionModel->where('status', 'active')->countAllResults(),
        ];

        return view('admin/dashboard', $data);
    }

    // --------------------------------------------------------------------
    // Plan Management
    // --------------------------------------------------------------------

    public function plans()
    {
        $planModel = new PlanModel();
        $planLimitationModel = new \App\Models\PlanLimitationModel();

        $plans = $planModel->findAll();

        // Load limitations for each plan
        foreach ($plans as &$plan) {
            $plan['limitations'] = $planLimitationModel->where('plan_id', $plan['id'])->findAll();
        }

        $data['plans'] = $plans;

        return view('admin/plans/index', $data);
    }

    public function createPlan()
    {
        return view('admin/plans/create');
    }

    public function storePlan()
    {
        $rules = [
            'name'             => 'required|is_unique[plans.name]',
            'price'            => 'required|decimal',
            'billing_interval' => 'required|in_list[monthly,yearly]',
            'description'      => 'permit_empty|string',
            'has_trial'        => 'required|in_list[0,1]',
            'trial_days'       => 'permit_empty|integer|greater_than_equal_to[0]',
            'max_locations'    => 'permit_empty|integer|greater_than_equal_to[-1]',
            'max_listings'     => 'permit_empty|integer|greater_than_equal_to[-1]',
            'max_categories'   => 'permit_empty|integer|greater_than_equal_to[-1]',
            'max_gallery_images' => 'permit_empty|integer|greater_than_equal_to[-1]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $planModel = new PlanModel();
        $data = [
            'name'             => $this->request->getPost('name'),
            'price'            => $this->request->getPost('price'),
            'billing_interval' => $this->request->getPost('billing_interval'),
            'description'      => $this->request->getPost('description'),
            'has_trial'        => $this->request->getPost('has_trial'),
            'trial_days'       => $this->request->getPost('has_trial') ? $this->request->getPost('trial_days') : 0,
        ];

        $planId = $planModel->insert($data);

        // Insert plan limitations
        $planLimitationModel = new \App\Models\PlanLimitationModel();
        $limitations = [
            'max_locations' => $this->request->getPost('max_locations') ?? 1,
            'max_listings' => $this->request->getPost('max_listings') ?? 5,
            'max_categories' => $this->request->getPost('max_categories') ?? 2,
            'max_gallery_images' => $this->request->getPost('max_gallery_images') ?? 3,
        ];

        foreach ($limitations as $type => $value) {
            $planLimitationModel->insert([
                'plan_id' => $planId,
                'limitation_type' => $type,
                'limit_value' => (int)$value
            ]);
        }

        return redirect()->to('admin/plans')->with('success', 'Plan created successfully.');
    }

    public function editPlan($id)
    {
        $planModel = new PlanModel();
        $data['plan'] = $planModel->find($id);

        if (empty($data['plan'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Cannot find the plan with ID: ' . $id);
        }

        // Load plan limitations
        $planLimitationModel = new \App\Models\PlanLimitationModel();
        $limitations = $planLimitationModel->where('plan_id', $id)->findAll();

        // Convert limitations array to associative array for easier access
        $data['plan_limits'] = [];
        foreach ($limitations as $limit) {
            $data['plan_limits'][$limit['limitation_type']] = $limit['limit_value'];
        }

        return view('admin/plans/edit', $data);
    }

    public function updatePlan($id)
    {
        $rules = [
            'name'             => "required|is_unique[plans.name,id,{$id}]",
            'price'            => 'required|decimal',
            'billing_interval' => 'required|in_list[monthly,yearly]',
            'description'      => 'permit_empty|string',
            'has_trial'        => 'required|in_list[0,1]',
            'trial_days'       => 'permit_empty|integer|greater_than_equal_to[0]',
            'max_locations'    => 'permit_empty|integer|greater_than_equal_to[-1]',
            'max_listings'     => 'permit_empty|integer|greater_than_equal_to[-1]',
            'max_categories'   => 'permit_empty|integer|greater_than_equal_to[-1]',
            'max_gallery_images' => 'permit_empty|integer|greater_than_equal_to[-1]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $planModel = new PlanModel();
        $data = [
            'name'             => $this->request->getPost('name'),
            'price'            => $this->request->getPost('price'),
            'billing_interval' => $this->request->getPost('billing_interval'),
            'description'      => $this->request->getPost('description'),
            'has_trial'        => $this->request->getPost('has_trial'),
            'trial_days'       => $this->request->getPost('has_trial') ? $this->request->getPost('trial_days') : 0,
        ];

        $planModel->update($id, $data);

        // Update plan limitations
        $planLimitationModel = new \App\Models\PlanLimitationModel();
        $db = \Config\Database::connect();

        // Define limitation types and their posted values
        $limitations = [
            'max_locations' => $this->request->getPost('max_locations'),
            'max_listings' => $this->request->getPost('max_listings'),
            'max_categories' => $this->request->getPost('max_categories'),
            'max_gallery_images' => $this->request->getPost('max_gallery_images'),
        ];

        foreach ($limitations as $type => $value) {
            // Check if limitation exists
            $existing = $planLimitationModel->where('plan_id', $id)
                                           ->where('limitation_type', $type)
                                           ->first();

            if ($existing) {
                // Update existing limitation
                $planLimitationModel->update($existing['id'], ['limit_value' => (int)$value]);
            } else {
                // Insert new limitation
                $planLimitationModel->insert([
                    'plan_id' => $id,
                    'limitation_type' => $type,
                    'limit_value' => (int)$value
                ]);
            }
        }

        return redirect()->to('admin/plans')->with('success', 'Plan updated successfully.');
    }

    public function deletePlan($id)
    {
        $planModel = new PlanModel();
        $planModel->delete($id);

        return redirect()->to('admin/plans')->with('success', 'Plan deleted successfully.');
    }

    // --------------------------------------------------------------------
    // Feature Management
    // --------------------------------------------------------------------

    public function features()
    {
        $featureModel = new FeatureModel();
        $data['features'] = $featureModel->findAll();

        return view('admin/features/index', $data);
    }

    public function createFeature()
    {
        return view('admin/features/create');
    }

    public function storeFeature()
    {
        $rules = [
            'name' => 'required|string|max_length[255]',
            'code' => 'required|string|is_unique[features.code]|max_length[255]',
            'description' => 'permit_empty|string',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $featureModel = new FeatureModel();
        $featureModel->save([
            'name'        => $this->request->getPost('name'),
            'code'        => $this->request->getPost('code'),
            'description' => $this->request->getPost('description'),
        ]);

        return redirect()->to('admin/features')->with('success', 'Feature created successfully.');
    }

    public function editFeature($id)
    {
        $featureModel = new FeatureModel();
        $data['feature'] = $featureModel->find($id);

        if (empty($data['feature'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Cannot find the feature with ID: ' . $id);
        }

        return view('admin/features/edit', $data);
    }

    public function updateFeature($id)
    {
        $rules = [
            'name' => 'required|string|max_length[255]',
            'code' => "required|string|is_unique[features.code,id,{$id}]|max_length[255]",
            'description' => 'permit_empty|string',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $featureModel = new FeatureModel();
        $featureModel->update($id, [
            'name'        => $this->request->getPost('name'),
            'code'        => $this->request->getPost('code'),
            'description' => $this->request->getPost('description'),
        ]);

        return redirect()->to('admin/features')->with('success', 'Feature updated successfully.');
    }

    public function deleteFeature($id)
    {
        $featureModel = new FeatureModel();
        $featureModel->delete($id);

        return redirect()->to('admin/features')->with('success', 'Feature deleted successfully.');
    }

    // --------------------------------------------------------------------
    // Plan-Feature Management
    // --------------------------------------------------------------------

    public function managePlanFeatures($planId)
    {
        $planModel = new PlanModel();
        $featureModel = new FeatureModel();
        $planFeatureModel = new PlanFeatureModel();

        $plan = $planModel->find($planId);
        if (!$plan) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Plan not found.');
        }

        $data['plan'] = $plan;
        $data['all_features'] = $featureModel->findAll();

        // Get current features with their order
        $data['current_features'] = $planModel->getFeatures($planId);
        $data['current_feature_ids'] = array_column($data['current_features'], 'id');

        return view('admin/plans/manage_features', $data);
    }

    public function updatePlanFeatures($planId)
    {
        $planFeatureModel = new PlanFeatureModel();

        // Validate plan ID
        if (!is_numeric($planId) || $planId <= 0) {
            return redirect()->to('admin/plans')->with('error', 'Invalid plan ID.');
        }

        // Get the submitted feature IDs from the form
        $submittedFeatureIds = $this->request->getPost('features') ?? [];

        // Validate feature IDs are numeric
        $validFeatureIds = [];
        if (!empty($submittedFeatureIds)) {
            foreach ($submittedFeatureIds as $featureId) {
                if (is_numeric($featureId) && $featureId > 0) {
                    $validFeatureIds[] = (int) $featureId;
                }
            }
        }

        try {
            // Delete existing features for this plan
            $planFeatureModel->where('plan_id', $planId)->delete();

            // Insert the new set of features with sort order
            if (!empty($validFeatureIds)) {
                $dataToInsert = [];
                foreach ($validFeatureIds as $index => $featureId) {
                    $dataToInsert[] = [
                        'plan_id' => (int) $planId,
                        'feature_id' => $featureId,
                        'sort_order' => $index + 1
                    ];
                }

                $result = $planFeatureModel->insertBatch($dataToInsert);

                if ($result === false) {
                    throw new \Exception('Failed to insert plan features');
                }
            }

            return redirect()->to('admin/plans/manage/' . $planId)->with('success', 'Plan features updated successfully.');

        } catch (\Exception $e) {
            log_message('error', 'Error updating plan features: ' . $e->getMessage());
            return redirect()->to('admin/plans/manage/' . $planId)->with('error', 'Failed to update plan features. Please try again.');
        }
    }

    /**
     * Update the features and their order for a plan via AJAX
     * This replaces all features with the new list (handles add, remove, and reorder)
     */
    public function updateFeatureOrder($planId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $planModel = new PlanModel();
        $planFeatureModel = new PlanFeatureModel();

        $plan = $planModel->find($planId);
        if (!$plan) {
            return $this->response->setJSON(['success' => false, 'message' => 'Plan not found']);
        }

        $input = $this->request->getJSON(true);
        $featureOrder = $input['feature_order'] ?? [];

        // Allow empty array (to remove all features)
        if (!is_array($featureOrder)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid feature order format']);
        }

        try {
            // Delete all existing features for this plan
            $planFeatureModel->where('plan_id', $planId)->delete();

            // Insert the new set of features with sort order
            if (!empty($featureOrder)) {
                $dataToInsert = [];
                foreach ($featureOrder as $index => $featureId) {
                    // Validate feature ID is numeric
                    if (is_numeric($featureId) && $featureId > 0) {
                        $dataToInsert[] = [
                            'plan_id' => (int) $planId,
                            'feature_id' => (int) $featureId,
                            'sort_order' => $index + 1
                        ];
                    }
                }

                if (!empty($dataToInsert)) {
                    $result = $planFeatureModel->insertBatch($dataToInsert);

                    if ($result === false) {
                        throw new \Exception('Failed to insert plan features');
                    }
                }
            }

            return $this->response->setJSON(['success' => true, 'message' => 'Features updated successfully']);

        } catch (\Exception $e) {
            log_message('error', 'Failed to update plan features: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'An error occurred while updating features']);
        }
    }

    // --------------------------------------------------------------------
    // Email Marketing
    // --------------------------------------------------------------------

    public function emailMarketing()
    {
        $campaignModel = new EmailCampaignModel();
        $data['campaigns'] = $campaignModel->orderBy('created_at', 'DESC')->findAll();

        return view('admin/email/index', $data);
    }

    public function createEmailCampaign()
    {
        return view('admin/email/create');
    }

    public function storeEmailCampaign()
    {
        $rules = [
            'subject'      => 'required|string|max_length[255]',
            'body'         => 'required|string',
            'target_group' => 'required|in_list[all_merchants,all_drivers,all_users]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $campaignModel = new EmailCampaignModel();
        $campaignModel->save([
            'subject'      => $this->request->getPost('subject'),
            'body'         => $this->request->getPost('body'),
            'target_group' => $this->request->getPost('target_group'),
            'status'       => 'draft',
        ]);

        return redirect()->to('admin/email-marketing')->with('success', 'Email campaign saved as a draft.');
    }

    public function sendEmailCampaign($id)
    {
        $campaignModel = new EmailCampaignModel();
        $campaign = $campaignModel->find($id);

        if (!$campaign) {
            return redirect()->to('admin/email-marketing')->with('error', 'Campaign not found.');
        }

        $merchantModel = new MerchantModel();
        $driverModel = new TruckDriverModel();
        $emails = [];

        switch ($campaign['target_group']) {
            case 'all_merchants':
                $merchants = $merchantModel->select('email')->findAll();
                $emails = array_column($merchants, 'email');
                break;
            case 'all_drivers':
                $drivers = $driverModel->select('email')->findAll();
                $emails = array_column($drivers, 'email');
                break;
            case 'all_users':
                $merchants = $merchantModel->select('email')->findAll();
                $drivers = $driverModel->select('email')->findAll();
                $emails = array_merge(array_column($merchants, 'email'), array_column($drivers, 'email'));
                break;
        }

        if (empty($emails)) {
            return redirect()->to('admin/email-marketing')->with('error', 'No recipients found for the target group.');
        }

        $emailService = service('email');
        $emailService->setFrom(getenv('email.fromEmail'), getenv('email.fromName'));
        $emailService->setSubject($campaign['subject']);
        $emailService->setMessage($campaign['body']);

        $campaignModel->update($id, ['status' => 'sending']);

        $success = $emailService->setBCC($emails)->send();

        if ($success) {
            $campaignModel->update($id, ['status' => 'sent', 'sent_at' => date('Y-m-d H:i:s')]);
            return redirect()->to('admin/email-marketing')->with('success', 'Email campaign sent successfully.');
        } else {
            $campaignModel->update($id, ['status' => 'failed']);
            log_message('error', $emailService->printDebugger(['headers']));
            return redirect()->to('admin/email-marketing')->with('error', 'Failed to send email campaign. Check the logs for details.');
        }
    }

    /**
     * Logs the admin out.
     */
    public function register()
    {
        if ($this->request->getMethod() === 'post') {
            $rules = [
                'name' => 'required|min_length[3]|max_length[50]',
                'email' => 'required|valid_email|is_unique[admins.email]',
                'password' => 'required|min_length[8]',
                'confirm_password' => 'matches[password]'
            ];

            if (!$this->validate($rules)) {
                return view('admin/register', [
                    'validation' => $this->validator
                ]);
            }

            $model = new AdminModel();
            $model->save([
                'name' => $this->request->getVar('name'),
                'email' => $this->request->getVar('email'),
                'password' => $this->request->getVar('password')
            ]);

            return redirect()->to('admin/login')->with('message', 'Registration successful. Please log in.');
        }

        return view('admin/register');
    }

    public function viewListing($listingId)
    {
        $listingModel = new \App\Models\MerchantListingModel();
        $locationModel = new \App\Models\MerchantLocationModel();

        // Get listing with merchant information
        $listing = $listingModel->select('merchant_listings.*, merchants.business_name, merchants.owner_name, merchants.email, merchants.business_contact_number, merchants.business_whatsapp_number, merchants.physical_address, merchants.latitude, merchants.longitude')
                                ->join('merchants', 'merchants.id = merchant_listings.merchant_id')
                                ->where('merchant_listings.id', $listingId)
                                ->first();

        if (!$listing) {
            return redirect()->to('admin/listings/all')->with('error', 'Listing not found.');
        }

        // Get location information
        $location = null;
        if (!empty($listing['location_id'])) {
            $location = $locationModel->find($listing['location_id']);
        }

        // Fetch gallery images
        $imageModel = new \App\Models\MerchantListingImageModel();
        $galleryImages = $imageModel->findByListingId($listingId);

        // Get currency information
        $currencyModel = new \App\Models\CurrencyModel();
        $currency = $currencyModel->where('currency_code', $listing['currency_code'])->first();

        // Get categories for this listing
        $categoryModel = new \App\Models\MerchantListingCategoryModel();
        $categories = $categoryModel->getCategoriesForListing($listingId);

        // Get services (subcategories) for this listing
        $serviceModel = new \App\Models\MerchantListingServiceModel();
        $services = $serviceModel->getServicesForListing($listingId);

        $data = [
            'listing' => $listing,
            'gallery_images' => $galleryImages,
            'currency' => $currency,
            'categories' => $categories,
            'services' => $services,
            'location' => $location,
            'page_title' => 'View Listing - ' . $listing['title']
        ];

        return view('admin/listings/view', $data);
    }

    public function logout()
    {
        session()->remove(['admin_id', 'admin_name', 'isAdminLoggedIn']);
        return redirect()->to('admin/login')->with('message', 'You have been logged out.');
    }
}
