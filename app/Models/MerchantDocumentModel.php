<?php

namespace App\Models;

use CodeIgniter\Model;

class MerchantDocumentModel extends Model
{
    protected $table = 'merchant_documents';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'merchant_id',
        'document_type',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'upload_date',
        'is_verified',
        'verified_by',
        'verified_at',
        'rejection_reason'
    ];

    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $createdField = 'upload_date';

    protected $validationRules = [
        'merchant_id' => 'required|integer',
        'document_type' => 'required|in_list[owner_id,company_registration,proof_of_residence,business_image]',
        'file_name' => 'required|max_length[255]',
        'file_path' => 'required|max_length[500]',
        'is_verified' => 'in_list[pending,approved,rejected]'
    ];

    protected $validationMessages = [
        'merchant_id' => [
            'required' => 'Merchant ID is required',
            'integer' => 'Merchant ID must be a valid number'
        ],
        'document_type' => [
            'required' => 'Document type is required',
            'in_list' => 'Invalid document type'
        ],
        'file_name' => [
            'required' => 'File name is required',
            'max_length' => 'File name is too long'
        ]
    ];

    /**
     * Get all documents for a specific merchant
     */
    public function getDocumentsByMerchant($merchantId)
    {
        return $this->where('merchant_id', $merchantId)
                   ->orderBy('document_type', 'ASC')
                   ->orderBy('upload_date', 'DESC')
                   ->findAll();
    }

    /**
     * Get specific document type for a merchant
     */
    public function getDocumentByType($merchantId, $documentType)
    {
        return $this->where('merchant_id', $merchantId)
                   ->where('document_type', $documentType)
                   ->orderBy('upload_date', 'DESC')
                   ->first();
    }

    /**
     * Get documents by verification status
     */
    public function getDocumentsByStatus($status = 'pending')
    {
        return $this->select('merchant_documents.*, merchants.business_name, merchants.owner_name')
                   ->join('merchants', 'merchants.id = merchant_documents.merchant_id')
                   ->where('merchant_documents.is_verified', $status)
                   ->orderBy('merchant_documents.upload_date', 'ASC')
                   ->findAll();
    }

    /**
     * Update document verification status
     */
    public function updateVerificationStatus($documentId, $status, $verifiedBy = null, $rejectionReason = null)
    {
        $data = [
            'is_verified' => $status,
            'verified_at' => date('Y-m-d H:i:s')
        ];

        if ($verifiedBy) {
            $data['verified_by'] = $verifiedBy;
        }

        if ($status === 'rejected' && $rejectionReason) {
            $data['rejection_reason'] = $rejectionReason;
        }

        return $this->update($documentId, $data);
    }

    /**
     * Check if merchant has all required documents
     */
    public function hasAllRequiredDocuments($merchantId, $businessType)
    {
        $verificationModel = new VerificationRequirementModel();
        $requiredDocs = $verificationModel->getRequiredDocuments($businessType);
        
        foreach ($requiredDocs as $requirement) {
            $document = $this->getDocumentByType($merchantId, $requirement['document_type']);
            if (!$document) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get merchant verification progress
     */
    public function getVerificationProgress($merchantId, $businessType)
    {
        $verificationModel = new VerificationRequirementModel();
        $requiredDocs = $verificationModel->getRequiredDocuments($businessType);
        $uploadedDocs = $this->getDocumentsByMerchant($merchantId);
        
        $progress = [
            'total_required' => count($requiredDocs),
            'uploaded' => 0,
            'approved' => 0,
            'rejected' => 0,
            'pending' => 0,
            'missing' => [],
            'documents' => []
        ];
        
        // Create a map of uploaded documents
        $uploadedMap = [];
        foreach ($uploadedDocs as $doc) {
            $uploadedMap[$doc['document_type']] = $doc;
            
            switch ($doc['is_verified']) {
                case 'approved':
                    $progress['approved']++;
                    break;
                case 'rejected':
                    $progress['rejected']++;
                    break;
                case 'pending':
                    $progress['pending']++;
                    break;
            }
        }
        
        // Check each required document
        foreach ($requiredDocs as $requirement) {
            $docType = $requirement['document_type'];
            
            if (isset($uploadedMap[$docType])) {
                $progress['uploaded']++;
                $progress['documents'][$docType] = array_merge($requirement, $uploadedMap[$docType]);
            } else {
                $progress['missing'][] = $requirement;
                $progress['documents'][$docType] = array_merge($requirement, [
                    'status' => 'missing',
                    'uploaded' => false
                ]);
            }
        }
        
        $progress['completion_percentage'] = $progress['total_required'] > 0 
            ? round(($progress['uploaded'] / $progress['total_required']) * 100) 
            : 0;
            
        $progress['approval_percentage'] = $progress['total_required'] > 0 
            ? round(($progress['approved'] / $progress['total_required']) * 100) 
            : 0;
        
        return $progress;
    }

    /**
     * Delete document and file
     */
    public function deleteDocument($documentId)
    {
        $document = $this->find($documentId);
        if ($document) {
            // Delete physical file
            $filePath = FCPATH . $document['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            // Delete database record
            return $this->delete($documentId);
        }
        
        return false;
    }

    /**
     * Get file upload path for document type
     */
    public static function getUploadPath($documentType)
    {
        $basePath = 'uploads/merchant_documents/';
        return $basePath . $documentType . '/';
    }

    /**
     * Generate unique filename
     */
    public static function generateFileName($merchantId, $documentType, $originalName)
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $timestamp = time();
        return "merchant_{$merchantId}_{$documentType}_{$timestamp}.{$extension}";
    }

    /**
     * Validate file upload
     */
    public static function validateFile($file, $documentType)
    {
        $verificationModel = new VerificationRequirementModel();
        $requirement = $verificationModel->getRequirementByType($documentType);
        
        if (!$requirement) {
            return ['valid' => false, 'error' => 'Invalid document type'];
        }
        
        // Check file size
        if ($file->getSize() > $requirement['max_file_size']) {
            $maxSizeMB = round($requirement['max_file_size'] / 1024 / 1024, 1);
            return ['valid' => false, 'error' => "File size exceeds {$maxSizeMB}MB limit"];
        }
        
        // Check file type
        $allowedTypes = explode(',', $requirement['accepted_formats']);
        $fileExtension = strtolower($file->getClientExtension());
        
        if (!in_array($fileExtension, $allowedTypes)) {
            return ['valid' => false, 'error' => 'File type not allowed. Accepted formats: ' . $requirement['accepted_formats']];
        }
        
        return ['valid' => true];
    }
}
