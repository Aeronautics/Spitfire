<?php

namespace Aeronautics\Spitfire\Controllers;

use Respect\Rest\Routable;
use Respect\Relational\Mapper;

class AbstractController implements Routable
{
	public $mapper;

	public function __construct(Mapper $mapper)
	{
		$this->mapper = $mapper;
	}
}