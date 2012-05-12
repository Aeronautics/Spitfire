<?php
require 'bootstrap.php';

$service = new Respect\Config\Container('conf/manifest.ini');
echo $service->container->application;