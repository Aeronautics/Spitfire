<?php
require __DIR__ . '/../bootstrap.php';

$service = new Respect\Config\Container(APPLICATION_ROOT . '/conf/manifest.ini');
echo $service->container->application->init();
