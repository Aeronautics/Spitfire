<?php

/* Common */
require_once __DIR__ . '/../common-inc.php';

/**
 * @throws  Exception
 * @param   string $time
 * @param   int $index
 * @return  SplFixedArray
 */
function presenca($time, $index)
{
    $url = sprintf(
        'http://www2.camara.sp.gov.br/SIP/BaixarXML.aspx?arquivo=Presencas_%s_[%d].xml',
        date('Y_m_d', strtotime($time)),
        $index
    );
    $content = file_get_contents($url);
    if (empty($content)) {
        throw new Exception('Sem resultado em ' . $url);
    }

    $xml = simplexml_load_string($content);
    if (false === $xml
            || ($xml instanceof SimpleXMLElement && !$xml->Presencas)) {
        throw new Exception('XML inválido em ' . $url);
    }

    $array   = new SplFixedArray(count($xml->Presencas->Vereador));
    $index   = 0;
    foreach ($xml->Presencas->Vereador as $vereador) {
        $object                = new StdClass;
        $object->nome          = (string) $vereador['NomeParlamentar'];
        $object->idParlamentar = (string) $vereador['IDParlamentar'];
        $object->partido       = (string) $vereador['Partido'];
        $object->presente      = (string) $vereador['Presente'];
        $object->idEvento      = (string) $vereador['IDEvento'];
        $object->acaoEvento    = (string) $vereador['AcaoEvento'];
        $object->horaEvento    = DateTime::createFromFormat('d/m/Y H:i:s', (string) $vereador['HoraEvento']);
        $array[$index++]       = $object;
    }
    return $array;
}

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {

    try {

        $return = presenca(
            isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : date('Y-m-d'),
            isset($_SERVER['argv'][2]) ? $_SERVER['argv'][2] : 0
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
