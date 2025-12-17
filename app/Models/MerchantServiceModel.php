<?php

namespace App\Models;

use CodeIgniter\Model;

class MerchantServiceModel extends Model
{
    protected $table = 'merchant_services';
    protected $primaryKey = ['merchant_id', 'service_id'];
    protected $returnType = 'array';

    protected $allowedFields = ['merchant_id', 'service_id'];

    /**
     * Fetch all service IDs for a given merchant.
     * @param int $merchantId
     * @return array
     */
    public function findByMerchantId(int $merchantId): array
    {
        return $this->where('merchant_id', $merchantId)->findColumn('service_id') ?? [];
    }

    /**
     * Update the services for a merchant.
     * This will delete all existing services and insert the new ones.
     * @param int $merchantId
     * @param array $serviceIds
     * @return bool
     */
    public function updateServicesForMerchant(int $merchantId, array $serviceIds): bool
    {
        $this->db->transStart();

        // Delete existing services for the merchant
        $this->where('merchant_id', $merchantId)->delete();

        // Insert new services
        if (!empty($serviceIds)) {
            $insertData = [];
            foreach ($serviceIds as $serviceId) {
                $insertData[] = [
                    'merchant_id' => $merchantId,
                    'service_id'  => $serviceId
                ];
            }
            $this->insertBatch($insertData);
        }

        $this->db->transComplete();

        return $this->db->transStatus();
    }
}
