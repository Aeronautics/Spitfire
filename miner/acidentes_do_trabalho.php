<?php

/* Common */
require_once __DIR__ . '/common-inc.php';

/**
 * @throws  Exception
 * @param   string $time
 * @param   int $index
 * @return  SplFixedArray
 */
function acidentesDoTrabalho($date)
{
    $url = sprintf('http://api.dataprev.gov.br/previdencia/anuario/%s/acidentes-do-trabalho.xml',
        date('Y', strtotime($date))
    );
    $content = file_get_contents($url);
    if (empty($content)) {
        throw new Exception('Sem resultado em ' . $url);
    }

    $xml = simplexml_load_string($content);
    if (false === $xml
            || ($xml instanceof SimpleXMLElement && !$xml->registro)) {
        throw new Exception('XML inválido em ' . $url);
    }

    $array   = array();
    writeln('tem dados mais importantes por ai...');
    foreach ($xml->registro as $item) {
        // irrelevant data
    }
    return $array;
}

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {

    try {
        $return = acidentesDoTrabalho(
            isset($argv[1]) ? $argv[1] : date('Y-m-d')
        );
        writeln(count($return) . ' resultados');
        writeln('Finished!');
        exit (0);

    } catch (Exception $exc) {
        writeln_error($exc->getMessage());
        writeln_error($exc->getTraceAsString());
        exit (1);
    }

}
