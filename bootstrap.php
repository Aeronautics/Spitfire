<?php

/* Timezone */
date_default_timezone_set('America/Sao_Paulo');

defined('APPLICATION_ROOT')
    || define('APPLICATION_ROOT', __DIR__);

/* Autoloader */
if (!($autoload = @include __DIR__ . '/vendor/autoload.php')) {

    /* Include path */
    set_include_path(implode(PATH_SEPARATOR, array(
        APPLICATION_ROOT . '/src',
        get_include_path(),
    )));

    /* PEAR autoloader */
    spl_autoload_register(
        function($className) {
            $filename = strtr($className, '\\', DIRECTORY_SEPARATOR) . '.php';
            foreach (explode(PATH_SEPARATOR, get_include_path()) as $path) {
                $path .= DIRECTORY_SEPARATOR . $filename;
                if (is_file($path)) {
                    require_once $path;
                    return true;
                }
            }
            return false;
        }
    );

}
