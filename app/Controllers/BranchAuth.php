<?php

namespace App\Controllers;

use App\Models\BranchUserModel;
use CodeIgniter\Controller;

class BranchAuth extends Controller
{
    /**
     * Display branch login page
     */
    public function login()
    {
        // If already logged in as branch user, redirect to dashboard
        if (session()->get('is_logged_in') && session()->get('user_type') === 'branch') {
            return redirect()->to('branch/dashboard');
        }

        $data = [
            'page_title' => 'Branch Login'
        ];

        return view('branch/auth/login', $data);
    }

    /**
     * Process branch login
     */
    public function processLogin()
    {
        $branchUserModel = new BranchUserModel();

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        // Validate input
        if (empty($email) || empty($password)) {
            return redirect()->back()->with('error', 'Please provide email and password');
        }

        // Verify credentials
        $branchUser = $branchUserModel->verifyLogin($email, $password);

        if (!$branchUser) {
            return redirect()->back()->with('error', 'Invalid email or password');
        }

        // Get full details with location and merchant info
        $branchUserDetails = $branchUserModel->getBranchUserWithDetails($branchUser['id']);

        // Check merchant subscription status
        $subscriptionModel = new \App\Models\SubscriptionModel();
        $merchantSubscription = $subscriptionModel
            ->where('merchant_id', $branchUser['merchant_id'])
            ->whereIn('status', ['active', 'trial'])
            ->orderBy('updated_at', 'DESC')
            ->first();

        if (!$merchantSubscription) {
            // Merchant has no active subscription
            return redirect()->back()->with('error', 'Access denied. Your merchant account does not have an active subscription. Please contact your business owner to renew the subscription.');
        }

        // Set session data
        session()->set([
            'user_id' => $branchUser['id'],
            'branch_user_id' => $branchUser['id'],
            'location_id' => $branchUser['location_id'],
            'merchant_id' => $branchUser['merchant_id'],
            'user_type' => 'branch',
            'email' => $branchUser['email'],
            'full_name' => $branchUser['full_name'],
            'location_name' => $branchUserDetails['location_name'] ?? '',
            'business_name' => $branchUserDetails['business_name'] ?? '',
            'is_logged_in' => true
        ]);

        return redirect()->to('branch/dashboard')->with('success', 'Welcome back, ' . $branchUser['full_name']);
    }

    /**
     * Logout branch user
     */
    public function logout()
    {
        session()->destroy();
        return redirect()->to('branch/login')->with('success', 'You have been logged out successfully');
    }

    /**
     * Display forgot password page
     */
    public function forgotPassword()
    {
        $data = [
            'page_title' => 'Forgot Password'
        ];

        return view('branch/auth/forgot_password', $data);
    }

    /**
     * Process forgot password request
     */
    public function processForgotPassword()
    {
        $branchUserModel = new BranchUserModel();
        $userEmail = $this->request->getPost('email');

        if (empty($userEmail)) {
            return redirect()->back()->with('error', 'Please provide your email address');
        }

        $resetData = $branchUserModel->generateResetToken($userEmail);

        if (!$resetData) {
            // Don't reveal if email exists or not for security
            return redirect()->back()->with('success', 'If your email is registered, you will receive a password reset link');
        }

        // Get user details for email
        $user = $branchUserModel->find($resetData['user_id']);
        
        // Send password reset email
        $resetLink = base_url('branch/reset-password/' . $resetData['token']);

        $email = \Config\Services::email();
        $email->setFrom(getenv('email.fromEmail') ?: 'noreply@truckersafrica.com', getenv('email.fromName') ?: 'Truckers Africa');
        $email->setTo($userEmail);
        $email->setSubject('Password Reset Request - Branch Account');

        $message = view('emails/branch_password_reset', [
            'full_name' => $user['full_name'],
            'reset_link' => $resetLink
        ]);

        $email->setMessage($message);

        if ($email->send()) {
            log_message('info', 'Password reset email sent to branch user: ' . $userEmail);
        } else {
            log_message('error', 'Failed to send password reset email to branch user: ' . $userEmail . ' - ' . $email->printDebugger());
        }
        
        return redirect()->back()->with('success', 'If your email is registered, you will receive a password reset link');
    }

