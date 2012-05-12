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

    public function testRootDispatch()
    {
        $response = $this->app->dispatch('GET', '/');
        $this->assertEquals('Crimsom Rider...', (string) $response);
    }

    public function testToString()
    {
        $_SERVER['REQUEST_URI']    = '/';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->assertEquals('Crimsom Rider...', (string) $this->app);
    }
}