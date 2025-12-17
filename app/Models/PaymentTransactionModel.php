<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentTransactionModel extends Model
{
    protected $table = 'payment_transactions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'merchant_id',
        'subscription_id',
        'transaction_id',
        'amount',
        'currency',
        'status',
        'payment_method',
        'payfast_payment_id',
        'payfast_payment_status',
        'failure_reason',
        'processed_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'merchant_id' => 'required|integer',
        'subscription_id' => 'required|integer',
        'transaction_id' => 'required|max_length[255]',
        'amount' => 'required|decimal',
        'currency' => 'required|max_length[3]',
        'status' => 'required|in_list[pending,completed,failed,cancelled,refunded]'
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Custom methods
    public function getMerchantTransactions($merchantId, $status = null)
    {
        $builder = $this->where('merchant_id', $merchantId);
        
        if ($status) {
            $builder->where('status', $status);
        }
        
        return $builder->orderBy('created_at', 'DESC')->findAll();
    }

    public function getTransactionByPayfastId($payfastPaymentId)
    {
        return $this->where('payfast_payment_id', $payfastPaymentId)->first();
    }

    public function getTransactionById($transactionId)
    {
        return $this->where('transaction_id', $transactionId)->first();
    }

    public function createTransaction($data)
    {
        $transaction = [
            'merchant_id' => $data['merchant_id'],
            'subscription_id' => $data['subscription_id'],
            'transaction_id' => $data['transaction_id'],
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'ZAR',
            'status' => 'pending',
            'payment_method' => $data['payment_method'] ?? null,
            'payfast_payment_id' => $data['payfast_payment_id'] ?? null
        ];
        
        return $this->insert($transaction);
    }

    public function updateTransactionStatus($transactionId, $status, $payfastData = [])
    {
        $updateData = ['status' => $status];
        
        if ($status === 'completed') {
            $updateData['processed_at'] = date('Y-m-d H:i:s');
        }
        
        if (!empty($payfastData)) {
            if (isset($payfastData['payment_status'])) {
                $updateData['payfast_payment_status'] = $payfastData['payment_status'];
            }
            if (isset($payfastData['payment_id'])) {
                $updateData['payfast_payment_id'] = $payfastData['payment_id'];
            }
        }
        
        if ($status === 'failed' && isset($payfastData['failure_reason'])) {
            $updateData['failure_reason'] = $payfastData['failure_reason'];
        }
        
        return $this->where('transaction_id', $transactionId)->set($updateData)->update();
    }

    public function getTransactionsByDateRange($startDate, $endDate, $status = null)
    {
        $builder = $this->where('created_at >=', $startDate)
                        ->where('created_at <=', $endDate);
        
        if ($status) {
            $builder->where('status', $status);
        }
        
        return $builder->orderBy('created_at', 'DESC')->findAll();
    }

    public function getSuccessfulTransactions($merchantId = null)
    {
        $builder = $this->where('status', 'completed');
        
        if ($merchantId) {
            $builder->where('merchant_id', $merchantId);
        }
        
        return $builder->orderBy('processed_at', 'DESC')->findAll();
    }

    public function getFailedTransactions($merchantId = null)
    {
        $builder = $this->where('status', 'failed');
        
        if ($merchantId) {
            $builder->where('merchant_id', $merchantId);
        }
        
        return $builder->orderBy('created_at', 'DESC')->findAll();
    }

    public function getTotalRevenue($startDate = null, $endDate = null)
    {
        $builder = $this->select('SUM(amount) as total_revenue')
                        ->where('status', 'completed');
        
        if ($startDate) {
            $builder->where('processed_at >=', $startDate);
        }
        
        if ($endDate) {
            $builder->where('processed_at <=', $endDate);
        }
        
        $result = $builder->first();
        return $result['total_revenue'] ?? 0;
    }
}