    /**
     * Display reset password page
     */
    public function resetPassword($token = null)
    {
        if (!$token) {
            return redirect()->to('branch/login')->with('error', 'Invalid reset token');
        }

        $branchUserModel = new BranchUserModel();
        $user = $branchUserModel->verifyResetToken($token);

        if (!$user) {
            return redirect()->to('branch/login')->with('error', 'Invalid or expired reset token');
        }

        $data = [
            'page_title' => 'Reset Password',
            'token' => $token
        ];

        return view('branch/auth/reset_password', $data);
    }

    /**
     * Process password reset
     */
    public function processResetPassword()
    {
        $branchUserModel = new BranchUserModel();
        
        $token = $this->request->getPost('token');
        $password = $this->request->getPost('password');
        $confirmPassword = $this->request->getPost('confirm_password');

        // Validate
        if (empty($password) || empty($confirmPassword)) {
            return redirect()->back()->with('error', 'Please provide both password fields');
        }

        if ($password !== $confirmPassword) {
            return redirect()->back()->with('error', 'Passwords do not match');
        }

        if (strlen($password) < 8) {
            return redirect()->back()->with('error', 'Password must be at least 8 characters');
        }

        // Reset password
        $success = $branchUserModel->resetPassword($token, $password);

        if (!$success) {
            return redirect()->to('branch/login')->with('error', 'Invalid or expired reset token');
        }

        return redirect()->to('branch/login')->with('success', 'Password reset successfully. Please login with your new password');
    }

    /**
     * Show password setup form for newly created branch users
     */
    public function setupPassword()
    {
        $token = $this->request->getGet('token');
        $email = $this->request->getGet('email');

        if (!$token || !$email) {
            return redirect()->to('branch/login')->with('error', 'Invalid password setup link.');
        }

        // Validate token
        $branchUserModel = new BranchUserModel();
        $branchUser = $branchUserModel->where('email', $email)
                                      ->where('password_reset_token', $token)
                                      ->where('password_reset_expires >', date('Y-m-d H:i:s'))
                                      ->first();

        if (!$branchUser) {
            return redirect()->to('branch/login')->with('error', 'Invalid or expired password setup link. Please contact your business owner.');
        }

        // Get location and merchant details
        $branchUserDetails = $branchUserModel->getBranchUserWithDetails($branchUser['id']);

        return view('branch/auth/setup_password', [
            'token' => $token,
            'email' => $email,
            'branchUser' => $branchUserDetails
        ]);
    }

    /**
     * Process password setup for newly created branch users
     */
    public function processSetupPassword()
    {
        if (!$this->request->is('post')) {
            return redirect()->to('branch/login');
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
        $branchUserModel = new BranchUserModel();
        $branchUser = $branchUserModel->where('email', $email)
                                      ->where('password_reset_token', $token)
                                      ->where('password_reset_expires >', date('Y-m-d H:i:s'))
                                      ->first();

        if (!$branchUser) {
            return redirect()->to('branch/login')->with('error', 'Invalid or expired password setup link. Please contact your business owner.');
        }

        // Update password and clear token
        $updateData = [
            'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
            'password_reset_token' => null,
            'password_reset_expires' => null
        ];

        if ($branchUserModel->update($branchUser['id'], $updateData)) {
            log_message('info', 'Branch user password set successfully for: ' . $email);
            return redirect()->to('branch/login')->with('success', 'Password set successfully! You can now log in with your new password.');
        } else {
            log_message('error', 'Failed to set password for branch user: ' . $email);
            return redirect()->back()->with('error', 'Failed to set password. Please try again or contact support.');
        }
    }
}

