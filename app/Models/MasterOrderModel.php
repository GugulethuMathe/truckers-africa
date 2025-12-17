<?php

namespace App\Models;

use CodeIgniter\Model;

class MasterOrderModel extends Model
{
    protected $table = 'master_orders';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'booking_reference',
        'driver_id',
        'grand_total',
        'vehicle_model',
        'vehicle_license_plate',
        'estimated_arrival',
        'order_status',
        'terms_accepted'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'booking_reference' => 'required|max_length[255]|is_unique[master_orders.booking_reference,id,{id}]',
        'driver_id' => 'required|integer',
        'grand_total' => 'required|decimal',
        'order_status' => 'required|in_list[pending,accepted,rejected,in_progress,completed,cancelled]',
        'terms_accepted' => 'in_list[0,1]'
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Custom methods
    public function getOrdersByDriver($driverId, $status = null)
    {
        $builder = $this->where('driver_id', $driverId);
        
        if ($status) {
            $builder->where('order_status', $status);
        }
        
        return $builder->orderBy('created_at', 'DESC')->findAll();
    }

    public function getOrderWithItems($orderId)
    {
        $order = $this->find($orderId);
        
        if ($order) {
            $orderItemModel = new \App\Models\OrderItemModel();
            $order['items'] = $orderItemModel->where('master_order_id', $orderId)->findAll();
        }
        
        return $order;
    }

    public function updateOrderStatus($orderId, $status)
    {
        return $this->update($orderId, ['order_status' => $status]);
    }

    public function generateBookingReference()
    {
        do {
            $reference = 'M-ORD-' . strtoupper(uniqid());
        } while ($this->where('booking_reference', $reference)->first());
        
        return $reference;
    }

    public function getOrdersByStatus($status)
    {
        return $this->where('order_status', $status)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    public function getOrdersByDateRange($startDate, $endDate)
    {
        return $this->where('created_at >=', $startDate)
                    ->where('created_at <=', $endDate)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }
}
