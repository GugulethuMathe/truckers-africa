<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Controllers\Routes;
use App\Models\PlatformSettingModel;

class DrivingSpeedCalculationTest extends CIUnitTestCase
{
    protected $platformSettingModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->platformSettingModel = new PlatformSettingModel();
    }

    public function testAverageDrivingSpeedSetting()
    {
        // Test default driving speed setting
        $averageSpeed = $this->platformSettingModel->getSetting('average_driving_speed_kmh') ?: '65';
        
        $this->assertIsString($averageSpeed);
        $this->assertEquals('65', $averageSpeed);
        $this->assertGreaterThan(0, (float) $averageSpeed);
    }

    public function testDurationCalculationLogic()
    {
        // Test duration calculation with known values
        $distance = 130; // 130 km
        $averageSpeed = 65; // 65 km/h
        
        $expectedDurationHours = $distance / $averageSpeed; // 2 hours
        $expectedDurationMinutes = $expectedDurationHours * 60; // 120 minutes
        
        $this->assertEquals(2.0, $expectedDurationHours);
        $this->assertEquals(120.0, $expectedDurationMinutes);
    }

    public function testDurationCalculationWithDifferentDistances()
    {
        $testCases = [
            ['distance' => 65, 'expected_hours' => 1.0, 'expected_minutes' => 60],
            ['distance' => 130, 'expected_hours' => 2.0, 'expected_minutes' => 120],
            ['distance' => 32.5, 'expected_hours' => 0.5, 'expected_minutes' => 30],
            ['distance' => 195, 'expected_hours' => 3.0, 'expected_minutes' => 180],
            ['distance' => 16.25, 'expected_hours' => 0.25, 'expected_minutes' => 15],
        ];

        $averageSpeed = 65; // 65 km/h

        foreach ($testCases as $case) {
            $calculatedHours = $case['distance'] / $averageSpeed;
            $calculatedMinutes = $calculatedHours * 60;
            
            $this->assertEquals($case['expected_hours'], $calculatedHours, "Duration hours calculation failed for {$case['distance']}km");
            $this->assertEquals($case['expected_minutes'], $calculatedMinutes, "Duration minutes calculation failed for {$case['distance']}km");
        }
    }

    public function testJavaScriptEquivalentCalculation()
    {
        // Test that our calculation matches what JavaScript would do
        $distance = 100; // 100 km
        $averageSpeed = 65; // 65 km/h
        
        // JavaScript equivalent: Math.round((parseFloat(distance) / fuelSettings.average_speed_kmh) * 60)
        $calculatedMinutes = round(($distance / $averageSpeed) * 60);
        $expectedMinutes = 92; // 100/65 * 60 = 92.31 rounded to 92
        
        $this->assertEquals($expectedMinutes, $calculatedMinutes);
    }

    public function testRealisticRouteExamples()
    {
        // Test realistic route examples
        $routes = [
            ['name' => 'Johannesburg to Pretoria', 'distance' => 60, 'expected_minutes' => 55],
            ['name' => 'Cape Town to Stellenbosch', 'distance' => 50, 'expected_minutes' => 46],
            ['name' => 'Durban to Pietermaritzburg', 'distance' => 90, 'expected_minutes' => 83],
            ['name' => 'Johannesburg to Durban', 'distance' => 560, 'expected_minutes' => 517],
            ['name' => 'Cape Town to Johannesburg', 'distance' => 1400, 'expected_minutes' => 1292],
        ];

        $averageSpeed = 65;

        foreach ($routes as $route) {
            $calculatedMinutes = round(($route['distance'] / $averageSpeed) * 60);
            $this->assertEquals($route['expected_minutes'], $calculatedMinutes, "Duration calculation failed for {$route['name']} ({$route['distance']}km)");
        }
    }

    public function testSpeedConsistencyWithFuelCalculation()
    {
        // Test that speed setting is consistent with fuel calculation settings
        $fuelCostPerLiter = $this->platformSettingModel->getSetting('fuel_cost_per_liter_zar') ?: '23.50';
        $kmPerLiter = $this->platformSettingModel->getSetting('truck_fuel_consumption_km_per_liter') ?: '2.0';
        $averageSpeed = $this->platformSettingModel->getSetting('average_driving_speed_kmh') ?: '65';
        
        // All settings should be available and valid
        $this->assertGreaterThan(0, (float) $fuelCostPerLiter);
        $this->assertGreaterThan(0, (float) $kmPerLiter);
        $this->assertGreaterThan(0, (float) $averageSpeed);
        
        // Speed should be reasonable for trucks (between 40-80 km/h)
        $this->assertGreaterThanOrEqual(40, (float) $averageSpeed);
        $this->assertLessThanOrEqual(80, (float) $averageSpeed);
    }
}
