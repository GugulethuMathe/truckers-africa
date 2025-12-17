<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderServiceModel extends Model
{
    protected $table = 'order_services';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'order_id',
        'listing_id',
        'quantity',
        'unit_price',
        'subtotal'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = null;

    protected $validationRules = [
        'order_id' => 'required|integer',
        'listing_id' => 'required|integer',
        'quantity' => 'permit_empty|integer|greater_than[0]',
        'unit_price' => 'permit_empty|decimal',
        'subtotal' => 'permit_empty|decimal'
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Custom methods
    public function getOrderServices($orderId)
    {
        return $this->where('order_id', $orderId)
                    ->orderBy('created_at', 'ASC')
                    ->findAll();
    }

    public function getOrderServicesWithDetails($orderId)
    {
        return $this->select('order_services.*, 
                             merchant_listings.title,
                             merchant_listings.description,
                             merchant_listings.price_type,
                             merchants.business_name')
                    ->join('merchant_listings', 'merchant_listings.id = order_services.listing_id')
                    ->join('merchants', 'merchants.id = merchant_listings.merchant_id')
                    ->where('order_services.order_id', $orderId)
                    ->orderBy('order_services.created_at', 'ASC')
                    ->findAll();
    }

    public function addServiceToOrder($orderId, $listingId, $quantity = 1, $unitPrice = null)
    {
        // Get listing details if unit price not provided
        if (!$unitPrice) {
            $listingModel = new \App\Models\MerchantListingModel();
            $listing = $listingModel->find($listingId);
            $unitPrice = $listing['price'] ?? 0;
        }
        
        $subtotal = $unitPrice * $quantity;
        
        $data = [
            'order_id' => $orderId,
            'listing_id' => $listingId,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $subtotal
        ];
        
        return $this->insert($data);
    }

    public function updateServiceQuantity($serviceId, $quantity)
    {
        $service = $this->find($serviceId);
        if (!$service) {
            return false;
        }
        
        $newSubtotal = $service['unit_price'] * $quantity;
        
        return $this->update($serviceId, [
            'quantity' => $quantity,
            'subtotal' => $newSubtotal
        ]);
    }

    public function removeServiceFromOrder($serviceId)
    {
        return $this->delete($serviceId);
    }

    public function calculateOrderTotal($orderId)
    {
        $result = $this->select('SUM(subtotal) as total')
                       ->where('order_id', $orderId)
                       ->first();
        
        return $result['total'] ?? 0;
    }
}
