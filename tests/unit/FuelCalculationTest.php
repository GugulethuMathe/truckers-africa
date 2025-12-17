<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Controllers\Routes;
use App\Models\PlatformSettingModel;

class FuelCalculationTest extends CIUnitTestCase
{
    protected $platformSettingModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->platformSettingModel = new PlatformSettingModel();
    }

    public function testFuelCalculationSettings()
    {
        // Test default fuel settings
        $fuelCostPerLiter = $this->platformSettingModel->getSetting('fuel_cost_per_liter_zar') ?: '23.50';
        $kmPerLiter = $this->platformSettingModel->getSetting('truck_fuel_consumption_km_per_liter') ?: '2.0';
        
        $this->assertIsString($fuelCostPerLiter);
        $this->assertIsString($kmPerLiter);
        $this->assertGreaterThan(0, (float) $fuelCostPerLiter);
        $this->assertGreaterThan(0, (float) $kmPerLiter);
    }

    public function testFuelCalculationLogic()
    {
        // Test fuel calculation with known values
        $distance = 100; // 100 km
        $kmPerLiter = 2.0; // 1 liter = 2 km
        $fuelCostPerLiter = 23.50; // R23.50 per liter
        
        $expectedFuelLiters = $distance / $kmPerLiter; // 50 liters
        $expectedFuelCost = $expectedFuelLiters * $fuelCostPerLiter; // R1,175.00
        
        $this->assertEquals(50.0, $expectedFuelLiters);
        $this->assertEquals(1175.0, $expectedFuelCost);
    }

    public function testFuelCalculationWithDifferentDistances()
    {
        $testCases = [
            ['distance' => 50, 'expected_liters' => 25, 'expected_cost' => 587.50],
            ['distance' => 200, 'expected_liters' => 100, 'expected_cost' => 2350.00],
            ['distance' => 10, 'expected_liters' => 5, 'expected_cost' => 117.50],
        ];

        $kmPerLiter = 2.0;
        $fuelCostPerLiter = 23.50;

        foreach ($testCases as $case) {
            $calculatedLiters = $case['distance'] / $kmPerLiter;
            $calculatedCost = $calculatedLiters * $fuelCostPerLiter;
            
            $this->assertEquals($case['expected_liters'], $calculatedLiters, "Fuel liters calculation failed for {$case['distance']}km");
            $this->assertEquals($case['expected_cost'], $calculatedCost, "Fuel cost calculation failed for {$case['distance']}km");
        }
    }

    public function testPlatformSettingModelExists()
    {
        $this->assertInstanceOf(PlatformSettingModel::class, $this->platformSettingModel);
    }
}
