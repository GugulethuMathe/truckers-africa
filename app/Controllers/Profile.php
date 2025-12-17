<?php

namespace App\Controllers;

use App\Models\MerchantModel;
use App\Models\ServiceCategoryModel;
use App\Models\TruckDriverModel; // Keep for driver profile later
use CodeIgniter\Controller;

class Profile extends Controller
{
    /**
     * Shows the form for a merchant to edit their business profile.
     */
    public function editMerchant()
    {
        $merchantModel = new MerchantModel();
        $serviceCategoryModel = new ServiceCategoryModel();
        $merchantId = session()->get('merchant_id');

        $data = [
            'merchant' => $merchantModel->find($merchantId),
            'service_categories' => $serviceCategoryModel->getAllCategories(),
            'page_title' => 'My Business Profile',
            'geoapify_api_key' => getenv('GEOAPIFY_API_KEY')
        ];

        return view('merchant/profile', $data);
    }

    /**
     * Processes the updates to a merchant's business profile.
     */
    public function updateMerchant()
    {
        $request = \Config\Services::request();
        $merchantModel = new MerchantModel();
        $merchantId = session()->get('merchant_id');

        $rules = [
            'owner_name' => [
                'label' => 'Owner Name',
                'rules' => 'required|max_length[255]',
                'errors' => [
                    'required' => 'Owner Name is required.',
                    'max_length' => 'Owner Name cannot exceed 255 characters.'
                ]
            ],
            'email' => [
                'label' => 'Email Address',
                'rules' => 'required|valid_email|max_length[255]',
                'errors' => [
                    'required' => 'Email Address is required.',
                    'valid_email' => 'Please enter a valid Email Address.',
                    'max_length' => 'Email Address cannot exceed 255 characters.'
                ]
            ],
            'business_name' => [
                'label' => 'Business Name',
                'rules' => 'required|max_length[255]',
                'errors' => [
                    'required' => 'Business Name is required.',
                    'max_length' => 'Business Name cannot exceed 255 characters.'
                ]
            ],
            'business_contact_number' => [
                'label' => 'Business Contact Number',
                'rules' => 'required|max_length[50]',
                'errors' => [
                    'required' => 'Business Contact Number is required.',
                    'max_length' => 'Business Contact Number cannot exceed 50 characters.'
                ]
            ],
            'physical_address' => [
                'label' => 'Physical Address',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Physical Address is required.'
                ]
            ],
            'main_service' => [
                'label' => 'Main Service',
                'rules' => 'permit_empty|max_length[100]',
                'errors' => [
                    'max_length' => 'Main Service cannot exceed 100 characters.'
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
            'profile_description' => [
                'label' => 'Profile Description',
                'rules' => 'permit_empty|max_length[1000]',
                'errors' => [
                    'max_length' => 'Profile Description cannot exceed 1000 characters.'
                ]
            ],
            'business_description' => [
                'label' => 'Business Description',
                'rules' => 'permit_empty|max_length[5000]',
                'errors' => [
                    'max_length' => 'Business Description cannot exceed 5000 characters.'
                ]
            ],
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'owner_name' => $request->getPost('owner_name'),
            'email' => $request->getPost('email'),
            'business_name' => $request->getPost('business_name'),
            'business_contact_number' => $request->getPost('business_contact_number'),
            'business_whatsapp_number' => $request->getPost('business_whatsapp_number'),
            'physical_address' => $request->getPost('physical_address'),
            'main_service' => $request->getPost('main_service'),
            'profile_description' => $request->getPost('profile_description'),
            'business_description' => $request->getPost('business_description'),
            'is_visible' => $request->getPost('is_visible') ? 1 : 0, // Handle visibility checkbox
        ];

        // Handle latitude and longitude from hidden fields (set by JavaScript)
        $latitude = $request->getPost('latitude');
        $longitude = $request->getPost('longitude');
        if ($latitude && $longitude) {
            $data['latitude'] = $latitude;
            $data['longitude'] = $longitude;
        }

        // Handle business image upload
        $businessImg = $request->getFile('business_image');
        if ($businessImg && $businessImg->isValid() && !$businessImg->hasMoved()) {
            $oldMerchantData = $merchantModel->find($merchantId);
            $oldImagePath = $oldMerchantData['business_image_url'] ?? null;

            $newName = $businessImg->getRandomName();
            // Save to uploads/merchant_profiles (web root, NOT public/)
            $businessImg->move(FCPATH . 'uploads/merchant_profiles', $newName);

            $data['business_image_url'] = 'uploads/merchant_profiles/' . $newName;

            // Delete old image if exists
            if ($oldImagePath && file_exists(FCPATH . $oldImagePath)) {
                @unlink(FCPATH . $oldImagePath);
            }
        }

        // Handle profile image upload
        $profileImg = $request->getFile('profile_image');
        if ($profileImg && $profileImg->isValid() && !$profileImg->hasMoved()) {
            $oldMerchantData = $oldMerchantData ?? $merchantModel->find($merchantId);
            $oldProfileImagePath = $oldMerchantData['profile_image_url'] ?? null;

            $newProfileName = $profileImg->getRandomName();
            // Save to uploads/merchant_profiles (web root, NOT public/)
            $profileImg->move(FCPATH . 'uploads/merchant_profiles', $newProfileName);

            $data['profile_image_url'] = 'uploads/merchant_profiles/' . $newProfileName;

            // Delete old image if exists
            if ($oldProfileImagePath && file_exists(FCPATH . $oldProfileImagePath)) {
                @unlink(FCPATH . $oldProfileImagePath);
            }
        }

        if ($merchantModel->update($merchantId, $data)) {
            // Update session with new business name if changed
            session()->set('business_name', $data['business_name']);

            // Auto-create primary location if this is the first time updating profile with address
            // and merchant has no locations yet
            $locationModel = new \App\Models\MerchantLocationModel();
            $existingLocations = $locationModel->getLocationsByMerchant($merchantId, false);

            if (empty($existingLocations) && !empty($data['physical_address'])) {
                try {
                    $this->autoCreatePrimaryLocation($merchantId, $data);
                } catch (\Exception $e) {
                    log_message('error', 'Failed to auto-create primary location: ' . $e->getMessage());
                    // Don't fail the profile update if location creation fails
                }
            } else {
                // Update primary location address if it exists and address has changed
                try {
                    $this->updatePrimaryLocationAddress($merchantId, $data);
                } catch (\Exception $e) {
                    log_message('error', 'Failed to update primary location address: ' . $e->getMessage());
                    // Don't fail the profile update if location update fails
                }
            }

            // Send notification to admin about profile update
            try {
                $merchant = $merchantModel->find($merchantId);
                $notifier = new \App\Services\NotificationService();
                $notifier->notifyAdminMerchantProfileUpdated($merchant);
            } catch (\Throwable $t) {
                log_message('error', 'Failed to notify admin about merchant profile update: ' . $t->getMessage());
            }

            return redirect()->to('merchant/dashboard')->with('message', 'Profile updated successfully!');
        }

        return redirect()->back()->withInput()->with('error', 'Could not update your profile.');
    }

    /**
     * Auto-create primary location from merchant profile data
     * This is called only the first time a merchant updates their profile with an address
     *
     * @param int $merchantId
     * @param array $merchantData Profile data including address, contact info, etc.
     * @return bool
     * @throws \Exception
     */
    private function autoCreatePrimaryLocation(int $merchantId, array $merchantData): bool
    {
        $locationModel = new \App\Models\MerchantLocationModel();
        $branchUserModel = new \App\Models\BranchUserModel();
        $merchantModel = new \App\Models\MerchantModel();

        $merchant = $merchantModel->find($merchantId);

        if (!$merchant) {
            throw new \Exception('Merchant not found');
        }

        // Prepare location data from merchant profile
        $locationData = [
            'merchant_id' => $merchantId,
            'location_name' => $merchantData['business_name'] . ' - Main Branch',
            'physical_address' => $merchantData['physical_address'],
            'contact_number' => $merchantData['business_contact_number'],
            'whatsapp_number' => $merchantData['business_whatsapp_number'] ?? null,
            'email' => $merchantData['email'] ?? null,
            'latitude' => $merchantData['latitude'] ?? null,
            'longitude' => $merchantData['longitude'] ?? null,
            'operating_hours' => null,
            'is_primary' => 1, // This is the primary location
            'is_active' => 1
        ];

        // Start database transaction
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Insert location
            $locationId = $locationModel->insert($locationData);

            if (!$locationId) {
                throw new \Exception('Failed to create primary location');
            }

            // Create branch user account using merchant's email with a suffix
            // Generate a unique email for the branch user
            $branchEmail = $this->generateBranchUserEmail($merchant['email'], $locationId);

            // Generate password setup token
            $passwordToken = bin2hex(random_bytes(32));
            $tokenExpires = date('Y-m-d H:i:s', strtotime('+7 days')); // 7 days for auto-created accounts

            // Generate a temporary random password
            $tempPassword = bin2hex(random_bytes(16));

            $branchUserData = [
                'location_id' => $locationId,
                'merchant_id' => $merchantId,
                'email' => $branchEmail,
                'full_name' => $merchant['owner_name'],
                'phone_number' => $merchantData['business_contact_number'],
                'password_hash' => $tempPassword,
                'password_reset_token' => $passwordToken,
                'password_reset_expires' => $tokenExpires,
                'is_active' => 1,
                'created_by' => $merchantId
            ];

            // Insert branch user
            $branchUserId = $branchUserModel->insert($branchUserData);

            if (!$branchUserId) {
                throw new \Exception('Failed to create branch user account');
            }

            // Update merchant's location count
            $locationModel->updateMerchantLocationCount($merchantId);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            log_message('info', "Auto-created primary location (ID: {$locationId}) for merchant {$merchantId}");

            // Optionally send email notification to merchant about the auto-created branch
            try {
                $this->sendPrimaryLocationCreatedEmail($merchant, $locationData, $branchEmail, $passwordToken);
            } catch (\Exception $e) {
                log_message('warning', 'Failed to send primary location email: ' . $e->getMessage());
                // Don't fail if email fails
            }

            return true;

        } catch (\Exception $e) {
            $db->transRollback();
            throw $e;
        }
    }

