<?php

namespace Aeronautics\Spitfire;

use Respect\Rest\Router;
use Respect\Rest\Request;

class Application extends Router
{
    public $mapper;
    public $vHost;
    public $request;

    public function __construct($virtualHost=null)
    {
        define('VIRTUAL_HOST', 'http://'.$_SERVER['HTTP_HOST'].($virtualHost ?: ''));
        parent::__construct($virtualHost);
    }

    public function dispatchRequest(Request $request=null)
    {
        $this->request = $request;

        return parent::dispatchRequest($request);
    }

    public function configure(Router $router)
    {
        $router->isAutoDispatched = false;
        $arguments = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
        if (count($arguments)) {
            $lastArgument = array_pop($arguments);
            $lastArgumentParts = explode('.', $lastArgument, 2);
            if (isset($lastArgumentParts[1]))
                define('VIRTUAL_EXTENSION', '.'.$lastArgumentParts[1]);
            else
                define('VIRTUAL_EXTENSION', '');
            $arguments[] = $lastArgumentParts[0];
        } else {
            define('VIRTUAL_EXTENSION', '');
        }
        $controller = array();
        $params = array();

        foreach ($arguments as $n => $a)
            if (is_numeric($a) || mb_strtoupper($a) === $a) {
                $params[] = $a;
                $arguments[$n] = '*';
            } else {
                $controller[] = $a;
            }

        $controllerName = implode('\\', array_map('ucwords',$controller)) ?: 'Index';
        if (end($arguments) != '*' && end($arguments) != 'index')
            $controllerName .= 'Collection';

        $controllerClass = __NAMESPACE__.'\\Controllers\\'.$controllerName;

        if (!class_exists($controllerClass))
            die('Class not found: '.$controllerClass);

        $router->any('/'.implode('/', $arguments), $controllerClass, array($router->mapper));

        $jsonRenderer   = function ($data) use ($router) {
            $render = new Renders\Json($router);

            return $render->render($data);
        };
        $xmlRenderer    = function ($data) use ($router) {
            $render = new Renders\Xml($router);

            return $render->render($data);
        };
        $htmlRenderer   = function ($data) use ($router) {
            $render = new Renders\Html($router);

            return $render->render($data);
        };

        $router->always(
            'Accept',
            array(
                'application/json'  => $jsonRenderer,
                '.json'             => $jsonRenderer,
                'text/xml'          => $xmlRenderer,
                '.xml'              => $xmlRenderer,
                'text/html'         => $htmlRenderer,
                '.html'             => $htmlRenderer
            )
        );
    }

    public function init()
    {
        $this->configure($this);

        return (string) $this->run();
    }
}
