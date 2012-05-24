<?php
require __DIR__ . '/../bootstrap.php';

$service = new Respect\Config\Container('conf/manifest.ini');
echo $service->container->application;
