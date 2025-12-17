<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderItemModel extends Model
{
    protected $table = 'order_items';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'master_order_id',
        'merchant_id',
        'listing_id',
        'quantity',
        'price',
        'total_cost',
        'merchant_response_notes',
        'item_status',
        'status',
        'accepted_at',
        'rejected_at',
        'completed_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'master_order_id' => 'required|integer',
        'merchant_id' => 'required|integer',
        'quantity' => 'required|integer|greater_than[0]',
        'price' => 'required|decimal',
        'status' => 'in_list[pending,accepted,rejected,completed,cancelled]'
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Custom methods
    public function getOrderItems($masterOrderId)
    {
        return $this->where('master_order_id', $masterOrderId)
                    ->orderBy('created_at', 'ASC')
                    ->findAll();
    }

    public function getOrderItemsWithDetails($masterOrderId)
    {
        return $this->select('order_items.*, 
                             merchants.business_name,
                             merchant_listings.title as listing_title,
                             merchant_listings.description as listing_description')
                    ->join('merchants', 'merchants.id = order_items.merchant_id', 'left')
                    ->join('merchant_listings', 'merchant_listings.id = order_items.listing_id', 'left')
                    ->where('order_items.master_order_id', $masterOrderId)
                    ->orderBy('order_items.created_at', 'ASC')
                    ->findAll();
    }

    public function getMerchantOrderItems($merchantId, $status = null)
    {
        $builder = $this->where('merchant_id', $merchantId);
        
        if ($status) {
            $builder->where('status', $status);
        }
        
        return $builder->orderBy('created_at', 'DESC')->findAll();
    }

    public function updateItemStatus($itemId, $status, $notes = null)
    {
        $data = ['status' => $status];
        
        if ($notes) {
            $data['merchant_response_notes'] = $notes;
        }
        
        // Set timestamp based on status
        switch ($status) {
            case 'accepted':
                $data['accepted_at'] = date('Y-m-d H:i:s');
                break;
            case 'rejected':
                $data['rejected_at'] = date('Y-m-d H:i:s');
                break;
            case 'completed':
                $data['completed_at'] = date('Y-m-d H:i:s');
                break;
        }
        
        return $this->update($itemId, $data);
    }

    public function calculateOrderTotal($masterOrderId)
    {
        $result = $this->select('SUM(total_cost) as total')
                       ->where('master_order_id', $masterOrderId)
                       ->where('status !=', 'rejected')
                       ->where('status !=', 'cancelled')
                       ->first();
        
        return $result['total'] ?? 0;
    }

    public function getItemsByMerchantAndOrder($merchantId, $masterOrderId)
    {
        return $this->where('merchant_id', $merchantId)
                    ->where('master_order_id', $masterOrderId)
                    ->findAll();
    }
}
