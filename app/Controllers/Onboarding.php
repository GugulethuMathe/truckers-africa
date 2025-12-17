<?php

namespace App\Controllers;

use App\Models\MerchantModel;
use App\Models\ServiceCategoryModel;
use App\Models\SubscriptionModel;
use App\Models\SubscriptionPlanModel;
use App\Services\NotificationService;
use CodeIgniter\Controller;

class Onboarding extends Controller
{
    protected $merchantModel;
    protected $serviceCategoryModel;
    protected $subscriptionModel;
    protected $planModel;

    public function __construct()
    {
        $this->merchantModel = new MerchantModel();
        $this->serviceCategoryModel = new ServiceCategoryModel();
        $this->subscriptionModel = new SubscriptionModel();
        $this->planModel = new SubscriptionPlanModel();
    }

    /**
     * Check if merchant needs onboarding
     */
    private function needsOnboarding()
    {
        $merchantId = session()->get('merchant_id');
        if (!$merchantId) {
            return false;
        }

        $merchant = $this->merchantModel->find($merchantId);
        return $merchant && $merchant['onboarding_completed'] == 0;
    }

    /**
     * Step 1: Complete business profile
     */
    public function index()
    {
        // Check if user is logged in as merchant
        if (!session()->get('is_logged_in') || session()->get('user_type') !== 'merchant') {
            return redirect()->to('login');
        }

        $merchantId = session()->get('merchant_id');
        $merchant = $this->merchantModel->find($merchantId);

        // If onboarding already completed, redirect to dashboard
        if ($merchant['onboarding_completed'] == 1) {
            return redirect()->to('merchant/dashboard');
        }

        $data = [
            'page_title' => 'Complete Your Business Profile',
            'merchant' => $merchant,
            'service_categories' => $this->serviceCategoryModel->getAllCategories(),
            'geoapify_api_key' => getenv('GEOAPIFY_API_KEY'),
            'step' => 1,
            'total_steps' => 3
        ];

        return view('merchant/onboarding/profile', $data);
    }

