<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Controllers\Home;

class HomePageTest extends CIUnitTestCase
{
    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new Home();
    }

    public function testHomeControllerExists()
    {
        $this->assertInstanceOf(Home::class, $this->controller);
    }

    public function testHomeIndexMethodExists()
    {
        $this->assertTrue(method_exists($this->controller, 'index'));
    }

    public function testHomePageHasPlayStoreButton()
    {
        // Test that the home page contains Play Store related content
        $result = $this->controller->index();

        // Check that the result contains Play Store references
        $this->assertStringContainsString('play.google.com', $result);
        $this->assertStringContainsString('Google Play', $result);
        $this->assertStringContainsString('google-play-badge-official', $result);
    }

    public function testHomePageHasGetStartedButton()
    {
        // Test that the home page still has the Get Started button
        $result = $this->controller->index();
        
        $this->assertStringContainsString('Get Started', $result);
        $this->assertStringContainsString('signup', $result);
    }

    public function testHomePageDoesNotHavePlanRouteButton()
    {
        // Test that the old "Plan a Route" button is no longer present in the hero section
        $result = $this->controller->index();
        
        // The home page should not have "Plan a Route" as a main CTA button
        // (though it might appear in feature descriptions)
        $this->assertStringNotContainsString('href="#features"', $result);
    }
}
