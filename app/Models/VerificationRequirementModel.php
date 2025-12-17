<?php

namespace App\Models;

use CodeIgniter\Model;

class VerificationRequirementModel extends Model
{
    protected $table = 'verification_requirements';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'business_type',
        'document_type',
        'is_required',
        'display_name',
        'description',
        'accepted_formats',
        'max_file_size'
    ];

    protected $useTimestamps = false;

    protected $validationRules = [
        'business_type' => 'required|in_list[business,individual]',
        'document_type' => 'required|in_list[owner_id,company_registration,proof_of_residence,business_image]',
        'display_name' => 'required|max_length[100]',
        'accepted_formats' => 'required',
        'max_file_size' => 'required|integer'
    ];

    /**
     * Get required documents for a business type
     */
    public function getRequiredDocuments($businessType)
    {
        return $this->where('business_type', $businessType)
                   ->where('is_required', 1)
                   ->orderBy('document_type', 'ASC')
                   ->findAll();
    }

    /**
     * Get requirement by document type and business type
     */
    public function getRequirement($businessType, $documentType)
    {
        return $this->where('business_type', $businessType)
                   ->where('document_type', $documentType)
                   ->first();
    }

    /**
     * Get requirement by document type (for any business type)
     */
    public function getRequirementByType($documentType)
    {
        return $this->where('document_type', $documentType)
                   ->first();
    }

    /**
     * Get all requirements grouped by business type
     */
    public function getAllRequirements()
    {
        $requirements = $this->orderBy('business_type', 'ASC')
                            ->orderBy('document_type', 'ASC')
                            ->findAll();
        
        $grouped = [];
        foreach ($requirements as $req) {
            $grouped[$req['business_type']][] = $req;
        }
        
        return $grouped;
    }

    /**
     * Check if document type is required for business type
     */
    public function isRequired($businessType, $documentType)
    {
        $requirement = $this->getRequirement($businessType, $documentType);
        return $requirement && $requirement['is_required'];
    }

    /**
     * Get formatted file size
     */
    public static function formatFileSize($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * Get document type icon
     */
    public static function getDocumentIcon($documentType)
    {
        $icons = [
            'owner_id' => '🆔',
            'company_registration' => '📋',
            'proof_of_residence' => '🏠',
            'business_image' => '📸'
        ];
        
        return $icons[$documentType] ?? '📄';
    }

    /**
     * Get document type color class
     */
    public static function getDocumentColorClass($documentType)
    {
        $colors = [
            'owner_id' => 'bg-blue-100 text-blue-800',
            'company_registration' => 'bg-purple-100 text-purple-800',
            'proof_of_residence' => 'bg-green-100 text-green-800',
            'business_image' => 'bg-orange-100 text-orange-800'
        ];
        
        return $colors[$documentType] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Get verification status color class
     */
    public static function getStatusColorClass($status)
    {
        $colors = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'approved' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            'missing' => 'bg-gray-100 text-gray-800'
        ];
        
        return $colors[$status] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Get business type display name
     */
    public static function getBusinessTypeDisplayName($businessType)
    {
        $names = [
            'business' => 'Registered Business',
            'individual' => 'Individual/Sole Proprietor'
        ];
        
        return $names[$businessType] ?? $businessType;
    }
}
