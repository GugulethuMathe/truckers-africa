<?php

namespace App\Controllers;

use App\Models\MerchantLocationModel;
use App\Models\MerchantModel;
use App\Models\PlanLimitationModel;
use CodeIgniter\Controller;

class MerchantLocations extends Controller
{
    protected $locationModel;
    protected $merchantModel;
    protected $planLimitModel;

    public function __construct()
    {
        $this->locationModel = new MerchantLocationModel();
        $this->merchantModel = new MerchantModel();
        $this->planLimitModel = new PlanLimitationModel();
    }

    /**
     * Display all locations for merchant
     */
    public function index()
    {
        $merchantId = session()->get('user_id');

        if (!$merchantId) {
            return redirect()->to('/login')->with('error', 'Please log in to continue');
        }

        $locations = $this->locationModel->getLocationsByMerchant($merchantId, false);
        $canAddCheck = $this->locationModel->canAddLocation($merchantId);
        $usageStats = $this->planLimitModel->getMerchantUsageStats($merchantId);

        $data = [
            'page_title' => 'Business Locations',
            'locations' => $locations,
            'can_add_location' => $canAddCheck['can_add'],
            'location_limit_message' => $canAddCheck['message'],
            'is_max_plan' => $canAddCheck['is_max_plan'] ?? false,
            'usage_stats' => $usageStats
        ];

        return view('merchant/locations/index', $data);
    }

    /**
     * Show form to add new location
     */
    public function create()
    {
        $merchantId = session()->get('user_id');

        if (!$merchantId) {
            return redirect()->to('/login')->with('error', 'Please log in to continue');
        }

        // Check if merchant can add more locations
        $canAddCheck = $this->locationModel->canAddLocation($merchantId);

        if (!$canAddCheck['can_add']) {
            return redirect()->to('merchant/locations')
                ->with('error', $canAddCheck['message']);
        }

        $data = [
            'page_title' => 'Add New Location',
            'merchant' => $this->merchantModel->find($merchantId),
            'geoapify_api_key' => getenv('GEOAPIFY_API_KEY')
        ];

        return view('merchant/locations/create', $data);
    }

