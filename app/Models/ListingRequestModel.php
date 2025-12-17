<?php

namespace App\Models;

use CodeIgniter\Model;

class ListingRequestModel extends Model
{
    protected $table = 'listing_requests';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'branch_user_id',
        'location_id',
        'merchant_id',
        'title',
        'description',
        'suggested_price',
        'currency_code',
        'unit',
        'suggested_categories',
        'main_image',
        'gallery_images',
        'justification',
        'status',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
        'created_listing_id'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'branch_user_id' => 'required|integer',
        'location_id' => 'required|integer',
        'merchant_id' => 'required|integer',
        'title' => 'required|min_length[3]|max_length[255]',
        'description' => 'permit_empty|max_length[5000]',
        'suggested_price' => 'permit_empty|decimal|greater_than_equal_to[0]',
        'currency_code' => 'permit_empty|exact_length[3]|alpha',
        'unit' => 'permit_empty|max_length[50]',
        'justification' => 'permit_empty|max_length[1000]',
        'status' => 'permit_empty|in_list[pending,approved,rejected,converted]'
    ];

    protected $validationMessages = [
        'title' => [
            'required' => 'Service title is required',
            'min_length' => 'Service title must be at least 3 characters',
            'max_length' => 'Service title cannot exceed 255 characters'
        ],
        'suggested_price' => [
            'decimal' => 'Price must be a valid number',
            'greater_than_equal_to' => 'Price cannot be negative'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Get all requests for a specific branch user
     */
    public function getRequestsByBranchUser(int $branchUserId, ?string $status = null): array
    {
        $builder = $this->select('listing_requests.*, merchant_locations.location_name')
                        ->join('merchant_locations', 'merchant_locations.id = listing_requests.location_id')
                        ->where('listing_requests.branch_user_id', $branchUserId)
                        ->orderBy('listing_requests.created_at', 'DESC');

        if ($status) {
            $builder->where('listing_requests.status', $status);
        }

        return $builder->findAll();
    }

    /**
     * Get all requests for a specific merchant
     */
    public function getRequestsByMerchant(int $merchantId, ?string $status = null): array
    {
        $builder = $this->select('listing_requests.*, merchant_locations.location_name, branch_users.full_name as requester_name, branch_users.email as requester_email')
                        ->join('merchant_locations', 'merchant_locations.id = listing_requests.location_id')
                        ->join('branch_users', 'branch_users.id = listing_requests.branch_user_id')
                        ->where('listing_requests.merchant_id', $merchantId)
                        ->orderBy('listing_requests.created_at', 'DESC');

        if ($status) {
            $builder->where('listing_requests.status', $status);
        }

        return $builder->findAll();
    }

    /**
     * Get pending requests count for a merchant
     */
    public function getPendingCountByMerchant(int $merchantId): int
    {
        return $this->where('merchant_id', $merchantId)
                    ->where('status', 'pending')
                    ->countAllResults();
    }

    /**
     * Get request statistics for a branch user
     */
    public function getBranchUserStats(int $branchUserId): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table($this->table);
        
        $stats = $builder->select('status, COUNT(*) as count')
                        ->where('branch_user_id', $branchUserId)
                        ->groupBy('status')
                        ->get()
                        ->getResultArray();

        $result = [
            'total' => 0,
            'pending' => 0,
            'approved' => 0,
            'rejected' => 0,
            'converted' => 0
        ];

        foreach ($stats as $stat) {
            $result[$stat['status']] = (int)$stat['count'];
            $result['total'] += (int)$stat['count'];
        }

        return $result;
    }

    /**
     * Get request statistics for a merchant
     */
    public function getMerchantStats(int $merchantId): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table($this->table);
        
        $stats = $builder->select('status, COUNT(*) as count')
                        ->where('merchant_id', $merchantId)
                        ->groupBy('status')
                        ->get()
                        ->getResultArray();

        $result = [
            'total' => 0,
            'pending' => 0,
            'approved' => 0,
            'rejected' => 0,
            'converted' => 0
        ];

        foreach ($stats as $stat) {
            $result[$stat['status']] = (int)$stat['count'];
            $result['total'] += (int)$stat['count'];
        }

        return $result;
    }

    /**
     * Approve a listing request
     */
    public function approveRequest(int $requestId, int $reviewedBy): bool
    {
        return $this->update($requestId, [
            'status' => 'approved',
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Reject a listing request
     */
    public function rejectRequest(int $requestId, int $reviewedBy, string $reason): bool
    {
        return $this->update($requestId, [
            'status' => 'rejected',
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'rejection_reason' => $reason
        ]);
    }

    /**
     * Mark request as converted to listing
     */
    public function markAsConverted(int $requestId, int $listingId): bool
    {
        return $this->update($requestId, [
            'status' => 'converted',
            'created_listing_id' => $listingId
        ]);
    }

    /**
     * Get request with full details
     */
    public function getRequestWithDetails(int $requestId): ?array
    {
        return $this->select('listing_requests.*, 
                             merchant_locations.location_name, 
                             merchant_locations.physical_address,
                             branch_users.full_name as requester_name, 
                             branch_users.email as requester_email,
                             branch_users.phone_number as requester_phone,
                             merchants.business_name')
                    ->join('merchant_locations', 'merchant_locations.id = listing_requests.location_id')
                    ->join('branch_users', 'branch_users.id = listing_requests.branch_user_id')
                    ->join('merchants', 'merchants.id = listing_requests.merchant_id')
                    ->where('listing_requests.id', $requestId)
                    ->first();
    }

    /**
     * Check if branch user can submit more requests (optional rate limiting)
     */
    public function canSubmitRequest(int $branchUserId, int $maxPendingRequests = 10): array
    {
        $pendingCount = $this->where('branch_user_id', $branchUserId)
                             ->where('status', 'pending')
                             ->countAllResults();

        $canSubmit = $pendingCount < $maxPendingRequests;
        
        return [
            'can_submit' => $canSubmit,
            'pending_count' => $pendingCount,
            'max_allowed' => $maxPendingRequests,
            'message' => $canSubmit 
                ? "You can submit " . ($maxPendingRequests - $pendingCount) . " more request(s)."
                : "You have reached the maximum of {$maxPendingRequests} pending requests. Please wait for review."
        ];
    }

    /**
     * Get recent requests for dashboard
     */
    public function getRecentRequests(int $merchantId, int $limit = 5): array
    {
        return $this->select('listing_requests.*, merchant_locations.location_name, branch_users.full_name as requester_name')
                    ->join('merchant_locations', 'merchant_locations.id = listing_requests.location_id')
                    ->join('branch_users', 'branch_users.id = listing_requests.branch_user_id')
                    ->where('listing_requests.merchant_id', $merchantId)
                    ->orderBy('listing_requests.created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }
}

