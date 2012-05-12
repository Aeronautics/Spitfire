<?php
date_default_timezone_set('UTC');
ini_set('display_errors',1);
error_reporting(-1);

chdir(__DIR__.'/../');
set_include_path('src'.PATH_SEPARATOR.get_include_path());

spl_autoload_register(require 'Respect/Loader.php');