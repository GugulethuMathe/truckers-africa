<?php

namespace App\Models;

use CodeIgniter\Model;

class CurrencyModel extends Model
{
    protected $table = 'supported_currencies';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'currency_code',
        'currency_name', 
        'currency_symbol',
        'decimal_places',
        'is_active',
        'country_codes',
        'display_format',
        'priority'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'id' => 'integer',
        'decimal_places' => 'integer',
        'is_active' => 'boolean',
        'priority' => 'integer',
        'country_codes' => 'json'
    ];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'currency_code' => 'required|exact_length[3]|alpha|is_unique[supported_currencies.currency_code,id,{id}]',
        'currency_name' => 'required|max_length[100]',
        'currency_symbol' => 'required|max_length[10]',
        'decimal_places' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[4]',
        'priority' => 'required|integer|in_list[1,2,3]'
    ];
    protected $validationMessages = [
        'currency_code' => [
            'required' => 'Currency code is required',
            'exact_length' => 'Currency code must be exactly 3 characters',
            'alpha' => 'Currency code must contain only letters',
            'is_unique' => 'This currency code already exists'
        ]
    ];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = ['formatCurrencyCode'];
    protected $afterInsert = [];
    protected $beforeUpdate = ['formatCurrencyCode'];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Format currency code to uppercase before insert/update
     */
    protected function formatCurrencyCode(array $data): array
    {
        if (isset($data['data']['currency_code'])) {
            $data['data']['currency_code'] = strtoupper($data['data']['currency_code']);
        }
        return $data;
    }

    /**
     * Get active currencies by priority
     */
    public function getByPriority(int $priority = null): array
    {
        $builder = $this->where('is_active', 1);
        
        if ($priority !== null) {
            $builder->where('priority <=', $priority);
        }
        
        return $builder->orderBy('priority', 'ASC')
                      ->orderBy('currency_name', 'ASC')
                      ->findAll();
    }

    /**
     * Get currency by country code
     */
    public function getByCountryCode(string $countryCode): ?array
    {
        $currencies = $this->where('is_active', 1)->findAll();
        
        foreach ($currencies as $currency) {
            $countryCodes = $currency['country_codes'] ?? [];
            if (in_array(strtoupper($countryCode), $countryCodes)) {
                return $currency;
            }
        }
        
        return null;
    }

    /**
     * Get high priority currencies for dropdowns
     */
    public function getHighPriorityCurrencies(): array
    {
        return $this->getByPriority(1);
    }

    /**
     * Get all active currencies for admin
     */
    public function getAllActive(): array
    {
        return $this->where('is_active', 1)
                   ->orderBy('priority', 'ASC')
                   ->orderBy('currency_name', 'ASC')
                   ->findAll();
    }

    /**
     * Check if currency code exists and is active
     */
    public function isValidCurrency(string $currencyCode): bool
    {
        $currency = $this->where('currency_code', strtoupper($currencyCode))
                        ->where('is_active', 1)
                        ->first();
        
        return $currency !== null;
    }

    /**
     * Get currency dropdown options
     */
    public function getDropdownOptions(int $priority = 1): array
    {
        $currencies = $this->getByPriority($priority);
        $options = [];
        
        foreach ($currencies as $currency) {
            $options[$currency['currency_code']] = $currency['currency_symbol'] . ' - ' . $currency['currency_name'];
        }
        
        return $options;
    }
}
