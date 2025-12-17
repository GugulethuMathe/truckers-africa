<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderModel extends Model
{
    protected $table            = 'master_orders';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['driver_id', 'booking_reference', 'grand_total', 'order_status', 'vehicle_model', 'vehicle_license_plate', 'estimated_arrival', 'terms_accepted'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function getOrdersByMerchant($merchantId, $status = 'all')
    {
        // Join through merchant_listings to get the correct merchant_id
        // This handles cases where order_items.merchant_id might be incorrect
        // Show only orders from PRIMARY location or locations without active branch users
        $builder = $this->select('master_orders.*, CONCAT(truck_drivers.name, " ", truck_drivers.surname) as driver_name, COUNT(order_items.id) as item_count, GROUP_CONCAT(DISTINCT merchant_listings.title SEPARATOR ", ") as listing_title')
            ->join('truck_drivers', 'truck_drivers.id = master_orders.driver_id')
            ->join('order_items', 'order_items.master_order_id = master_orders.id')
            ->join('merchant_listings', 'merchant_listings.id = order_items.listing_id')
            ->join('merchant_locations', 'merchant_locations.id = merchant_listings.location_id', 'left')
            ->where('merchant_listings.merchant_id', $merchantId)
            ->where('(merchant_locations.is_primary = 1 OR merchant_locations.id NOT IN (SELECT location_id FROM branch_users WHERE is_active = 1))', null, false)
            ->groupBy('master_orders.id');

        if ($status !== 'all') {
            $builder->where('master_orders.order_status', $status);
        }

        return $builder->orderBy('master_orders.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get order items for a specific merchant order
     * Shows only items from PRIMARY location or locations without active branch users
     */
    public function getOrderItemsByMerchant($merchantId)
    {
        $orderItemModel = new \App\Models\OrderItemModel();
        // Use merchant_listings.merchant_id for accurate filtering
        // Show only items from PRIMARY location or locations without active branch users
        return $orderItemModel->select('order_items.*, merchant_listings.title as listing_title')
            ->join('merchant_listings', 'merchant_listings.id = order_items.listing_id')
            ->join('merchant_locations', 'merchant_locations.id = merchant_listings.location_id', 'left')
            ->where('merchant_listings.merchant_id', $merchantId)
            ->where('(merchant_locations.is_primary = 1 OR merchant_locations.id NOT IN (SELECT location_id FROM branch_users WHERE is_active = 1))', null, false)
            ->orderBy('order_items.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get order details with items for merchant
     * Shows only orders from PRIMARY location or locations without active branch users
     */
    public function getOrderWithItems($orderId, $merchantId)
    {
        // Get the main order
        // Show only orders from PRIMARY location or locations without active branch users
        $order = $this->select('master_orders.*, CONCAT(truck_drivers.name, " ", truck_drivers.surname) as driver_name')
            ->join('truck_drivers', 'truck_drivers.id = master_orders.driver_id')
            ->join('order_items', 'order_items.master_order_id = master_orders.id')
            ->join('merchant_listings', 'merchant_listings.id = order_items.listing_id')
            ->join('merchant_locations', 'merchant_locations.id = merchant_listings.location_id', 'left')
            ->where('master_orders.id', $orderId)
            ->where('merchant_listings.merchant_id', $merchantId)
            ->where('(merchant_locations.is_primary = 1 OR merchant_locations.id NOT IN (SELECT location_id FROM branch_users WHERE is_active = 1))', null, false)
            ->first();

        if ($order) {
            // Get the order items for this specific order and merchant
            // Show only items from PRIMARY location or locations without active branch users
            $orderItemModel = new \App\Models\OrderItemModel();
            $order['items'] = $orderItemModel->select('order_items.*, merchant_listings.title as listing_title, merchant_listings.currency_code')
                ->join('merchant_listings', 'merchant_listings.id = order_items.listing_id')
                ->join('merchant_locations', 'merchant_locations.id = merchant_listings.location_id', 'left')
                ->where('order_items.master_order_id', $orderId)
                ->where('merchant_listings.merchant_id', $merchantId)
                ->where('(merchant_locations.is_primary = 1 OR merchant_locations.id NOT IN (SELECT location_id FROM branch_users WHERE is_active = 1))', null, false)
                ->findAll();
        }

        return $order;
    }
}