    /**
     * Store new location
     */
    public function store()
    {
        $merchantId = session()->get('user_id');

        if (!$merchantId) {
            return redirect()->to('/login')->with('error', 'Please log in to continue');
        }

        // Check if merchant can add more locations
        $canAddCheck = $this->locationModel->canAddLocation($merchantId);

        if (!$canAddCheck['can_add']) {
            return redirect()->to('merchant/locations')
                ->with('error', $canAddCheck['message']);
        }

        $rules = [
            'location_name' => 'required|min_length[3]|max_length[255]',
            'physical_address' => 'required|min_length[10]',
            'contact_number' => 'required|min_length[8]|max_length[50]',
            'whatsapp_number' => 'permit_empty|min_length[8]|max_length[50]',
            'email' => 'permit_empty|valid_email',
            'latitude' => 'permit_empty|decimal',
            'longitude' => 'permit_empty|decimal',
            'address_selected' => 'permit_empty|in_list[1]',
            'manager_full_name' => 'required|min_length[2]|max_length[255]',
            'manager_email' => 'required|valid_email|is_unique[branch_users.email]',
            'manager_phone' => 'required|min_length[8]|max_length[50]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // Additional validation: ensure address was selected from dropdown
        if (!empty($this->request->getPost('physical_address'))) {
            if (empty($this->request->getPost('address_selected')) || $this->request->getPost('address_selected') !== '1') {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Please select an address from the dropdown suggestions.');
            }
        }

        // Prepare location data
        $locationData = [
            'merchant_id' => $merchantId,
            'location_name' => $this->request->getPost('location_name'),
            'physical_address' => $this->request->getPost('physical_address'),
            'contact_number' => $this->request->getPost('contact_number'),
            'whatsapp_number' => $this->request->getPost('whatsapp_number'),
            'email' => $this->request->getPost('email'),
            'latitude' => $this->request->getPost('latitude'),
            'longitude' => $this->request->getPost('longitude'),
            'operating_hours' => $this->request->getPost('operating_hours'),
            'is_primary' => 0,
            'is_active' => 1
        ];

        // If this is the first location, make it primary
        $existingCount = $this->locationModel->getActiveLocationsCount($merchantId);
        if ($existingCount === 0) {
            $locationData['is_primary'] = 1;
        }

        // Start database transaction
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Insert location
            $locationId = $this->locationModel->insert($locationData);

            if (!$locationId) {
                throw new \Exception('Failed to create location record');
            }

            // Create branch user account
            $branchUserModel = new \App\Models\BranchUserModel();

            // Generate password setup token
            $passwordToken = bin2hex(random_bytes(32));
            $tokenExpires = date('Y-m-d H:i:s', strtotime('+48 hours'));

            // Generate a temporary random password (will be replaced when user sets their own)
            $tempPassword = bin2hex(random_bytes(16));

            $branchUserData = [
                'location_id' => $locationId,
                'merchant_id' => $merchantId,
                'email' => $this->request->getPost('manager_email'),
                'full_name' => $this->request->getPost('manager_full_name'),
                'phone_number' => $this->request->getPost('manager_phone'),
                'password_hash' => $tempPassword, // Temporary password, will be replaced via setup link
                'password_reset_token' => $passwordToken,
                'password_reset_expires' => $tokenExpires,
                'is_active' => 1,
                'created_by' => $merchantId // Merchant ID who created this branch user
            ];

            // Insert branch user - password will be set via email link
            $branchUserId = $branchUserModel->insert($branchUserData);

            if (!$branchUserId) {
                // Log validation errors if any
                $errors = $branchUserModel->errors();
                if ($errors) {
                    log_message('error', 'Branch user validation errors: ' . json_encode($errors));
                    throw new \Exception('Failed to create branch user account: ' . json_encode($errors));
                }
                throw new \Exception('Failed to create branch user account');
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            // Update merchant's location count
            $this->locationModel->updateMerchantLocationCount($merchantId);

            // Get the created records for email
            $location = $this->locationModel->find($locationId);
            $branchUser = $branchUserModel->find($branchUserId);
            $merchant = $this->merchantModel->find($merchantId);

            // Send account setup email
            $emailSent = false;
            try {
                $emailSent = $this->sendBranchAccountSetupEmail($branchUser, $location, $merchant, $passwordToken);
                if ($emailSent) {
                    log_message('info', 'Branch account setup email sent successfully to: ' . $branchUser['email']);
                } else {
                    log_message('warning', 'Branch account setup email failed to send to: ' . $branchUser['email']);
                }
            } catch (\Exception $e) {
                log_message('error', 'Exception while sending branch account setup email: ' . $e->getMessage());
                // Don't fail the creation if email fails
            }

            $successMessage = 'Branch location and manager account created successfully!';
            if ($emailSent) {
                $successMessage .= ' The branch manager will receive an email to set up their password.';
            } else {
                $successMessage .= ' However, the setup email could not be sent. Please contact the branch manager manually.';
            }

            return redirect()->to('merchant/locations')
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Failed to create branch location: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to add location: ' . $e->getMessage());
        }
    }

    /**
     * Show form to edit location
     */
    public function edit($locationId)
    {
        $merchantId = session()->get('user_id');

        if (!$merchantId) {
            return redirect()->to('/login')->with('error', 'Please log in to continue');
        }

        $location = $this->locationModel->where('id', $locationId)
            ->where('merchant_id', $merchantId)
            ->first();

        if (!$location) {
            return redirect()->to('merchant/locations')
                ->with('error', 'Location not found');
        }

        // Get listings for this location
        $listingModel = new \App\Models\MerchantListingModel();
        $listings = $listingModel->findByLocationId($locationId);
        $listingsCount = count($listings);

        $data = [
            'page_title' => 'Edit Location',
            'location' => $location,
            'listings' => $listings,
            'listingsCount' => $listingsCount,
            'geoapify_api_key' => getenv('GEOAPIFY_API_KEY')
        ];

        return view('merchant/locations/edit', $data);
    }

    /**
     * Update location
     */
    public function update($locationId)
    {
        $merchantId = session()->get('user_id');

        if (!$merchantId) {
            return redirect()->to('/login')->with('error', 'Please log in to continue');
        }

        $location = $this->locationModel->where('id', $locationId)
            ->where('merchant_id', $merchantId)
            ->first();

        if (!$location) {
            return redirect()->to('merchant/locations')
                ->with('error', 'Location not found');
        }

        $rules = [
            'location_name' => 'required|min_length[3]|max_length[255]',
            'physical_address' => 'required|min_length[10]',
            'contact_number' => 'required|min_length[8]|max_length[50]',
            'whatsapp_number' => 'permit_empty|min_length[8]|max_length[50]',
            'email' => 'permit_empty|valid_email',
            'latitude' => 'permit_empty|decimal',
            'longitude' => 'permit_empty|decimal'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $updateData = [
            'location_name' => $this->request->getPost('location_name'),
            'physical_address' => $this->request->getPost('physical_address'),
            'contact_number' => $this->request->getPost('contact_number'),
            'whatsapp_number' => $this->request->getPost('whatsapp_number'),
            'email' => $this->request->getPost('email'),
            'latitude' => $this->request->getPost('latitude'),
            'longitude' => $this->request->getPost('longitude'),
            'operating_hours' => $this->request->getPost('operating_hours')
        ];

        if ($this->locationModel->update($locationId, $updateData)) {
            return redirect()->to('merchant/locations')
                ->with('success', 'Location updated successfully!');
        } else {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update location. Please try again.');
        }
    }

    /**
     * Set location as primary
     */
    public function setPrimary($locationId)
    {
        $merchantId = session()->get('user_id');

        if (!$merchantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please log in to continue'
            ]);
        }

        $location = $this->locationModel->where('id', $locationId)
            ->where('merchant_id', $merchantId)
            ->first();

        if (!$location) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Location not found'
            ]);
        }

        if ($this->locationModel->setPrimaryLocation($locationId, $merchantId)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Primary location updated successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update primary location'
            ]);
        }
    }

    /**
     * Deactivate location
     */
    public function deactivate($locationId)
    {
        $merchantId = session()->get('user_id');

        if (!$merchantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please log in to continue'
            ]);
        }

        if ($this->locationModel->deactivateLocation($locationId, $merchantId)) {
            // Update merchant's location count
            $this->locationModel->updateMerchantLocationCount($merchantId);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Location deactivated successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cannot deactivate this location. It may be your only active location or primary location.'
            ]);
        }
    }

    /**
     * Activate location
     */
    public function activate($locationId)
    {
        $merchantId = session()->get('user_id');

        if (!$merchantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please log in to continue'
            ]);
        }

        if ($this->locationModel->activateLocation($locationId, $merchantId)) {
            // Update merchant's location count
            $this->locationModel->updateMerchantLocationCount($merchantId);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Location activated successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cannot activate location. You may have reached your plan limit.'
            ]);
        }
    }

    /**
     * Delete location (soft delete)
     */
    public function delete($locationId)
    {
        $merchantId = session()->get('user_id');

        if (!$merchantId) {
            return redirect()->to('/login')->with('error', 'Please log in to continue');
        }

        // Use deactivate instead of delete to preserve data
        if ($this->locationModel->deactivateLocation($locationId, $merchantId)) {
            // Update merchant's location count
            $this->locationModel->updateMerchantLocationCount($merchantId);

            return redirect()->to('merchant/locations')
                ->with('success', 'Location removed successfully');
        } else {
            return redirect()->to('merchant/locations')
                ->with('error', 'Cannot remove this location. It may be your only active location.');
        }
    }

    /**
     * Get location details (AJAX)
     */
    public function details($locationId)
    {
        $merchantId = session()->get('user_id');

        if (!$merchantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please log in to continue'
            ]);
        }

        $location = $this->locationModel->getLocationWithStats($locationId);

        if (!$location || $location['merchant_id'] != $merchantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Location not found'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'location' => $location
        ]);
    }

    /**
     * Get locations for listing creation (AJAX)
     */
    public function getForListing()
    {
        $merchantId = session()->get('user_id');

        if (!$merchantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please log in to continue'
            ]);
        }

        $locations = $this->locationModel->getLocationsByMerchant($merchantId, true);

        return $this->response->setJSON([
            'success' => true,
            'locations' => $locations
        ]);
    }

    /**
     * Send account setup email to branch manager with password setup link
     */
    private function sendBranchAccountSetupEmail(array $branchUser, array $location, array $merchant, string $passwordToken): bool
    {
        $emailService = \Config\Services::email();
        $subject = 'Complete Your Branch Manager Account Setup - ' . $merchant['business_name'];

        $setupUrl = site_url("branch/setup-password?token={$passwordToken}&email=" . urlencode($branchUser['email']));

        $message = view('emails/branch_account_setup', [
            'branchUser' => $branchUser,
            'location' => $location,
            'merchant' => $merchant,
            'setup_url' => $setupUrl,
            'token_expires' => '48 hours'
        ]);

        $emailService->setTo($branchUser['email']);
        $emailService->setSubject($subject);
        $emailService->setMessage($message);

        return $emailService->send();
    }
}
