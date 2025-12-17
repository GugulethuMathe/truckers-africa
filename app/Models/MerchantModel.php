<?php

namespace App\Models;

use CodeIgniter\Model;

class MerchantModel extends Model
{
    protected $table = 'merchants';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'owner_name', 'email', 'password_hash', 'google_id', 'business_name',
        'business_contact_number', 'business_whatsapp_number', 'physical_address',
        'latitude', 'longitude', 'main_service', 'profile_description', 'business_description', 'profile_image_url',
        'business_image_url', 'status', 'verification_status', 'is_visible', 'is_verified', 'business_type',
        'verification_submitted_at', 'verification_completed_at', 'verified_by', 'approval_notification_seen',
        'password_reset_token', 'password_reset_expires', 'onboarding_completed',
        'current_locations_count', 'current_listings_count'
    ];

    // Enable timestamps
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Searches for active and visible merchants based on a query string.
     * The query searches in merchant names, descriptions, and their offered services.
     *
     * @param string $query The search term.
     * @return array An array of matching merchant records.
     */
    public function searchMerchants(string $query): array
    {
        // This query finds merchants that are approved AND visible.
        return $this->select('merchants.*')
            ->distinct()
            ->join('merchant_services', 'merchant_services.merchant_id = merchants.id', 'left')
            ->join('services', 'services.id = merchant_services.service_id', 'left')
            ->where('merchants.verification_status', 'approved')
            ->where('merchants.is_visible', 1)
            ->groupStart()
                ->like('merchants.business_name', $query)
                ->orLike('merchants.profile_description', $query)
                ->orLike('services.name', $query)
            ->groupEnd()
            ->findAll();
    }

    /**
     * Find all merchants with a 'pending' verification status.
     *
     * @return array An array of pending merchant records.
     */
    public function findPending(): array
    {
        return $this->where('verification_status', 'pending')->findAll();
    }
    
    public function verifyPassword(string $email, string $password)
    {
        $merchant = $this->where('email', $email)->first();

        if ($merchant && password_verify($password, $merchant['password_hash'])) {
            return $merchant;
        }
        return false;
    }

    /**
     * Get merchants pending document verification
     */
    public function getPendingVerification()
    {
        return $this->where('verification_status', 'pending')
                   ->where('verification_submitted_at IS NOT NULL')
                   ->orderBy('verification_submitted_at', 'ASC')
                   ->findAll();
    }

    /**
     * Update merchant verification status
     */
    public function updateVerificationStatus($merchantId, $status, $verifiedBy = null)
    {
        $data = [
            'is_verified' => $status,
            'verification_completed_at' => date('Y-m-d H:i:s')
        ];

        if ($verifiedBy) {
            $data['verified_by'] = $verifiedBy;
        }

        // If verified, also update verification_status to approved and make visible
        if ($status === 'verified') {
            $data['verification_status'] = 'approved';
            $data['is_visible'] = 1; // Make merchant visible on platform
        }

        return $this->update($merchantId, $data);
    }

    /**
     * Mark verification as submitted
     */
    public function markVerificationSubmitted($merchantId)
    {
        return $this->update($merchantId, [
            'verification_submitted_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get merchant with verification progress
     */
    public function getMerchantWithVerification($merchantId)
    {
        $merchant = $this->find($merchantId);
        if (!$merchant) {
            return null;
        }

        $documentModel = new \App\Models\MerchantDocumentModel();
        $merchant['verification_progress'] = $documentModel->getVerificationProgress(
            $merchantId,
            $merchant['business_type'] ?? 'individual'
        );

        return $merchant;
    }

    /**
     * Validate password reset token
     */
    public function validatePasswordResetToken(string $email, string $token): ?array
    {
        $merchant = $this->where('email', $email)
                        ->where('password_reset_token', $token)
                        ->first();

        if (!$merchant) {
            return null;
        }

        // Check if token has expired
        if (!empty($merchant['password_reset_expires'])) {
            $expiryTime = strtotime($merchant['password_reset_expires']);
            if (time() > $expiryTime) {
                return null; // Token expired
            }
        }

        return $merchant;
    }

    /**
     * Clear password reset token after use
     */
    public function clearPasswordResetToken(int $merchantId): bool
    {
        return $this->update($merchantId, [
            'password_reset_token' => null,
            'password_reset_expires' => null
        ]);
    }

    /**
     * Update merchant password
     */
    public function updatePassword(int $merchantId, string $newPassword): bool
    {
        return $this->update($merchantId, [
            'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT)
        ]);
    }
}