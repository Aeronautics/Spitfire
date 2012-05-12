<?php
date_default_timezone_set('America/Sao_Paulo');
ini_set('display_errors',1);
error_reporting(-1);

chdir(__DIR__.'/../');
set_include_path('src'.PATH_SEPARATOR.get_include_path());

spl_autoload_register(require 'Respect/Loader.php');
define('TEST_DATA', realpath('tests/data'));