<?php

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

