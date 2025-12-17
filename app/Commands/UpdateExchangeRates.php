<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\CurrencyService;

class UpdateExchangeRates extends BaseCommand
{
    protected $group = 'Currency';
    protected $name = 'currency:update-rates';
    protected $description = 'Update exchange rates from fawazahmed0 API';

    public function run(array $params)
    {
        CLI::write('Starting exchange rate update...', 'green');
        
        $currencyService = new CurrencyService();
        
        try {
            $results = $currencyService->updateAllExchangeRates();
            
            $successful = 0;
            $failed = 0;
            
            foreach ($results as $result) {
                if ($result['success']) {
                    $successful++;
                    CLI::write("✓ {$result['from']} → {$result['to']}: {$result['rate']}", 'green');
                } else {
                    $failed++;
                    CLI::write("✗ Failed: {$result['from']} → {$result['to']}", 'red');
                }
            }
            
            CLI::newLine();
            CLI::write("Exchange rate update completed!", 'yellow');
            CLI::write("Successful: {$successful}", 'green');
            CLI::write("Failed: {$failed}", 'red');
            
            if ($failed > 0) {
                CLI::write("Check logs for detailed error information.", 'yellow');
            }
            
        } catch (\Exception $e) {
            CLI::error('Error updating exchange rates: ' . $e->getMessage());
            return EXIT_ERROR;
        }
        
        return EXIT_SUCCESS;
    }
}