    /**
     * Generate a unique email for branch user based on merchant email
     *
     * @param string $merchantEmail
     * @param int $locationId
     * @return string
     */
    private function generateBranchUserEmail(string $merchantEmail, int $locationId): string
    {
        // Split email into local and domain parts
        $parts = explode('@', $merchantEmail);
        $localPart = $parts[0];
        $domain = $parts[1] ?? 'example.com';

        // Create branch email: merchant+branch1@domain.com
        return $localPart . '+branch' . $locationId . '@' . $domain;
    }

    /**
     * Send email notification about auto-created primary location
     *
     * @param array $merchant
     * @param array $location
     * @param string $branchEmail
     * @param string $passwordToken
     * @return bool
     */
    private function sendPrimaryLocationCreatedEmail(array $merchant, array $location, string $branchEmail, string $passwordToken): bool
    {
        $emailService = \Config\Services::email();
        $subject = 'Your Primary Business Location Has Been Created - ' . $merchant['business_name'];

        $setupUrl = site_url("branch/setup-password?token={$passwordToken}&email=" . urlencode($branchEmail));

        $message = view('emails/primary_location_created', [
            'merchant' => $merchant,
            'location' => $location,
            'branch_email' => $branchEmail,
            'setup_url' => $setupUrl,
            'token_expires' => '7 days'
        ]);

        $emailService->setTo($merchant['email']);
        $emailService->setSubject($subject);
        $emailService->setMessage($message);

        return $emailService->send();
    }

