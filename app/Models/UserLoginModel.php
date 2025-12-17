<?php

namespace App\Models;

use CodeIgniter\Model;

class UserLoginModel extends Model
{
    protected $table = 'user_logins';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'email',
        'password_hash',
        'google_id',
        'user_type',
        'user_id',
        'email_verified_at',
        'remember_token',
        'last_login_at',
        'login_attempts',
        'locked_until',
        'is_active'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'email' => 'required|valid_email|is_unique[user_logins.email,id,{id}]',
        'user_type' => 'required|in_list[merchant,truck_driver]',
        'user_id' => 'required|integer',
        'is_active' => 'in_list[0,1]'
    ];

    protected $validationMessages = [
        'email' => [
            'required' => 'Email is required',
            'valid_email' => 'Please enter a valid email address',
            'is_unique' => 'This email is already registered'
        ],
        'user_type' => [
            'required' => 'User type is required',
            'in_list' => 'Invalid user type'
        ],
        'user_id' => [
            'required' => 'User ID is required',
            'integer' => 'User ID must be an integer'
        ]
    ];

    protected $skipValidation = false;

    /**
     * Find user by email
     */
    public function findByEmail($email)
    {
        return $this->where('email', $email)->first();
    }

    /**
     * Find user by Google ID
     */
    public function findByGoogleId($googleId)
    {
        return $this->where('google_id', $googleId)->first();
    }

    /**
     * Get user with profile data
     */
    public function getUserWithProfile($loginId)
    {
        $login = $this->find($loginId);
        if (!$login) {
            return null;
        }

        if ($login['user_type'] === 'merchant') {
            $merchantModel = new MerchantModel();
            $profile = $merchantModel->find($login['user_id']);
            return [
                'login' => $login,
                'profile' => $profile,
                'type' => 'merchant'
            ];
        } else {
            $driverModel = new TruckDriverModel();
            $profile = $driverModel->find($login['user_id']);
            return [
                'login' => $login,
                'profile' => $profile,
                'type' => 'truck_driver'
            ];
        }
    }

    /**
     * Update last login time
     */
    public function updateLastLogin($loginId)
    {
        return $this->update($loginId, [
            'last_login_at' => date('Y-m-d H:i:s'),
            'login_attempts' => 0,
            'locked_until' => null
        ]);
    }

    /**
     * Increment login attempts
     */
    public function incrementLoginAttempts($loginId, $lockAfter = 5, $lockDuration = 15)
    {
        $user = $this->find($loginId);
        if (!$user) {
            return false;
        }

        $attempts = $user['login_attempts'] + 1;
        $updateData = ['login_attempts' => $attempts];

        // Lock account after specified attempts
        if ($attempts >= $lockAfter) {
            $updateData['locked_until'] = date('Y-m-d H:i:s', strtotime("+{$lockDuration} minutes"));
        }

        return $this->update($loginId, $updateData);
    }

    /**
     * Check if account is locked
     */
    public function isAccountLocked($loginId)
    {
        $user = $this->find($loginId);
        if (!$user) {
            return false;
        }

        return $user['locked_until'] && strtotime($user['locked_until']) > time();
    }

    /**
     * Unlock account
     */
    public function unlockAccount($loginId)
    {
        return $this->update($loginId, [
            'login_attempts' => 0,
            'locked_until' => null
        ]);
    }

    /**
     * Activate/Deactivate account
     */
    public function setAccountStatus($loginId, $isActive)
    {
        return $this->update($loginId, [
            'is_active' => $isActive ? 1 : 0
        ]);
    }

    /**
     * Update password
     */
    public function updatePassword($loginId, $newPassword)
    {
        return $this->update($loginId, [
            'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT)
        ]);
    }

    /**
     * Verify password
     */
    public function verifyPassword($email, $password)
    {
        $user = $this->findByEmail($email);
        
        if (!$user || !$user['password_hash']) {
            return false;
        }

        return password_verify($password, $user['password_hash']) ? $user : false;
    }

    /**
     * Get active users count by type
     */
    public function getActiveUsersCount($userType = null)
    {
        $builder = $this->builder()->where('is_active', 1);
        
        if ($userType) {
            $builder->where('user_type', $userType);
        }
        
        return $builder->countAllResults();
    }

    /**
     * Get recent logins
     */
    public function getRecentLogins($limit = 10)
    {
        return $this->orderBy('last_login_at', 'DESC')
                   ->limit($limit)
                   ->find();
    }
}