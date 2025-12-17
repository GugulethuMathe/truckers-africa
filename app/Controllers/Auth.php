<?php

namespace App\Controllers;

use App\Models\TruckDriverModel;
use App\Models\MerchantModel;
use App\Models\UserLoginModel;
use App\Models\PlanModel;
use App\Models\SubscriptionModel;
use App\Models\PasswordResetTokenModel;
use App\Services\NotificationService;
use App\Helpers\EmailService;
use CodeIgniter\Controller;
use CodeIgniter\API\ResponseTrait;
use League\OAuth2\Client\Provider\Google;

/**
 * @property \CodeIgniter\HTTP\IncomingRequest $request
 */
class Auth extends Controller
{
    use ResponseTrait;

    public function login()
    {
        if ($this->request->is('post')) {
            $rules = [
                'email'    => 'required|valid_email',
                'password' => 'required',
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');

            $userLoginModel = new UserLoginModel();
            $userLogin = $userLoginModel->where('email', $email)->first();

            if (!$userLogin) {
                return redirect()->back()->withInput()->with('error', 'Invalid email or password.');
            }

            // Check if account is locked
            if ($userLogin['locked_until'] && strtotime($userLogin['locked_until']) > time()) {
                return redirect()->back()->withInput()->with('error', 'Account is temporarily locked. Please try again later.');
            }

            // Check if account is active
            if (!$userLogin['is_active']) {
                return redirect()->back()->withInput()->with('error', 'Account is deactivated. Please contact support.');
            }

            // Verify password
            if (!password_verify($password, $userLogin['password_hash'])) {
                // Increment login attempts
                $attempts = $userLogin['login_attempts'] + 1;
                $updateData = ['login_attempts' => $attempts];

                // Lock account after 5 failed attempts for 15 minutes
                if ($attempts >= 5) {
                    $updateData['locked_until'] = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                }

                $userLoginModel->update($userLogin['id'], $updateData);
                return redirect()->back()->withInput()->with('error', 'Invalid email or password.');
            }

            // Reset login attempts on successful login
            $userLoginModel->update($userLogin['id'], [
                'login_attempts' => 0,
                'locked_until' => null,
                'last_login_at' => date('Y-m-d H:i:s')
            ]);

            // Get user profile based on user type
            if ($userLogin['user_type'] === 'merchant') {
                $merchantModel = new MerchantModel();
                $user = $merchantModel->find($userLogin['user_id']);
                
                if (!$user) {
                    return redirect()->back()->withInput()->with('error', 'User profile not found.');
                }
                
                // Set merchant session data
                $sessionData = [
                    'user_id' => $user['id'],
                    'merchant_id' => $user['id'], // Add merchant_id for clarity and consistency
                    'user_type' => 'merchant',
                    'email' => $userLogin['email'],
                    'business_name' => $user['business_name'] ?? '',
                    'first_name' => $user['first_name'] ?? '',
                    'last_name' => $user['last_name'] ?? '',
                    'is_logged_in' => true,
                    'verification_status' => $user['verification_status'] ?? 'pending'
                ];
                
                session()->set($sessionData);
                return redirect()->to('merchant/dashboard')->with('message', 'Login Successful!');
                
            } else {
                $driverModel = new TruckDriverModel();
                $user = $driverModel->find($userLogin['user_id']);
                
                if (!$user) {
                    return redirect()->back()->withInput()->with('error', 'User profile not found.');
                }
                
                // Set driver session data
                $sessionData = [
                    'user_id' => $user['id'],
                    'user_type' => 'driver',
                    'email' => $userLogin['email'],
                    'first_name' => $user['first_name'] ?? '',
                    'last_name' => $user['last_name'] ?? '',
                    'is_logged_in' => true,
                    'license_number' => $user['license_number'] ?? ''
                ];
                
                session()->set($sessionData);
                return redirect()->to('dashboard/driver')->with('message', 'Login Successful!');
            }
        }

        return view('auth/login');
    }


    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login')->with('message', 'You have been logged out successfully.');
    }

    /**
     * Show forgot password form
     */
    public function forgotPassword()
    {
        return view('auth/forgot_password');
    }

