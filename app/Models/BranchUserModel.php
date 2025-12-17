<?php

namespace App\Models;

use CodeIgniter\Model;

class BranchUserModel extends Model
{
    protected $table = 'branch_users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'location_id',
        'merchant_id',
        'email',
        'password_hash',
        'full_name',
        'phone_number',
        'is_active',
        'last_login',
        'password_reset_token',
        'password_reset_expires',
        'created_by'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'location_id' => 'required|integer|is_unique[branch_users.location_id,id,{id}]',
        'merchant_id' => 'required|integer',
        'email' => 'required|valid_email|is_unique[branch_users.email,id,{id}]',
        'password_hash' => 'required|min_length[8]',
        'full_name' => 'permit_empty|max_length[255]',
        'phone_number' => 'permit_empty|max_length[50]',
        'is_active' => 'permit_empty|in_list[0,1]'
    ];

    protected $validationMessages = [
        'email' => [
            'required' => 'Email is required',
            'valid_email' => 'Please provide a valid email address',
            'is_unique' => 'This email is already registered'
        ],
        'location_id' => [
            'is_unique' => 'This location already has a branch user assigned'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    /**
     * Hash password before saving
     */
    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password_hash']) && !empty($data['data']['password_hash'])) {
            // Only hash if it's not already hashed (doesn't start with $2y$)
            if (strpos($data['data']['password_hash'], '$2y$') !== 0) {
                $data['data']['password_hash'] = password_hash($data['data']['password_hash'], PASSWORD_DEFAULT);
            }
        }
        return $data;
    }

    /**
     * Find branch user by email
     */
    public function findByEmail(string $email): ?array
    {
        return $this->where('email', $email)->first();
    }

    /**
     * Find branch user by location ID
     */
    public function findByLocationId(int $locationId): ?array
    {
        return $this->where('location_id', $locationId)->first();
    }

    /**
     * Get branch user with location and merchant details
     */
    public function getBranchUserWithDetails(int $branchUserId): ?array
    {
        return $this->select('branch_users.*, merchant_locations.location_name, merchant_locations.physical_address, merchant_locations.contact_number, merchant_locations.whatsapp_number, merchant_locations.email as location_email, merchant_locations.operating_hours, merchants.business_name')
                    ->join('merchant_locations', 'merchant_locations.id = branch_users.location_id')
                    ->join('merchants', 'merchants.id = branch_users.merchant_id')
                    ->where('branch_users.id', $branchUserId)
                    ->first();
    }

    /**
     * Get all branch users for a merchant
     */
    public function getBranchUsersByMerchant(int $merchantId): array
    {
        return $this->select('branch_users.*, merchant_locations.location_name, merchant_locations.is_primary')
                    ->join('merchant_locations', 'merchant_locations.id = branch_users.location_id')
                    ->where('branch_users.merchant_id', $merchantId)
                    ->orderBy('merchant_locations.is_primary', 'DESC')
                    ->orderBy('branch_users.created_at', 'ASC')
                    ->findAll();
    }

    /**
     * Verify login credentials
     */
    public function verifyLogin(string $email, string $password): ?array
    {
        $user = $this->where('email', $email)
                     ->where('is_active', 1)
                     ->first();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Update last login
            $this->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);
            return $user;
        }

        return null;
    }

    /**
     * Check if location has a branch user
     */
    public function locationHasUser(int $locationId): bool
    {
        return $this->where('location_id', $locationId)->countAllResults() > 0;
    }

    /**
     * Activate/Deactivate branch user
     */
    public function toggleActive(int $branchUserId): bool
    {
        $user = $this->find($branchUserId);
        if (!$user) {
            return false;
        }

        return $this->update($branchUserId, ['is_active' => $user['is_active'] ? 0 : 1]);
    }

    /**
     * Update password
     */
    public function updatePassword(int $branchUserId, string $newPassword): bool
    {
        return $this->update($branchUserId, [
            'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT)
        ]);
    }

    /**
     * Deactivate all branch users for a merchant
     */
    public function deactivateAllForMerchant(int $merchantId): bool
    {
        return $this->where('merchant_id', $merchantId)
                    ->set(['is_active' => 0])
                    ->update();
    }

    /**
     * Get inactive branch count for a merchant
     */
    public function getInactiveBranchCount(int $merchantId): int
    {
        return $this->where('merchant_id', $merchantId)
                    ->where('is_active', 0)
                    ->countAllResults();
    }

    /**
     * Generate password reset token
     */
    public function generateResetToken(string $email): ?array
    {
        $user = $this->findByEmail($email);
        if (!$user) {
            return null;
        }

        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $this->update($user['id'], [
            'password_reset_token' => $token,
            'password_reset_expires' => $expires
        ]);

        return [
            'user_id' => $user['id'],
            'token' => $token,
            'expires' => $expires
        ];
    }

    /**
     * Verify reset token
     */
    public function verifyResetToken(string $token): ?array
    {
        return $this->where('password_reset_token', $token)
                    ->where('password_reset_expires >', date('Y-m-d H:i:s'))
                    ->first();
    }

    /**
     * Reset password using token
     */
    public function resetPassword(string $token, string $newPassword): bool
    {
        $user = $this->verifyResetToken($token);
        if (!$user) {
            return false;
        }

        return $this->update($user['id'], [
            'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
            'password_reset_token' => null,
            'password_reset_expires' => null
        ]);
    }
}

