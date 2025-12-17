<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentInvoiceModel extends Model
{
    protected $table = 'payment_invoices';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'invoice_number',
        'merchant_id',
        'subscription_id',
        'plan_id',
        'billing_period_start',
        'billing_period_end',
        'subtotal',
        'tax_amount',
        'total_amount',
        'status',
        'due_date',
        'paid_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'invoice_number' => 'required|max_length[50]|is_unique[payment_invoices.invoice_number,id,{id}]',
        'merchant_id' => 'required|integer',
        'subscription_id' => 'required|integer',
        'plan_id' => 'required|integer',
        'billing_period_start' => 'required|valid_date',
        'billing_period_end' => 'required|valid_date',
        'subtotal' => 'required|decimal',
        'tax_amount' => 'permit_empty|decimal',
        'total_amount' => 'required|decimal',
        'status' => 'required|in_list[draft,pending,paid,overdue,cancelled]',
        'due_date' => 'required|valid_date'
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Custom methods
    public function getMerchantInvoices($merchantId, $status = null)
    {
        $builder = $this->where('merchant_id', $merchantId);
        
        if ($status) {
            $builder->where('status', $status);
        }
        
        return $builder->orderBy('created_at', 'DESC')->findAll();
    }

    public function getInvoiceWithDetails($invoiceId)
    {
        return $this->select('payment_invoices.*, 
                             merchants.business_name,
                             merchants.owner_name,
                             plans.name as plan_name,
                             subscriptions.status as subscription_status')
                    ->join('merchants', 'merchants.id = payment_invoices.merchant_id')
                    ->join('plans', 'plans.id = payment_invoices.plan_id')
                    ->join('subscriptions', 'subscriptions.id = payment_invoices.subscription_id')
                    ->where('payment_invoices.id', $invoiceId)
                    ->first();
    }

    public function generateInvoiceNumber()
    {
        do {
            $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while ($this->where('invoice_number', $invoiceNumber)->first());
        
        return $invoiceNumber;
    }

    public function markAsPaid($invoiceId)
    {
        return $this->update($invoiceId, [
            'status' => 'paid',
            'paid_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function markAsOverdue($invoiceId)
    {
        return $this->update($invoiceId, ['status' => 'overdue']);
    }

    public function getOverdueInvoices()
    {
        return $this->where('status', 'pending')
                    ->where('due_date <', date('Y-m-d'))
                    ->findAll();
    }

    public function getInvoicesByDateRange($startDate, $endDate, $status = null)
    {
        $builder = $this->where('created_at >=', $startDate)
                        ->where('created_at <=', $endDate);
        
        if ($status) {
            $builder->where('status', $status);
        }
        
        return $builder->orderBy('created_at', 'DESC')->findAll();
    }

    public function createInvoice($merchantId, $subscriptionId, $planId, $amount, $billingPeriodStart, $billingPeriodEnd, $dueDate)
    {
        $taxRate = 0.15; // 15% VAT
        $subtotal = $amount;
        $taxAmount = $subtotal * $taxRate;
        $totalAmount = $subtotal + $taxAmount;
        
        $data = [
            'invoice_number' => $this->generateInvoiceNumber(),
            'merchant_id' => $merchantId,
            'subscription_id' => $subscriptionId,
            'plan_id' => $planId,
            'billing_period_start' => $billingPeriodStart,
            'billing_period_end' => $billingPeriodEnd,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'status' => 'pending',
            'due_date' => $dueDate
        ];
        
        return $this->insert($data);
    }
}
