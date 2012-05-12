<?php

/* Common */
require_once __DIR__ . '/common-inc.php';

/**
 * @param   string $date
 * @return  array
 * @throws  Exception
 */
function vereadores_sp()
{
    $url = 'http://www1.camara.sp.gov.br/vereadores_joomla.asp';
    $content = file_get_contents($url);
    if (empty($content)) {
        throw new Exception('Sem resultado em ' . $url);
    }
    $lines      = explode(PHP_EOL, $content);
    $content    = $lines[11];
    $content    = preg_replace('/>/', ">\n", $content);
    $content    = strip_tags($content, '<a>');
    $content    = strtr($content, array(
        "\n\n" => "\n",
        '\'' => '"',
    ));
    $content    = preg_replace('/>/', ">\n", $content);
    $vereadores = array();
    $content    = preg_replace_callback(
        '@(<a href="vereador_joomla2\.asp\?vereador=(?P<id>[0-9]+)">(?P<nome>[^<]+)</a>)@m',
        function ($matches) use (&$vereadores) {
            $id                 = $matches['id'];
            $nome               = utf8_encode(trim($matches['nome']));
            $vereadores[$id]    = array(
                'nome'  => preg_replace('/([^(]+)\s?\(.+/', '$1', $nome)
            );
        },
        $content
    );
    return $vereadores;
};

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {

    try {

        $return = vereadores_sp();
        writeln(count($return) . ' vereadores encontrados em São Paulo');
        writeln('Finished!');
        exit (0);

    } catch (Exception $exc) {
        writeln_error($exc->getMessage());
        writeln_error($exc->getTraceAsString());
        exit (1);
    }

}