    /**
     * Update primary location address when merchant profile address is updated
     *
     * @param int $merchantId
     * @param array $merchantData Updated profile data including address, contact info, etc.
     * @return bool
     * @throws \Exception
     */
    private function updatePrimaryLocationAddress(int $merchantId, array $merchantData): bool
    {
        $locationModel = new \App\Models\MerchantLocationModel();

        // Get the primary location
        $primaryLocation = $locationModel->getPrimaryLocation($merchantId);

        if (!$primaryLocation) {
            // If no primary location exists, try to get any active location
            $locations = $locationModel->getLocationsByMerchant($merchantId, true);
            if (empty($locations)) {
                log_message('info', "No locations found for merchant {$merchantId}, skipping location update");
                return false;
            }
            $primaryLocation = $locations[0];
        }

        // Prepare updated location data
        $locationData = [];

        // Update address if provided
        if (!empty($merchantData['physical_address'])) {
            $locationData['physical_address'] = $merchantData['physical_address'];
        }

        // Update coordinates if provided
        if (!empty($merchantData['latitude']) && !empty($merchantData['longitude'])) {
            $locationData['latitude'] = $merchantData['latitude'];
            $locationData['longitude'] = $merchantData['longitude'];
        }

        // Update contact information if provided
        if (!empty($merchantData['business_contact_number'])) {
            $locationData['contact_number'] = $merchantData['business_contact_number'];
        }

        if (!empty($merchantData['business_whatsapp_number'])) {
            $locationData['whatsapp_number'] = $merchantData['business_whatsapp_number'];
        }

        if (!empty($merchantData['email'])) {
            $locationData['email'] = $merchantData['email'];
        }

        // Update location name if business name changed
        if (!empty($merchantData['business_name'])) {
            $locationData['location_name'] = $merchantData['business_name'] . ' - Main Branch';
        }

        // Only update if there's data to update
        if (empty($locationData)) {
            return false;
        }

        // Update the location
        $updated = $locationModel->update($primaryLocation['id'], $locationData);

        if ($updated) {
            log_message('info', "Updated primary location (ID: {$primaryLocation['id']}) address for merchant {$merchantId}");
            return true;
        }

        return false;
    }

