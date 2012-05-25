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
		$router->always('Accept', array(
			'application/json' => $jsonRenderer = function($data) {
				header('Content-Type: application/json');
				return json_encode($data);	
			},
			'.json' => $jsonRenderer,
			'text/xml' => $xmlRenderer = function($data) use ($router) {
				header('Content-Type: text/xml');
				$rootName = key($data);
				$xmlRoot = simplexml_load_string("<$rootName/>");
				$router->xmlConverter($xmlRoot, $data[$rootName]);
				$dom = new \DOMDocument;
				$dom->formatOutput = true;
				$dom->loadXml($xmlRoot->asXML());
				return $dom->saveXML();
			},
			'.xml' => $xmlRenderer,
			'text/html' => $htmlRenderer = function($data) use ($router) {
				header('Content-Type: text/html');
				$rootName = key($data);
				$xmlRoot = simplexml_load_string(<<<XML
					<html>
						<head>
						    <link rel="stylesheet" href="/style/style.less" type="text/less"/>
						    <script src="/js/less-1.1.3.min.js" type="text/javascript"> </script>
						</head>
						<body>
							<article class="$rootName">
								<ul/>
							</article>
						</body>
					</html>
XML
				);
				$router->htmlConverter($xmlRoot->body->article->ul, $data[$rootName]);
				$dom = new \DOMDocument;
				$dom->loadXml($xmlRoot->asXML());
				$dom->formatOutput = true;
				return '<!DOCTYPE html>'.$dom->saveHtml();
			},
			'.html' => $htmlRenderer
		));
	}

	public function xmlConverter($xmlRoot, $data)
	{
		if (is_array($data) || is_object($data))
			foreach ($data as $k => $v) {
				if (is_numeric($k)) {
					$child = $xmlRoot->addChild('item');
					$this->xmlConverter($child, $v);
				} elseif (is_scalar($v) || is_null($v)) {
					$xmlRoot->addAttribute($k, $v);
				} elseif ($k == 'links') {
					foreach ($v as $link) {
						$linkElem = $xmlRoot->addChild('link');
						foreach ($link as $attribute => $attrValue)
							$linkElem->addAttribute($attribute, $attrValue);
					}
				} else {
					$child = $xmlRoot->addChild($k);
					$this->xmlConverter($child, $v);
				}
			}
		return $data;
	}

	public function htmlConverter($xmlRoot, $data)
	{
		if (is_array($data) || is_object($data))
			foreach ($data as $k => $v) {
				if (is_numeric($k)) {
					$child = $xmlRoot->addChild('li');
					$child = $child->addChild('dl');
					$this->htmlConverter($child, $v);
				} elseif (is_scalar($v) || is_null($v)) {
					if ('ul' === $xmlRoot->getName()) {
						$xmlRoot = $xmlRoot->addChild('li');
						$xmlRoot = $xmlRoot->addChild('dl');
					}
					$xmlRoot->addChild('dt', $k);
					$xmlRoot->addChild('dd', $v);
				} elseif ($k == 'links') {
					$xmlRoot->addChild('dt', 'Links');
					$nav = $xmlRoot->addChild('dd');
					$nav = $nav->addChild('ul');
					foreach ($v as $link) {
						$linkElem = $nav->addChild('li');
						$linkElem = $linkElem->addChild('a', $link['title'] ?: $link['href']);
						foreach ($link as $attribute => $attrValue)
							$linkElem->addAttribute($attribute, $attrValue);
					}
				} else {
					$child = $xmlRoot->addChild('li');
					$this->htmlConverter($child, $v);
				}
			}
		return $data;
	}

	public function init()
	{
		$this->configure($this);
		return (string) $this->run();
	}
}