<?php

namespace App\Models;

use CodeIgniter\Model;

class ApiTokenModel extends Model
{
    protected $table = 'api_tokens';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'user_login_id',
        'driver_id',
        'token',
        'token_hash',
        'refresh_token',
        'refresh_token_hash',
        'token_name',
        'expires_at',
        'refresh_expires_at',
        'is_active',
        'device_id',
        'device_name',
        'ip_address',
        'user_agent',
        'last_used_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'user_login_id' => 'permit_empty|integer',
        'driver_id' => 'permit_empty|integer',
        'token' => 'permit_empty|max_length[512]',
        'token_hash' => 'permit_empty|max_length[255]',
        'expires_at' => 'required|valid_date',
        'is_active' => 'in_list[0,1]'
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Custom methods
    public function findActiveTokens($userId)
    {
        return $this->where('user_login_id', $userId)
                    ->where('is_active', 1)
                    ->where('expires_at >', date('Y-m-d H:i:s'))
                    ->findAll();
    }

    public function revokeToken($tokenId)
    {
        return $this->update($tokenId, ['is_active' => 0]);
    }

    public function updateLastUsed($tokenId)
    {
        return $this->update($tokenId, ['last_used_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Create a new JWT token record for a driver
     */
    public function createDriverToken(int $driverId, string $token, string $refreshToken, array $deviceInfo = []): int|false
    {
        $data = [
            'driver_id' => $driverId,
            'token_hash' => hash('sha256', $token),
            'refresh_token_hash' => hash('sha256', $refreshToken),
            'expires_at' => date('Y-m-d H:i:s', time() + 2592000), // 30 days
            'refresh_expires_at' => date('Y-m-d H:i:s', time() + 7776000), // 90 days
            'is_active' => 1,
            'device_id' => $deviceInfo['device_id'] ?? null,
            'device_name' => $deviceInfo['device_name'] ?? null,
            'ip_address' => $deviceInfo['ip_address'] ?? null,
            'user_agent' => $deviceInfo['user_agent'] ?? null,
            'last_used_at' => date('Y-m-d H:i:s')
        ];

        return $this->insert($data);
    }

    /**
     * Validate a JWT token hash
     */
    public function validateTokenHash(string $tokenHash): array|false
    {
        $tokenRecord = $this->where('token_hash', $tokenHash)
                           ->where('is_active', 1)
                           ->where('expires_at >', date('Y-m-d H:i:s'))
                           ->first();

        if ($tokenRecord) {
            // Update last used timestamp
            $this->update($tokenRecord['id'], ['last_used_at' => date('Y-m-d H:i:s')]);
        }

        return $tokenRecord ?: false;
    }

    /**
     * Revoke all tokens for a driver
     */
    public function revokeAllDriverTokens(int $driverId): bool
    {
        return $this->where('driver_id', $driverId)
                   ->set(['is_active' => 0])
                   ->update();
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
     * Get active tokens for a driver
     */
    public function getDriverTokens(int $driverId): array
    {
        return $this->where('driver_id', $driverId)
                   ->where('is_active', 1)
                   ->where('expires_at >', date('Y-m-d H:i:s'))
                   ->orderBy('created_at', 'DESC')
                   ->findAll();
    }
}
