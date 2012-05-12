<?php

namespace Aeronautics\Spitfire;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    protected $app;

    public function setUp()
    {
        $this->app = new Application();
    }

    public function tearDown()
    {
        unset($this->app);
    }

    public function testContainer()
    {
        $response = $this->app->dispatch('GET', '/');
        $this->assertEquals('Crimsom Rider...', (string) $response);
    }
}