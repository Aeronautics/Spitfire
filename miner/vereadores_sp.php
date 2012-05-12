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
    $config = new Respect\Config\Container('conf/manifest.ini');

    /* @var $mapper Respect\Relational\Mapper */
    $mapper =  $config->container->relational;

    $base   = 'http://www1.camara.sp.gov.br';
    $url    = $base . '/vereadores_joomla.asp';
    debug('Fetching from url "%s"', $url);
    $content = file_get_contents($url);

    if (empty($content)) {
        throw new Exception('Sem resultado em ' . $url);
    }

    debug('Parsing fetched data');

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

    foreach (array_keys($vereadores) as $id) {
        $vereador =& $vereadores[$id];
        $url = $base. '/vereador_joomla2.asp?vereador=' . $id;
        debug('Fetching data of "%s"', $url);
        /* @var $data tidy */
        $data   = file_get_contents($url);
        debug('Parsing data');
        $data   = tidy_repair_string($data, array('output-xml' =>true));
        $data   = str_replace('&nbsp;', '', $data);
        /* @var $data SimpleXMLElement */
        $xml    = simplexml_load_string($data);
        $img    = $base . '/'
                . $xml  ->body
                        ->div[0]
                        ->table[0]
                        ->tr[0]
                        ->td[0]
                        ->table[0]
                        ->tr[0]
                        ->td[0]
                        ->img['src'];
        $biografia  = $xml->body
                        ->div[0]
                        ->table[0]
                        ->tr[1]
                        ->td;
        $biografia  = $biografia->asXml();
        $biografia  = str_replace(
            '<p class="biografia_vereador_titulo">Biografia</p>',
            '',
            $biografia
        );
        $biografia  = str_replace(PHP_EOL, ' ', $biografia);
        $biografia  = str_replace('<', PHP_EOL . '<', $biografia);
        $biografia  = strip_tags($biografia);
        $biografia  = preg_replace('/\n+/', PHP_EOL, $biografia);
        $biografia  = preg_replace('/\n\s/', PHP_EOL, $biografia);
        $biografia  = preg_replace('/\n+/', PHP_EOL, $biografia);

        if (getenv('DEBUG') > 1) {
            $vereador['img'] = $img;
        } else {
            $vereador['img'] = file_get_contents($img);
        }
        $vereador['biografia'] = $biografia;
        unset($vereador);
    }
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
