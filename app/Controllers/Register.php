<?php

namespace App\Controllers;

use App\Models\MerchantModel;
use App\Models\SubscriptionModel;
use App\Helpers\EmailService;

class Register extends BaseController
{
    public function index()
    {
        return view('auth/register');
    }

    public function createAccount()
    {
        $rules = [
            'name' => 'required',
            'company_name' => 'required',
            'email' => 'required|valid_email|is_unique[merchants.email]',
            'password' => 'required|min_length[8]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $merchantModel = new MerchantModel();
        $merchantData = [
            'name' => $this->request->getPost('name'),
            'company_name' => $this->request->getPost('company_name'),
            'email' => $this->request->getPost('email'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'verification_status' => 'pending', // Default status
            'is_visible' => false, // Not visible until approved
        ];

        $merchantId = $merchantModel->insert($merchantData);

        // Send welcome email and admin notification
        try {
            $emailService = new EmailService();

            $newMerchantData = [
                'email' => $merchantData['email'],
                'business_name' => $merchantData['company_name'],
                'contact_person' => $merchantData['name']
            ];

            // Send welcome email to merchant
            $emailService->sendMerchantWelcomeEmail($newMerchantData);

            // Send notification to admin
            $adminNotificationData = [
                'business_name' => $merchantData['company_name'],
                'contact_person' => $merchantData['name'],
                'email' => $merchantData['email'],
                'phone' => $this->request->getPost('phone') ?? 'Not provided',
                'created_at' => date('Y-m-d H:i:s')
            ];
            $emailService->sendAdminNewMerchantNotification($adminNotificationData);

        } catch (\Exception $e) {
            log_message('error', 'Failed to send merchant registration emails: ' . $e->getMessage());
        }

        // Store merchant ID in session to proceed to package selection
        session()->set('new_merchant_id', $merchantId);

        return redirect()->to('register/packages');
    }

    public function packages()
    {
        if (!session()->has('new_merchant_id')) {
            return redirect()->to('register');
        }
        return view('auth/packages');
    }

    public function selectPlan($plan)
    {
        if (!session()->has('new_merchant_id')) {
            return redirect()->to('register');
        }

        $merchantId = session('new_merchant_id');
        $subscriptionModel = new SubscriptionModel();

        $subscriptionData = [
            'merchant_id' => $merchantId,
            'plan_type' => $plan,
            'status' => 'active',
        ];

        if ($plan === 'free') {
            $subscriptionData['trial_ends_at'] = date('Y-m-d H:i:s', strtotime('+30 days'));
        } else {
            // For paid plans, redirect to a payment page.
            // For now, we'll just create an 'inactive' subscription
            // until payment is confirmed.
            $subscriptionData['status'] = 'new';
            $subscriptionModel->insert($subscriptionData);
            return redirect()->to('payment/process'); // Placeholder for payment page
        }

        $subscriptionModel->insert($subscriptionData);

        // Log the user in and redirect to the dashboard
        $merchantModel = new MerchantModel();
        $merchant = $merchantModel->asArray()->find($merchantId);
        session()->set('isLoggedIn', true);
        session()->set('merchant_id', $merchant['id']);
        session()->set('merchant_name', $merchant['name']);

        session()->remove('new_merchant_id');

        return redirect()->to('merchant/dashboard');
    }
}
