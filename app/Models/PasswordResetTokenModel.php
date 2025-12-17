<?php

namespace App\Models;

use CodeIgniter\Model;

class PasswordResetTokenModel extends Model
{
    protected $table = 'password_reset_tokens';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'email',
        'user_type',
        'token',
        'expires_at',
        'used_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'email' => 'required|valid_email|max_length[255]',
        'user_type' => 'required|in_list[admin,driver,merchant]',
        'token' => 'required|max_length[255]',
        'expires_at' => 'required|valid_date'
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Create a new password reset token
     */
    public function createToken(string $email, string $userType): string|false
    {
        // Delete any existing tokens for this email and user type
        $this->where('email', $email)
             ->where('user_type', $userType)
             ->delete();

        // Generate a secure random token
        $token = bin2hex(random_bytes(32));
        
        // Token expires in 1 hour
        $expiresAt = date('Y-m-d H:i:s', time() + 3600);

        $data = [
            'email' => $email,
            'user_type' => $userType,
            'token' => hash('sha256', $token), // Store hashed token
            'expires_at' => $expiresAt
        ];

        if ($this->insert($data)) {
            return $token; // Return unhashed token for email
        }

        return false;
    }

    /**
     * Validate a password reset token
     */
    public function validateToken(string $token, string $email, string $userType): array|false
    {
        $hashedToken = hash('sha256', $token);
        
        $tokenRecord = $this->where('token', $hashedToken)
                           ->where('email', $email)
                           ->where('user_type', $userType)
                           ->where('expires_at >', date('Y-m-d H:i:s'))
                           ->where('used_at', null)
                           ->first();

        return $tokenRecord ?: false;
    }

    /**
     * Mark token as used
     */
    public function markTokenAsUsed(int $tokenId): bool
    {
        return $this->update($tokenId, [
            'used_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Clean up expired tokens
     */
    public function cleanupExpiredTokens(): int
    {
        return $this->where('expires_at <', date('Y-m-d H:i:s'))
                   ->delete();
    }

    /**
     * Delete tokens for a specific email and user type
     */
    public function deleteTokensForUser(string $email, string $userType): bool
    {
        return $this->where('email', $email)
                   ->where('user_type', $userType)
                   ->delete();
    }
}
