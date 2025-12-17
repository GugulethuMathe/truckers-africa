<?php

namespace App\Models;

use CodeIgniter\Model;

class DriverDocumentModel extends Model
{
    protected $table = 'driver_documents';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'driver_id',
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
        'driver_id' => 'required|integer',
        'document_type' => 'required|in_list[driver_license,vehicle_registration,proof_of_residence,other]',
        'file_name' => 'required|max_length[255]',
        'file_path' => 'required|max_length[500]',
    ];

    public static function getUploadPath(string $documentType): string
    {
        return 'uploads/driver_documents/' . $documentType . '/';
    }

    public static function generateFileName(int $driverId, string $documentType, string $originalName): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION) ?: 'bin';
        $timestamp = time();
        return "driver_{$driverId}_{$documentType}_{$timestamp}.{$extension}";
    }
}

