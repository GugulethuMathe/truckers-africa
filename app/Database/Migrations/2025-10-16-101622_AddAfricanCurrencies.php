<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAfricanCurrencies extends Migration
{
    public function up()
    {
        $data = [
            [
                'currency_code' => 'AOA',
                'currency_name' => 'Angolan Kwanza',
                'currency_symbol' => 'Kz',
                'decimal_places' => 2,
                'is_active' => 1,
                'country_codes' => json_encode(['AO']),
                'display_format' => '{symbol}{amount}',
                'priority' => 2,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'currency_code' => 'MWK',
                'currency_name' => 'Malawian Kwacha',
                'currency_symbol' => 'MK',
                'decimal_places' => 2,
                'is_active' => 1,
                'country_codes' => json_encode(['MW']),
                'display_format' => '{symbol}{amount}',
                'priority' => 2,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'currency_code' => 'SZL',
                'currency_name' => 'Swazi Lilangeni',
                'currency_symbol' => 'E',
                'decimal_places' => 2,
                'is_active' => 1,
                'country_codes' => json_encode(['SZ']),
                'display_format' => '{symbol}{amount}',
                'priority' => 2,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'currency_code' => 'LSL',
                'currency_name' => 'Lesotho Loti',
                'currency_symbol' => 'L',
                'decimal_places' => 2,
                'is_active' => 1,
                'country_codes' => json_encode(['LS']),
                'display_format' => '{symbol}{amount}',
                'priority' => 2,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'currency_code' => 'MZN',
                'currency_name' => 'Mozambican Metical',
                'currency_symbol' => 'MT',
                'decimal_places' => 2,
                'is_active' => 1,
                'country_codes' => json_encode(['MZ']),
                'display_format' => '{symbol}{amount}',
                'priority' => 2,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($data as $currency) {
            // Check if currency already exists
            $existing = $this->db->table('supported_currencies')
                ->where('currency_code', $currency['currency_code'])
                ->get()
                ->getRow();

            if ($existing) {
                // Update existing currency
                $this->db->table('supported_currencies')
                    ->where('currency_code', $currency['currency_code'])
                    ->update([
                        'currency_name' => $currency['currency_name'],
                        'currency_symbol' => $currency['currency_symbol'],
                        'is_active' => 1,
                        'priority' => $currency['priority'],
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
            } else {
                // Insert new currency
                $this->db->table('supported_currencies')->insert($currency);
            }
        }
    }

    public function down()
    {
        // Remove the added currencies
        $this->db->table('supported_currencies')
            ->whereIn('currency_code', ['AOA', 'MWK', 'SZL', 'LSL', 'MZN'])
            ->delete();
    }
}