    /**
     * Shows the form for a driver to edit their profile.
     */
    public function editDriver()
    {
        $driverModel = new TruckDriverModel();
        $driverId = session()->get('user_id');

        if (!$driverId) {
            return redirect()->to('login')->with('error', 'Please log in to access your profile.');
        }

        $driver = $driverModel->find($driverId);
        
        if (!$driver) {
            return redirect()->to('dashboard/driver')->with('error', 'Driver profile not found.');
        }

        $data = [
            'driver' => $driver,
            'page_title' => 'My Profile'
        ];

        return view('driver/profile', $data);
    }

    /**
     * Processes the updates to a driver's profile.
     */
    public function updateDriver()
    {
        $request = \Config\Services::request();
        $driverModel = new TruckDriverModel();
        $driverId = session()->get('user_id');
        
        if (!$driverId) {
            return redirect()->to('auth/login')->with('error', 'Please log in to update your profile.');
        }

        $rules = [
            'name' => [
                'label' => 'First Name',
                'rules' => 'required|max_length[255]',
                'errors' => [
                    'required' => 'First Name is required.',
                    'max_length' => 'First Name cannot exceed 255 characters.'
                ]
            ],
            'surname' => [
                'label' => 'Surname',
                'rules' => 'required|max_length[255]',
                'errors' => [
                    'required' => 'Surname is required.',
                    'max_length' => 'Surname cannot exceed 255 characters.'
                ]
            ],
            'email' => [
                'label' => 'Email Address',
                'rules' => 'required|valid_email|max_length[255]',
                'errors' => [
                    'required' => 'Email Address is required.',
                    'valid_email' => 'Please enter a valid Email Address.',
                    'max_length' => 'Email Address cannot exceed 255 characters.'
                ]
            ],
            'contact_number' => [
                'label' => 'Contact Number',
                'rules' => 'required|max_length[50]',
                'errors' => [
                    'required' => 'Contact Number is required.',
                    'max_length' => 'Contact Number cannot exceed 50 characters.'
                ]
            ],
            'whatsapp_number' => [
                'label' => 'WhatsApp Number',
                'rules' => 'permit_empty|max_length[50]',
                'errors' => [
                    'max_length' => 'WhatsApp Number cannot exceed 50 characters.'
                ]
            ],
            'country_of_residence' => [
                'label' => 'Country of Residence',
                'rules' => 'required|max_length[100]',
                'errors' => [
                    'required' => 'Country of Residence is required.',
                    'max_length' => 'Country of Residence cannot exceed 100 characters.'
                ]
            ],
            'preferred_search_radius_km' => [
                'label' => 'Preferred Search Radius',
                'rules' => 'permit_empty|integer|greater_than[0]',
                'errors' => [
                    'integer' => 'Preferred Search Radius must be a number.',
                    'greater_than' => 'Preferred Search Radius must be greater than 0.'
                ]
            ],
            'vehicle_type' => [
                'label' => 'Vehicle Type',
                'rules' => 'permit_empty|max_length[100]',
                'errors' => [
                    'max_length' => 'Vehicle Type cannot exceed 100 characters.'
                ]
            ],
            'vehicle_registration' => [
                'label' => 'Vehicle Registration',
                'rules' => 'permit_empty|max_length[50]',
                'errors' => [
                    'max_length' => 'Vehicle Registration cannot exceed 50 characters.'
                ]
            ],
            'license_number' => [
                'label' => 'License Number',
                'rules' => 'permit_empty|max_length[100]',
                'errors' => [
                    'max_length' => 'License Number cannot exceed 100 characters.'
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
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name' => $request->getPost('name'),
            'surname' => $request->getPost('surname'),
            'email' => $request->getPost('email'),
            'contact_number' => $request->getPost('contact_number'),
            'whatsapp_number' => $request->getPost('whatsapp_number'),
            'country_of_residence' => $request->getPost('country_of_residence'),
            'preferred_search_radius_km' => $request->getPost('preferred_search_radius_km') ?: 50,
            'vehicle_type' => $request->getPost('vehicle_type'),
            'vehicle_registration' => $request->getPost('vehicle_registration'),
            'license_number' => $request->getPost('license_number'),
        ];

        // Handle profile image upload
        $profileImg = $request->getFile('profile_image');
        if ($profileImg && $profileImg->isValid() && !$profileImg->hasMoved()) {
            $oldDriverData = $driverModel->find($driverId);
            $oldImagePath = $oldDriverData['profile_image_url'] ?? null;

            $newName = $profileImg->getRandomName();
            // Save to uploads/driver_profiles (NOT public/uploads)
            $uploadPath = ROOTPATH . 'uploads/driver_profiles/';

            // Create directory if it doesn't exist
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $profileImg->move($uploadPath, $newName);

            $data['profile_image_url'] = 'uploads/driver_profiles/' . $newName; // Store relative path

            // Delete old image if exists (handle both old and new paths)
            if ($oldImagePath) {
                // Try ROOTPATH first (correct path)
                if (file_exists(ROOTPATH . $oldImagePath)) {
                    @unlink(ROOTPATH . $oldImagePath);
                }
                // Try FCPATH as fallback (old path)
                elseif (file_exists(FCPATH . $oldImagePath)) {
                    @unlink(FCPATH . $oldImagePath);
                }
            }
        }

        if ($driverModel->update($driverId, $data)) {
            return redirect()->to('profile/driver')->with('message', 'Profile updated successfully!');
        }

        return redirect()->back()->withInput()->with('error', 'Could not update your profile.');
    }

    /**
     * Display the change password form for merchants
     */
    public function changePassword()
    {
        // Check if user is logged in as merchant
        if (!session()->has('merchant_id')) {
            return redirect()->to('login')->with('error', 'Please log in to access this page.');
        }

        $data = [
            'page_title' => 'Change Password'
        ];

        return view('merchant/change_password', $data);
    }

    /**
     * Process the password change request for merchants
     */
    public function updatePassword()
    {
        // Check if user is logged in as merchant
        if (!session()->has('merchant_id')) {
            return redirect()->to('login')->with('error', 'Please log in to access this page.');
        }

        $request = \Config\Services::request();
        $userLoginModel = new \App\Models\UserLoginModel();
        $merchantModel = new MerchantModel();
        $merchantId = session()->get('merchant_id');

        // Validation rules
        $rules = [
            'current_password' => 'required',
            'new_password' => [
                'required',
                'min_length[8]',
                'regex_match[/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]/]'
            ],
            'confirm_password' => 'required|matches[new_password]'
        ];

        $messages = [
            'new_password' => [
                'min_length' => 'Password must be at least 8 characters long.',
                'regex_match' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (!@#$%^&*).'
            ],
            'confirm_password' => [
                'matches' => 'Password confirmation does not match the new password.'
            ]
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Get merchant data to find email
        $merchant = $merchantModel->find($merchantId);
        if (!$merchant) {
            log_message('error', 'Merchant not found with ID: ' . $merchantId);
            return redirect()->back()->with('error', 'Merchant not found.');
        }
        $merchantArray = is_array($merchant) ? $merchant : $merchant->toArray();
        
        // Debug: Log merchant data
        log_message('info', 'Merchant data: ' . json_encode($merchantArray));
        log_message('info', 'Looking for login record with merchant ID: ' . $merchantId . ', email: ' . ($merchantArray['email'] ?? 'NO_EMAIL'));
        
        // Check if merchant has email field
        if (!isset($merchantArray['email']) || empty($merchantArray['email'])) {
            log_message('error', 'Merchant does not have email field or email is empty');
            return redirect()->back()->with('error', 'Merchant email not found. Please update your profile first.');
        }

        // Get user login record for this merchant
        $userLogin = $userLoginModel->where('user_type', 'merchant')
                                   ->where('user_id', $merchantId)
                                   ->first();

        // If not found by user_id, try to find by email
        if (!$userLogin) {
            $userLogin = $userLoginModel->where('user_type', 'merchant')
                                       ->where('email', $merchantArray['email'])
                                       ->first();
        }

        // Debug information and fallback
        if (!$userLogin) {
            log_message('error', 'Login record not found for merchant ID: ' . $merchantId . ', email: ' . $merchantArray['email']);
            
            // Check if there are any records for this merchant
            $allUserLogins = $userLoginModel->where('user_type', 'merchant')->findAll();
            log_message('info', 'All merchant login records: ' . json_encode($allUserLogins));
            
            // Try to create a login record if merchant exists but no login record
            // This is a fallback for merchants created before the unified login system
            if (isset($merchantArray['password_hash']) && !empty($merchantArray['password_hash'])) {
                log_message('info', 'Attempting to create login record from merchant data');
                $loginData = [
                    'email' => $merchantArray['email'],
                    'password_hash' => $merchantArray['password_hash'],
                    'user_type' => 'merchant',
                    'user_id' => $merchantId,
                    'is_active' => 1
                ];
                
                $newLoginId = $userLoginModel->insert($loginData);
                if ($newLoginId) {
                    $userLogin = $userLoginModel->find($newLoginId);
                    log_message('info', 'Created new login record with ID: ' . $newLoginId);
                } else {
                    log_message('error', 'Failed to create login record');
                    return redirect()->back()->with('error', 'Could not create login record. Please contact support.');
                }
            } else {
                return redirect()->back()->with('error', 'Login record not found and cannot be created. Please contact support.');
            }
        }

        // Verify current password
        $currentPassword = $request->getPost('current_password');
        if (!password_verify($currentPassword, $userLogin['password_hash'])) {
            return redirect()->back()->withInput()->with('error', 'Current password is incorrect.');
        }

        // Hash new password
        $newPassword = $request->getPost('new_password');
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update password in user_logins table
        $data = [
            'password_hash' => $hashedPassword,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($userLoginModel->update($userLogin['id'], $data)) {
            // Log the password change (optional - for security audit)
            log_message('info', 'Password changed for merchant ID: ' . $merchantId . ', email: ' . $merchantArray['email']);
            
            return redirect()->to('merchant/dashboard')->with('message', 'Password updated successfully!');
        }

        return redirect()->back()->with('error', 'Could not update your password. Please try again.');
    }

    /**
     * Display the change password form for drivers
     */
    public function changePasswordDriver()
    {
        // Check if user is logged in as driver
        if (!session()->has('user_id')) {
            return redirect()->to('login')->with('error', 'Please log in to access this page.');
        }

        $data = [
            'page_title' => 'Change Password'
        ];

        return view('driver/change_password', $data);
    }

    /**
     * Process the password change request for drivers
     */
    public function updatePasswordDriver()
    {
        // Check if user is logged in as driver
        if (!session()->has('user_id')) {
            return redirect()->to('auth/login')->with('error', 'Please log in to access this page.');
        }

        $request = \Config\Services::request();
        $userLoginModel = new \App\Models\UserLoginModel();
        $driverModel = new TruckDriverModel();
        $driverId = session()->get('user_id');

        // Validation rules
        $rules = [
            'current_password' => 'required',
            'new_password' => [
                'required',
                'min_length[8]',
                'regex_match[/^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[!@#$%^&*])[A-Za-z\\d!@#$%^&*]+$/]'
            ],
            'confirm_password' => 'required|matches[new_password]'
        ];

        $messages = [
            'new_password' => [
                'min_length' => 'Password must be at least 8 characters long.',
                'regex_match' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (!@#$%^&*).'
            ],
            'confirm_password' => [
                'matches' => 'Password confirmation does not match.'
            ]
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Get driver info
        $driver = $driverModel->find($driverId);
        if (!$driver) {
            log_message('error', "Driver not found for ID: {$driverId}");
            return redirect()->back()->with('error', 'Driver profile not found.');
        }

        $driverArray = is_array($driver) ? $driver : $driver->toArray();
        log_message('info', "Processing password change for driver ID: {$driverId}, Email: {$driverArray['email']}");

        // Find login record for this driver
        $userLogin = $userLoginModel->where('user_type', 'driver')
                                   ->where('user_id', $driverId)
                                   ->first();

        if (!$userLogin) {
            log_message('warning', "No login record found for driver ID: {$driverId}. Attempting to create one.");
            
            // Fallback: Create login record if it doesn't exist
            $loginData = [
                'email' => $driverArray['email'],
                'password_hash' => password_hash('defaultpassword', PASSWORD_DEFAULT), // Temporary password
                'user_type' => 'driver',
                'user_id' => $driverId,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if ($userLoginModel->insert($loginData)) {
                log_message('info', "Created login record for driver ID: {$driverId}");
                $userLogin = $userLoginModel->where('user_type', 'driver')
                                           ->where('user_id', $driverId)
                                           ->first();
            } else {
                log_message('error', "Failed to create login record for driver ID: {$driverId}");
                return redirect()->back()->with('error', 'Login record not found and cannot be created. Please contact support.');
            }
        }

        // Verify current password
        $currentPassword = $request->getPost('current_password');
        if (!password_verify($currentPassword, $userLogin['password_hash'])) {
            return redirect()->back()->withInput()->with('error', 'Current password is incorrect.');
        }

        // Hash new password
        $newPassword = $request->getPost('new_password');
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update password in user_logins table
        $data = [
            'password_hash' => $hashedPassword,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($userLoginModel->update($userLogin['id'], $data)) {
            log_message('info', "Password updated successfully for driver ID: {$driverId}");
            return redirect()->to('dashboard/driver')->with('message', 'Password updated successfully!');
        }

        log_message('error', "Failed to update password for driver ID: {$driverId}");
        return redirect()->back()->with('error', 'Failed to update password. Please try again.');
    }
}