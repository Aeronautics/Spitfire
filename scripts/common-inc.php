<?php

/* Bootstrap */
require_once __DIR__ . '/../bootstrap.php';

$config = new Respect\Config\Container(APPLICATION_ROOT.'/conf/manifest.ini');

/**
 * @return Respect\Config\Container
 */
function config() {
    global $config;

    return $config->container;
}

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    fwrite(STDERR, 'This file can not be called directly.' . PHP_EOL);
    exit(1);
}

/**
 * @param   string $message 
 * @param   resource $stream 
 * @return  void
 */
function write($message, $stream = STDOUT)
{
    if ($stream === STDERR && 'xterm-color' == getenv('TERM')) {
        $message = "\033[31m{$message}\033[0m";
    }
    fwrite($stream, $message);
}

/**
 * @param   string|array[optional] $message 
 * @param   resource $stream 
 * @return  void
 */
function writeln($message = '', $stream = STDOUT)
{
    if (is_array($message)) {
        foreach ($message as $value) {
            writeln($value, $stream);
        }
    } else {
        write($message . PHP_EOL, $stream);
    }
}

/**
 * @param   string $message
 * @return  void
 */
function writeln_error($message = '')
{
    writeln($message, STDERR);
}

/**
 * @param   string $question 
 * @param   bool[optional] $trim 
 * @return  string
 */
function ask($question, $trim = true)
{
    writeln($question);
    write('> ');
    $reply = fread(STDIN, 8192);
    if (true === $trim) {
        $reply = trim($reply);
    }
    return $reply;
}

function x2s(SimpleXMLElement $element)
{
    return empty($element) ? null : (string) $element;
}

function x2a(SimpleXMLElement $element)
{
    $json = json_encode($element);
    return json_decode($json, TRUE);
}


function debug($message)
{
    if (getenv('DEBUG')) {
        $args = func_get_args();
        $message = array_shift($args);
        writeln(vsprintf($message, $args));
    }
}
