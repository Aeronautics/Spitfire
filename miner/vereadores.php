<?php

/* Common */
require_once __DIR__ . '/common-inc.php';

/**
 * @throws  Exception
 * @param   string $time
 * @param   int $index
 * @return  SplFixedArray
 */
function vereadores()
{
    $url = 'http://www2.camara.sp.gov.br/Dados_abertos/vereador/Lista_Vereadores.xml';
    $content = file_get_contents($url);
    if (empty($content)) {
        throw new Exception('Sem resultado em ' . $url);
    }

    $xml = simplexml_load_string($content);
    if (false === $xml
            || ($xml instanceof SimpleXMLElement && !$xml->Row)) {
        throw new Exception('XML inválido em ' . $url);
    }

    $array   = array();
    foreach ($xml->Row as $item) {
        $object = new StdClass;
        $object->nome = x2s($item->VEREADOR);
        $object->gv = x2s($item->GV);
        $object->ramal = x2s($item->RAMAL);
        $object->fax = x2s($item->FAX);
        $object->sala = x2s($item->SALA);
        $array[] = $object;
    }
    return $array;
}

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {

    try {
        $return = vereadores();
        writeln(count($return) . ' resultados');
        writeln('Finished!');
        exit (0);

    } catch (Exception $exc) {
        writeln_error($exc->getMessage());
        writeln_error($exc->getTraceAsString());
        exit (1);
    }

}
