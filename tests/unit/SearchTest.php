<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Controllers\Search;
use Config\Services;

class SearchTest extends CIUnitTestCase
{
    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new Search();
    }

    public function testSearchControllerExists()
    {
        $this->assertInstanceOf(Search::class, $this->controller);
    }

    public function testSearchFindMethodExists()
    {
        $this->assertTrue(method_exists($this->controller, 'find'));
    }

    public function testSearchIndexMethodExists()
    {
        $this->assertTrue(method_exists($this->controller, 'index'));
    }

    public function testViewMerchantMethodExists()
    {
        $this->assertTrue(method_exists($this->controller, 'viewMerchant'));
    }
}
