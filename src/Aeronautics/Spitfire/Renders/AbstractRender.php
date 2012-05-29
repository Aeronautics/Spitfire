<?php

namespace Aeronautics\Spitfire\Renders;

use Respect\Rest\Router;

abstract class AbstractRender
{

    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function getRouter()
    {
        return $this->router;
    }

    public function render(array $data)
    {
        header('Content-type: ' . $this->getMimetype());

        return $this->getContent($data);
    }

    abstract public function getContent(array $data);

    abstract public function getMimetype();

}