    /**
     * Process profile completion
     */
    public function updateProfile()
    {
        if (!$this->request->is('post')) {
            return redirect()->to('merchant/onboarding');
        }

        $merchantId = session()->get('merchant_id');
        if (!$merchantId) {
            return redirect()->to('login');
        }

        $rules = [
            'business_name' => [
                'label' => 'Business Name',
                'rules' => 'required|min_length[3]',
                'errors' => [
                    'required' => 'Business Name is required.',
                    'min_length' => 'Business Name must be at least 3 characters long.'
                ]
            ],
            'business_contact_number' => [
                'label' => 'Business Contact Number',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Business Contact Number is required.'
                ]
            ],
            'physical_address' => [
                'label' => 'Physical Address',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Physical Address is required. Please select an address from the suggestions.'
                ]
            ],
            'main_service' => [
                'label' => 'Main Service Category',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Main Service Category is required.'
                ]
            ],
            'business_description' => [
                'label' => 'Business Description',
                'rules' => 'required|min_length[20]',
                'errors' => [
                    'required' => 'Business Description is required.',
                    'min_length' => 'Business Description must be at least 20 characters long.'
                ]
            ],
            'profile_description' => [
                'label' => 'Additional Information',
                'rules' => 'permit_empty'
            ],
            'latitude' => [
                'label' => 'Latitude',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Please select a valid address from the suggestions to set your location.'
                ]
            ],
            'longitude' => [
                'label' => 'Longitude',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Please select a valid address from the suggestions to set your location.'
                ]
            ],
            'profile_image' => [
                'label' => 'Profile Image',
                'rules' => 'if_exist|uploaded[profile_image]|max_size[profile_image,2048]|is_image[profile_image]|mime_in[profile_image,image/jpg,image/jpeg,image/png,image/gif]',
                'errors' => [
                    'uploaded' => 'Please select a valid Profile Image file.',
                    'max_size' => 'Profile Image must be less than 2MB.',
                    'is_image' => 'Profile Image must be a valid image file.',
                    'mime_in' => 'Profile Image must be JPG, JPEG, PNG, or GIF format.'
                ]
            ],
            'business_image' => [
                'label' => 'Business Image',
                'rules' => 'if_exist|uploaded[business_image]|max_size[business_image,2048]|is_image[business_image]|mime_in[business_image,image/jpg,image/jpeg,image/png,image/gif]',
                'errors' => [
                    'uploaded' => 'Please select a valid Business Image file.',
                    'max_size' => 'Business Image must be less than 2MB.',
                    'is_image' => 'Business Image must be a valid image file.',
                    'mime_in' => 'Business Image must be JPG, JPEG, PNG, or GIF format.'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $updateData = [
            'business_name' => $this->request->getPost('business_name'),
            'business_contact_number' => $this->request->getPost('business_contact_number'),
            'business_whatsapp_number' => $this->request->getPost('business_whatsapp_number'),
            'physical_address' => $this->request->getPost('physical_address'),
            'latitude' => $this->request->getPost('latitude'),
            'longitude' => $this->request->getPost('longitude'),
            'main_service' => $this->request->getPost('main_service'),
            'business_description' => $this->request->getPost('business_description'),
            'profile_description' => $this->request->getPost('profile_description')
        ];

        // Handle profile image upload
        $profileImg = $this->request->getFile('profile_image');
        if ($profileImg && $profileImg->isValid() && !$profileImg->hasMoved()) {
            $merchant = $this->merchantModel->find($merchantId);
            $oldProfileImagePath = $merchant['profile_image_url'] ?? null;

            $newProfileName = $profileImg->getRandomName();
            // Save to uploads/merchant_profiles (web root, NOT public/)
            $profileImg->move(FCPATH . 'uploads/merchant_profiles', $newProfileName);

            $updateData['profile_image_url'] = 'uploads/merchant_profiles/' . $newProfileName;

            // Delete old image if exists
            if ($oldProfileImagePath && file_exists(FCPATH . $oldProfileImagePath)) {
                @unlink(FCPATH . $oldProfileImagePath);
            }
        }

        // Handle business image upload
        $businessImg = $this->request->getFile('business_image');
        if ($businessImg && $businessImg->isValid() && !$businessImg->hasMoved()) {
            $merchant = $merchant ?? $this->merchantModel->find($merchantId);
            $oldImagePath = $merchant['business_image_url'] ?? null;

            $newName = $businessImg->getRandomName();
            // Save to uploads/merchant_profiles (web root, NOT public/)
            $businessImg->move(FCPATH . 'uploads/merchant_profiles', $newName);

            $updateData['business_image_url'] = 'uploads/merchant_profiles/' . $newName;

            // Delete old image if exists
            if ($oldImagePath && file_exists(FCPATH . $oldImagePath)) {
                @unlink(FCPATH . $oldImagePath);
            }
        }

        if ($this->merchantModel->update($merchantId, $updateData)) {
            // Update session with new business name
            session()->set('business_name', $updateData['business_name']);

            // Send notification to admin about profile update
            try {
                $merchant = $this->merchantModel->find($merchantId);
                $notifier = new NotificationService();
                $notifier->notifyAdminMerchantProfileUpdated($merchant);
            } catch (\Throwable $t) {
                log_message('error', 'Failed to notify admin about merchant profile update: ' . $t->getMessage());
            }

            return redirect()->to('merchant/onboarding/plans')->with('message', 'Profile updated! Now choose your subscription plan.');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to update profile. Please try again.');
        }
    }

    /**
     * Step 2: Choose subscription plan
     */
    public function plans()
    {
        // Check if user is logged in as merchant
        if (!session()->get('is_logged_in') || session()->get('user_type') !== 'merchant') {
            return redirect()->to('login');
        }

        $merchantId = session()->get('merchant_id');
        $merchant = $this->merchantModel->find($merchantId);

        // If onboarding already completed, redirect to dashboard
        if ($merchant['onboarding_completed'] == 1) {
            return redirect()->to('merchant/dashboard');
        }

        // Check if profile is complete
        $profileComplete = !empty($merchant['business_name']) && 
                          !empty($merchant['physical_address']) && 
                          !empty($merchant['main_service']) && 
                          !empty($merchant['business_description']);

        if (!$profileComplete) {
            return redirect()->to('merchant/onboarding')->with('error', 'Please complete your profile first.');
        }

        $availablePlans = $this->planModel->getPlansForComparison();

        $data = [
            'page_title' => 'Choose Your Subscription Plan',
            'available_plans' => $availablePlans,
            'step' => 2,
            'total_steps' => 3
        ];

        return view('merchant/onboarding/plans', $data);
    }

    /**
     * Process plan selection
     */
    public function selectPlan()
    {
        if (!$this->request->is('post')) {
            return redirect()->to('merchant/onboarding/plans');
        }

        $merchantId = session()->get('merchant_id');
        if (!$merchantId) {
            return redirect()->to('login');
        }

        $planId = $this->request->getPost('plan_id');
        if (!$planId) {
            return redirect()->back()->with('error', 'Please select a plan.');
        }

        // Get plan details
        $plan = $this->planModel->find($planId);
        if (!$plan) {
            return redirect()->back()->with('error', 'Invalid plan selected.');
        }

        // Check if merchant already has a subscription
        $existingSubscription = $this->subscriptionModel->getCurrentSubscription($merchantId);

        if ($existingSubscription) {
            // Update existing subscription
            $subscriptionData = [
                'plan_id' => $planId,
                'status' => $plan['has_trial'] ? 'trial_pending' : 'new',
                'trial_ends_at' => $plan['has_trial'] ? date('Y-m-d H:i:s', strtotime('+' . $plan['trial_days'] . ' days')) : null,
                'current_period_starts_at' => null, // Will be set after payment
                'current_period_ends_at' => null // Will be set after payment
            ];

            $this->subscriptionModel->update($existingSubscription['id'], $subscriptionData);
        } else {
            // Create new subscription
            $subscriptionData = [
                'merchant_id' => $merchantId,
                'plan_id' => $planId,
                'status' => $plan['has_trial'] ? 'trial_pending' : 'new',
                'trial_ends_at' => $plan['has_trial'] ? date('Y-m-d H:i:s', strtotime('+' . $plan['trial_days'] . ' days')) : null,
                'current_period_starts_at' => null, // Will be set after payment
                'current_period_ends_at' => null // Will be set after payment
            ];

            $this->subscriptionModel->insert($subscriptionData);
        }

        // Always redirect to payment to capture credit card details
        // Even free plans need to provide payment method for future billing
        return redirect()->to('merchant/onboarding/payment')->with('message', 'Almost done! Complete your payment setup to activate your account.');
    }

    /**
     * Step 3: Payment (if no trial)
     */
    public function payment()
    {
        // Check if user is logged in as merchant
        if (!session()->get('is_logged_in') || session()->get('user_type') !== 'merchant') {
            return redirect()->to('login');
        }

        $merchantId = session()->get('merchant_id');
        $merchant = $this->merchantModel->find($merchantId);

        // If onboarding already completed, redirect to dashboard
        if ($merchant['onboarding_completed'] == 1) {
            return redirect()->to('merchant/dashboard');
        }

        // Get current subscription
        $subscription = $this->subscriptionModel->getCurrentSubscription($merchantId);
        if (!$subscription) {
            return redirect()->to('merchant/onboarding/plans')->with('error', 'Please select a plan first.');
        }

        // Get plan details
        $plan = $this->planModel->find($subscription['plan_id']);

        // Format price for display (in USD)
        $plan['formatted_price'] = '$' . number_format($plan['price'], 2);

        $data = [
            'page_title' => 'Complete Payment',
            'subscription' => $subscription,
            'plan' => $plan,
            'step' => 3,
            'total_steps' => 3
        ];

        return view('merchant/onboarding/payment', $data);
    }

    /**
     * Complete onboarding (called after successful payment)
     */
    public function complete()
    {
        $merchantId = session()->get('merchant_id');
        if (!$merchantId) {
            return redirect()->to('login');
        }

        // Mark onboarding as completed
        $this->merchantModel->update($merchantId, ['onboarding_completed' => 1]);

        return redirect()->to('merchant/dashboard')->with('message', 'Welcome to Truckers Africa! Your account is now active.');
    }
}

