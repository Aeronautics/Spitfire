<?php

/* Timezone */
date_default_timezone_set('America/Sao_Paulo');


/* Include path */
chdir(__DIR__);
set_include_path(implode(PATH_SEPARATOR, array(
    __DIR__ . '/src',
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
