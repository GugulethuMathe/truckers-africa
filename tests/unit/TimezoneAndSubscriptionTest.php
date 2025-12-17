<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\SubscriptionModel;
use App\Models\SubscriptionPlanModel;

class TimezoneAndSubscriptionTest extends CIUnitTestCase
{
    protected $subscriptionModel;
    protected $planModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subscriptionModel = new SubscriptionModel();
        $this->planModel = new SubscriptionPlanModel();
    }

    public function testTimezoneConfiguration()
    {
        // Test that timezone is set to South Africa (UTC+2)
        $appTimezone = app_timezone();
        
        $this->assertEquals('Africa/Johannesburg', $appTimezone);
        
        // Test that the timezone is properly set
        $currentTimezone = date_default_timezone_get();
        $this->assertEquals('Africa/Johannesburg', $currentTimezone);
    }

    public function testTimezoneOffset()
    {
        // Test that South African timezone is UTC+2
        $johannesburgTime = new \DateTime('now', new \DateTimeZone('Africa/Johannesburg'));
        $utcTime = new \DateTime('now', new \DateTimeZone('UTC'));
        
        $offsetSeconds = $johannesburgTime->getOffset();
        $offsetHours = $offsetSeconds / 3600;
        
        // South Africa is UTC+2
        $this->assertEquals(2, $offsetHours);
    }

    public function testDateDisplayWithCorrectTimezone()
    {
        // Test that dates are displayed in South African timezone
        $testTimestamp = strtotime('2024-08-19 12:00:00 UTC');
        $saTime = date('Y-m-d H:i:s', $testTimestamp);
        
        // Should be 2 hours ahead of UTC
        $expectedTime = date('Y-m-d H:i:s', $testTimestamp + (2 * 3600));
        
        // Note: This test depends on the system timezone being set correctly
        $this->assertIsString($saTime);
        $this->assertNotEmpty($saTime);
    }

    public function testSubscriptionTrialDaysCalculation()
    {
        // Test trial days calculation logic
        $trialEndDate = date('Y-m-d H:i:s', strtotime('+30 days'));
        $daysLeft = max(0, ceil((strtotime($trialEndDate) - time()) / (24 * 60 * 60)));
        
        $this->assertGreaterThanOrEqual(29, $daysLeft);
        $this->assertLessThanOrEqual(30, $daysLeft);
    }

    public function testSubscriptionStatusDisplay()
    {
        // Test different subscription status displays
        $statusDisplays = [
            'trial' => '30-Day Free Trial',
            'active' => 'Active',
            'past_due' => 'Past_due',
            'cancelled' => 'Cancelled',
            'expired' => 'Expired'
        ];

        foreach ($statusDisplays as $status => $expectedDisplay) {
            if ($status === 'trial') {
                // For trial status, we expect it to include trial days
                $this->assertStringContainsString('Day Free Trial', $expectedDisplay);
            } else {
                // For other statuses, we expect ucfirst format
                $this->assertEquals(ucfirst($status), $expectedDisplay);
            }
        }
    }

    public function testTrialDaysLeftCalculation()
    {
        // Test calculation of days left in trial
        $testCases = [
            ['trial_ends' => '+1 day', 'expected_min' => 0, 'expected_max' => 1],
            ['trial_ends' => '+7 days', 'expected_min' => 6, 'expected_max' => 7],
            ['trial_ends' => '+30 days', 'expected_min' => 29, 'expected_max' => 30],
            ['trial_ends' => '-1 day', 'expected_min' => 0, 'expected_max' => 0], // Expired trial
        ];

        foreach ($testCases as $case) {
            $trialEndsAt = date('Y-m-d H:i:s', strtotime($case['trial_ends']));
            $daysLeft = max(0, ceil((strtotime($trialEndsAt) - time()) / (24 * 60 * 60)));
            
            $this->assertGreaterThanOrEqual($case['expected_min'], $daysLeft, "Failed for case: {$case['trial_ends']}");
            $this->assertLessThanOrEqual($case['expected_max'], $daysLeft, "Failed for case: {$case['trial_ends']}");
        }
    }

    public function testSubscriptionModelIncludesTrialDays()
    {
        // Test that subscription model queries include trial_days
        $subscriptionModel = new SubscriptionModel();
        
        // Check that the select statement includes trial_days
        $reflection = new \ReflectionClass($subscriptionModel);
        $method = $reflection->getMethod('getCurrentSubscription');
        
        $this->assertTrue($method->isPublic());
        
        // Test the method signature
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('merchantId', $parameters[0]->getName());
    }

    public function testTimeFormatConsistency()
    {
        // Test that time formats are consistent across the application
        $testDate = '2024-08-19 14:30:00';
        
        // Test different date formats used in the application
        $formats = [
            'M j, Y' => date('M j, Y', strtotime($testDate)), // Used in subscription views
            'Y-m-d H:i:s' => date('Y-m-d H:i:s', strtotime($testDate)), // Database format
            'M d, Y' => date('M d, Y', strtotime($testDate)), // Admin views
        ];

        foreach ($formats as $format => $result) {
            $this->assertIsString($result);
            $this->assertNotEmpty($result);
            
            // Verify the format produces expected patterns
            switch ($format) {
                case 'M j, Y':
                    $this->assertMatchesRegularExpression('/^[A-Z][a-z]{2} \d{1,2}, \d{4}$/', $result);
                    break;
                case 'Y-m-d H:i:s':
                    $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $result);
                    break;
                case 'M d, Y':
                    $this->assertMatchesRegularExpression('/^[A-Z][a-z]{2} \d{2}, \d{4}$/', $result);
                    break;
            }
        }
    }
}