    /**
     * Process forgot password request
     */
    public function processForgotPassword()
    {
        if (!$this->request->is('post')) {
            return redirect()->to('auth/forgot-password');
        }

        $rules = [
            'email' => 'required|valid_email'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email = $this->request->getPost('email');

        // Check if email exists in user_logins table
        $userLoginModel = new UserLoginModel();
        $user = $userLoginModel->where('email', $email)->first();

        if (!$user) {
            // Don't reveal if email exists or not for security
            return redirect()->back()->with('message', 'If your email is registered, you will receive a password reset link shortly.');
        }

        // Determine user type and get user details
        $userType = $user['user_type'];
        $userDetails = null;

        if ($userType === 'driver') {
            $driverModel = new TruckDriverModel();
            $userDetails = $driverModel->find($user['user_id']);
        } elseif ($userType === 'merchant') {
            $merchantModel = new MerchantModel();
            $userDetails = $merchantModel->find($user['user_id']);
        }

        if (!$userDetails) {
            return redirect()->back()->with('message', 'If your email is registered, you will receive a password reset link shortly.');
        }

        // Create password reset token
        $tokenModel = new PasswordResetTokenModel();
        $token = $tokenModel->createToken($email, $userType);

        if ($token) {
            // Send password reset email
            try {
                $notificationService = new NotificationService();
                $this->sendPasswordResetEmail($email, $token, $userType, $userDetails);

                return redirect()->back()->with('message', 'If your email is registered, you will receive a password reset link shortly.');
            } catch (\Exception $e) {
                log_message('error', 'Failed to send password reset email: ' . $e->getMessage());
                return redirect()->back()->with('error', 'Failed to send password reset email. Please try again.');
            }
        }

        return redirect()->back()->with('error', 'Failed to process password reset request. Please try again.');
    }

    /**
     * Show reset password form
     */
    public function resetPassword()
    {
        $token = $this->request->getGet('token');
        $email = $this->request->getGet('email');
        $userType = $this->request->getGet('type');

        if (!$token || !$email || !$userType) {
            return redirect()->to('login')->with('error', 'Invalid password reset link.');
        }

        // Validate token
        $tokenModel = new PasswordResetTokenModel();
        $tokenRecord = $tokenModel->validateToken($token, $email, $userType);

        if (!$tokenRecord) {
            return redirect()->to('login')->with('error', 'Invalid or expired password reset link.');
        }

        return view('auth/reset_password', [
            'token' => $token,
            'email' => $email,
            'user_type' => $userType
        ]);
    }

    /**
     * Process password reset
     */
    public function processResetPassword()
    {
        if (!$this->request->is('post')) {
            return redirect()->to('login');
        }

        $rules = [
            'token' => 'required',
            'email' => 'required|valid_email',
            'user_type' => 'required|in_list[driver,merchant]',
            'password' => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $token = $this->request->getPost('token');
        $email = $this->request->getPost('email');
        $userType = $this->request->getPost('user_type');
        $newPassword = $this->request->getPost('password');

        // Validate token
        $tokenModel = new PasswordResetTokenModel();
        $tokenRecord = $tokenModel->validateToken($token, $email, $userType);

        if (!$tokenRecord) {
            return redirect()->to('login')->with('error', 'Invalid or expired password reset link.');
        }

        // Update password in user_logins table
        $userLoginModel = new UserLoginModel();
        $user = $userLoginModel->where('email', $email)->where('user_type', $userType)->first();

        if (!$user) {
            return redirect()->to('login')->with('error', 'User not found.');
        }

        // Update password
        $success = $userLoginModel->updatePassword($user['id'], $newPassword);

        if ($success) {
            // Mark token as used
            $tokenModel->markTokenAsUsed($tokenRecord['id']);

            return redirect()->to('login')->with('message', 'Password reset successfully. You can now log in with your new password.');
        }

        return redirect()->back()->with('error', 'Failed to reset password. Please try again.');
    }

    /**
     * Send password reset email
     */
    private function sendPasswordResetEmail(string $email, string $token, string $userType, array $userDetails)
    {
        $resetUrl = site_url("auth/reset-password?token={$token}&email=" . urlencode($email) . "&type={$userType}");

        $emailService = \Config\Services::email();
        $subject = 'Password Reset Request - Truckers Africa';

        $message = view('emails/password_reset', [
            'user' => $userDetails,
            'user_type' => $userType,
            'reset_url' => $resetUrl,
            'token_expires' => '1 hour'
        ]);

        $emailService->setTo($email);
        $emailService->setSubject($subject);
        $emailService->setMessage($message);

        return $emailService->send();
    }

    public function chooseUserType()
    {
        return view('auth/choose_type');
    }

    public function signupDriverForm()
    {
        return view('auth/signup-driver');
    }

    public function createDriver()
    {
        $rules = [
            'full_name' => 'required|min_length[3]',
            'email'     => 'required|valid_email|is_unique[user_logins.email]',
            'phone'     => 'required|regex_match[/^\d{8,15}$/]',
            'password'  => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Create truck driver profile
            $driverModel = new TruckDriverModel();
            // Normalize phone to digits only
            $rawPhone = preg_replace('/\D+/', '', $this->request->getPost('phone'));
            if (strlen($rawPhone) > 0 && $rawPhone[0] === '0') {
                $rawPhone = ltrim($rawPhone, '0');
            }

            $driverData = [
                'name'           => $this->request->getPost('full_name'),
                'email'          => $this->request->getPost('email'),
                'contact_number' => $rawPhone,
            ];

            $driverId = $driverModel->insert($driverData);

            if (!$driverId) {
                throw new \Exception('Failed to create driver profile');
            }

            // Create login record
            $userLoginModel = new UserLoginModel();
            $loginData = [
                'email' => $this->request->getPost('email'),
                'password_hash' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
                'user_type' => 'truck_driver',
                'user_id' => $driverId,
                'is_active' => 1
            ];

            $loginId = $userLoginModel->insert($loginData);

            if (!$loginId) {
                throw new \Exception('Failed to create login record');
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            $newUser = $driverModel->find($driverId);
            // Notify driver via email/SMS and email admins
            try {
                $notifier = new NotificationService();
                $notifier->notifyDriverRegistered((int) $driverId);
                $notifier->notifyAdminsNewDriver((array) $newUser);

                // Send welcome email to driver
                $emailService = new EmailService();
                $driverEmailData = [
                    'email' => $newUser['email'],
                    'first_name' => $newUser['first_name'] ?? explode(' ', $newUser['name'])[0] ?? 'Driver',
                    'last_name' => $newUser['last_name'] ?? ''
                ];
                $emailService->sendDriverWelcomeEmail($driverEmailData);

            } catch (\Throwable $t) {
                log_message('error', 'Failed notifying driver after signup: ' . $t->getMessage());
            }

            $this->setUserSession($newUser, 'driver');
            return redirect()->to('dashboard/driver')->with('message', 'Welcome! Your driver account has been created.');

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Failed to create account. Please try again.');
        }
    }

    public function signupMerchantForm()
    {
        $data = [
            'googleUser' => session('google_user')
        ];
        return view('auth/signup-merchant', $data);
    }

    public function createMerchant()
    {
        $request = \Config\Services::request();

        $rules = [
            'full_name'    => 'required|min_length[3]',
            'business_type' => 'required|in_list[individual,business]',
            'company_name' => 'required',
            'email'        => 'required|valid_email|is_unique[user_logins.email]',
            // The hidden combined phone value
            'phone'        => 'required|regex_match[/^\d{8,15}$/]',
            'password'     => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]'
        ];
// rewrite email exist error
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Create merchant profile
            $merchantModel = new MerchantModel();
            // Normalize the phone to international digits only, remove any leading zeros in the local part if user bypassed JS
            $rawPhone = preg_replace('/\D+/', '', $request->getPost('phone'));
            if (strlen($rawPhone) > 0 && $rawPhone[0] === '0') {
                $rawPhone = ltrim($rawPhone, '0');
            }

            $merchantData = [
                'owner_name'      => $request->getPost('full_name'),
                'business_name'   => $request->getPost('company_name'),
                'email'           => $request->getPost('email'),
                'business_contact_number' => $rawPhone,
                'business_type'   => $request->getPost('business_type'),
                'verification_status' => 'pending',
                'is_visible'          => false,
                'onboarding_completed' => 1, // Self-registered merchants complete onboarding during signup
            ];

            $merchantId = $merchantModel->insert($merchantData);

            if (!$merchantId) {
                throw new \Exception('Failed to create merchant profile');
            }

            // Create login record
            $userLoginModel = new UserLoginModel();
            $loginData = [
                'email' => $request->getPost('email'),
                'password_hash' => password_hash($request->getPost('password'), PASSWORD_DEFAULT),
                'user_type' => 'merchant',
                'user_id' => $merchantId,
                'is_active' => 1
            ];

            $loginId = $userLoginModel->insert($loginData);

            if (!$loginId) {
                throw new \Exception('Failed to create login record');
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            // Notify admins via email/SMS about new merchant
            try {
                $notifier = new NotificationService();
                $notifier->notifyAdminsNewMerchant(array_merge(['id' => $merchantId], $merchantData));
                // Notify merchant confirmation (email + SMS)
                $notifier->notifyMerchantRegistered((int) $merchantId);
            } catch (\Throwable $t) {
                // Do not block registration on notification failures
                log_message('error', 'Failed notifying admins about new merchant: ' . $t->getMessage());
            }

            // Set complete session for the new merchant to log them in
            $sessionData = [
                'user_id'             => $merchantId,
                'merchant_id'         => $merchantId,
                'user_type'           => 'merchant',
                'email'               => $merchantData['email'],
                'business_name'       => $merchantData['business_name'],
                'first_name'          => $merchantData['owner_name'],
                'last_name'           => '',
                'is_logged_in'        => true,
                'verification_status' => 'pending'
            ];
            
            session()->set($sessionData);

            return redirect()->to('pricing')->with('message', 'Registration successful! Please choose a plan.');

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Failed to create merchant account. Please try again.');
        }
    }

    public function googleLogin()
    {
        $googleProvider = new Google([
            'clientId'     => getenv('GOOGLE_CLIENT_ID'),
            'clientSecret' => getenv('GOOGLE_CLIENT_SECRET'),
            'redirectUri'  => getenv('GOOGLE_REDIRECT_URI'),
        ]);

        $authUrl = $googleProvider->getAuthorizationUrl();
        session()->set('oauth2state', $googleProvider->getState());

        return redirect()->to($authUrl);
    }

    /**
     * Handles the callback from Google after authentication.
     */
    public function googleCallback()
    {
        $request = \Config\Services::request();

        $googleProvider = new Google([
            'clientId'     => getenv('GOOGLE_CLIENT_ID'),
            'clientSecret' => getenv('GOOGLE_CLIENT_SECRET'),
            'redirectUri'  => getenv('GOOGLE_REDIRECT_URI'),
        ]);

        if (empty($request->getVar('state')) || ($request->getVar('state') !== session()->get('oauth2state'))) {
            session()->remove('oauth2state');
            return redirect()->to('login')->with('error', 'Invalid state. Authentication failed.');
        }

        try {
            $token = $googleProvider->getAccessToken('authorization_code', ['code' => $request->getVar('code')]);
            $googleUser = $googleProvider->getResourceOwner($token);

            /** @var \League\OAuth2\Client\Provider\GoogleUser $googleUser */

            $userLoginModel = new UserLoginModel();
            $userLogin = $userLoginModel->where('google_id', $googleUser->getId())->first();

            if ($userLogin) {
                // Update last login
                $userLoginModel->update($userLogin['id'], [
                    'last_login_at' => date('Y-m-d H:i:s')
                ]);

                // Get user profile based on user type
                if ($userLogin['user_type'] === 'merchant') {
                    $merchantModel = new MerchantModel();
                    $user = $merchantModel->find($userLogin['user_id']);
                    $this->setUserSession($user, 'merchant');
                    return redirect()->to('merchant/dashboard')->with('message', 'Welcome back!');
                } else {
                    $driverModel = new TruckDriverModel();
                    $user = $driverModel->find($userLogin['user_id']);
                    $this->setUserSession($user, 'driver');
                    return redirect()->to('dashboard/driver')->with('message', 'Welcome back!');
                }
            }
            
            // Store Google user data for registration
            session()->set('google_user', [
                'id'    => $googleUser->getId(),
                'email' => $googleUser->getEmail(),
                'name'  => $googleUser->getName(),
            ]);
            
            return redirect()->to('signup')->with('message', 'Please choose your account type to complete registration.');

        } catch (\Exception $e) {
            return redirect()->to('login')->with('error', 'Failed to authenticate with Google: ' . $e->getMessage());
        }
    }

    public function createGoogleUser($userType)
    {
        if (!session()->has('google_user')) {
            return redirect()->to('login')->with('error', 'Google authentication data not found.');
        }

        $googleUser = session('google_user');
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            if ($userType === 'merchant') {
                // Create merchant profile
                $merchantModel = new MerchantModel();
                $merchantData = [
                    'owner_name'      => $googleUser['name'],
                    'business_name'   => $googleUser['name'] . "'s Business", // Default business name
                    'email'           => $googleUser['email'],
                    'business_type'   => 'individual', // Default to individual for Google OAuth
                    'verification_status' => 'pending',
                    'is_visible'          => false,
                ];

                $userId = $merchantModel->insert($merchantData);
            } else {
                // Create truck driver profile
                $driverModel = new TruckDriverModel();
                $driverData = [
                    'name'  => $googleUser['name'],
                    'email' => $googleUser['email'],
                ];

                $userId = $driverModel->insert($driverData);
            }

            if (!$userId) {
                throw new \Exception('Failed to create user profile');
            }

            // Create login record
            $userLoginModel = new UserLoginModel();
            $loginData = [
                'email' => $googleUser['email'],
                'google_id' => $googleUser['id'],
                'user_type' => $userType === 'merchant' ? 'merchant' : 'truck_driver',
                'user_id' => $userId,
                'is_active' => 1,
                'last_login_at' => date('Y-m-d H:i:s')
            ];

            $loginId = $userLoginModel->insert($loginData);

            if (!$loginId) {
                throw new \Exception('Failed to create login record');
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            session()->remove('google_user');

            if ($userType === 'merchant') {
                session()->set('new_merchant_id', $userId);
                return redirect()->to('auth/packages');
            } else {
                $user = $driverModel->find($userId);
                $this->setUserSession($user, 'driver');
                return redirect()->to('dashboard/driver')->with('message', 'Welcome! Your Google account has been linked.');
            }

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->to('signup')->with('error', 'Failed to create account. Please try again.');
        }
    }

    public function packages()
    {
        if (!session()->has('merchant_id')) {
            return redirect()->to('login')->with('error', 'You must be logged in to view subscription packages.');
        }

        $planModel = new PlanModel();
        $data['plans'] = $planModel->findAll();
        
        if (!empty($data['plans'])) {
            foreach($data['plans'] as &$plan) {
                $plan['features'] = $planModel->getFeatures($plan['id']);
            }
        }
        
        return view('auth/packages', $data);
    }

    public function selectPlan($planId)
    {
        if (!session()->has('merchant_id')) {
            return redirect()->to('login')->with('error', 'You must be logged in to select a plan.');
        }

        $merchantId = session('merchant_id');
        $planModel = new PlanModel();
        $plan = $planModel->find($planId);

        if (!$plan) {
            return redirect()->to('auth/packages')->with('error', 'Invalid plan selected.');
        }

        $subscriptionModel = new SubscriptionModel();
        $existingSubscription = $subscriptionModel->where('merchant_id', $merchantId)->first();

        // Determine subscription status based on plan type
        // - Plans with trial: 'trial_pending' (awaiting payment method capture)
        // - Plans without trial: 'new' (awaiting initial payment)
        // - Free plans (price = 0): 'active' (no payment needed)
        $isFree = ($plan['price'] <= 0);
        $hasTrial = !empty($plan['trial_days']) && $plan['trial_days'] > 0;

        $subscriptionData = [
            'plan_id' => $planId,
            'status' => $isFree ? 'active' : ($hasTrial ? 'trial_pending' : 'new'),
            'trial_ends_at' => $hasTrial ? date('Y-m-d H:i:s', strtotime('+' . $plan['trial_days'] . ' days')) : null,
            'current_period_starts_at' => $isFree ? date('Y-m-d H:i:s') : null, // Set after payment
            'current_period_ends_at' => $isFree ? date('Y-m-d H:i:s', strtotime('+1 month')) : null, // Set after payment
        ];

        if ($existingSubscription) {
            // Update existing subscription
            if (!$subscriptionModel->update($existingSubscription['id'], $subscriptionData)) {
                return redirect()->to('auth/packages')->with('error', 'Failed to update your subscription. Please try again.');
            }
        } else {
            // Create new subscription
            $subscriptionData['merchant_id'] = $merchantId;
            if (!$subscriptionModel->insert($subscriptionData)) {
                return redirect()->to('auth/packages')->with('error', 'Failed to create your subscription. Please try again.');
            }
        }

        // If the plan is free, redirect to the dashboard. Otherwise, proceed to payment.
        if ($plan['price'] <= 0) {
            return redirect()->to('merchant/dashboard')->with('message', 'Welcome! Your free plan is now active.');
        }

        // Redirect to the payment gateway to complete the subscription
        return redirect()->to('payment/process/' . $planId);
    }

    private function setUserSession(array $user, string $type)
    {
        $sessionData = [
            'is_logged_in' => true,
            'isLoggedIn' => true, // Keep for backward compatibility
            'user_type'  => $type,
            'user_id'    => $user['id'], // Changed from 'id' to 'user_id' to match DriverDashboard expectations
            'id'         => $user['id'], // Keep 'id' for backward compatibility
            'email'      => $user['email'],
        ];

        if ($type === 'merchant') {
            $sessionData['name'] = $user['owner_name'];
        } else {
            $sessionData['name'] = $user['name'];
        }

        session()->set($sessionData);
    }

    /**
     * Show password setup form for admin-created merchants
     */
    public function setupMerchantPassword()
    {
        $token = $this->request->getGet('token');
        $email = $this->request->getGet('email');

        if (!$token || !$email) {
            return redirect()->to('login')->with('error', 'Invalid password setup link.');
        }

        // Validate token
        $merchantModel = new MerchantModel();
        $merchant = $merchantModel->validatePasswordResetToken($email, $token);

        if (!$merchant) {
            return redirect()->to('login')->with('error', 'Invalid or expired password setup link. Please contact support.');
        }

        return view('auth/merchant_setup_password', [
            'token' => $token,
            'email' => $email,
            'merchant' => $merchant
        ]);
    }

    /**
     * Process password setup for admin-created merchants
     */
    public function processSetupMerchantPassword()
    {
        if (!$this->request->is('post')) {
            return redirect()->to('login');
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
        $merchantModel = new MerchantModel();
        $merchant = $merchantModel->validatePasswordResetToken($email, $token);

        if (!$merchant) {
            return redirect()->to('login')->with('error', 'Invalid or expired password setup link. Please contact support.');
        }

        // Start database transaction
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Update password in merchants table
            $merchantModel->updatePassword($merchant['id'], $newPassword);

            // Update password in user_logins table
            $userLoginModel = new UserLoginModel();
            $userLogin = $userLoginModel->where('email', $email)
                                        ->where('user_type', 'merchant')
                                        ->first();

            if ($userLogin) {
                $userLoginModel->update($userLogin['id'], [
                    'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT)
                ]);
            }

            // Clear the reset token
            $merchantModel->clearPasswordResetToken($merchant['id']);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            // Log the merchant in automatically and redirect to onboarding
            $sessionData = [
                'user_id' => $merchant['id'],
                'merchant_id' => $merchant['id'],
                'user_type' => 'merchant',
                'email' => $merchant['email'],
                'business_name' => $merchant['business_name'],
                'first_name' => $merchant['owner_name'],
                'last_name' => '',
                'is_logged_in' => true,
                'verification_status' => $merchant['verification_status']
            ];

            session()->set($sessionData);

            log_message('info', 'Password set successfully for merchant: ' . $email . '. Redirecting to onboarding.');
            return redirect()->to('merchant/onboarding')->with('message', 'Welcome! Let\'s complete your business profile.');

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Failed to set password for merchant ' . $email . ': ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to set password. Please try again or contact support.');
        }
    }
}