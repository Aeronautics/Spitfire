<?php

namespace Aeronautics\Spitfire;

use Respect\Rest\Router;

class Application extends Router
{
	public $mapper;
	
	public function __construct($virtualHost=null)
	{
		parent::__construct($virtualHost);
		$this->configure($this);
	}

	public function configure(Router $router)
	{
		$router->isAutoDispatched = false;
		$router->get('/', 'Crimsom Rider...');
	}

	public function __toString()
	{
		return (string) $this->run();
	}
}