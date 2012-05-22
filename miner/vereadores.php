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
    $criacao = date('Y-m-d H:i:s');
    foreach ($xml->Row as $item) {
        list($nome , $partido) = explode('-',x2s($item->VEREADOR));
        $object                = new StdClass;
        //$object->id          = $id_interno;
        $object->nome          = trim($nome);
        $object->partido       = x2s($item->PARTIDO);
        $object->ramal         = x2s($item->RAMAL);
        $object->fax           = x2s($item->FAX);
        $object->sala          = x2s($item->SALA);
        $object->gabinete_id   = x2s($item->GV);
        $object->promovente_id = x2l($item->COD_PRVM_APL);
        $object->criacao       = $criacao;
        $array[]               = $object;
    }
    return $array;
}

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    try {
        $mapper = config()->mapper;
        $return = vereadores();
        writeln(count($return) . ' resultados');
        foreach ($return as $row) {
            $partido = $mapper->partido(array('sigla'=>$row->partido))->fetch();
            if (!$partido) {
                $partido        = new StdClass;
                $partido->sigla = $row->partido;
                $mapper->partido->persist($partido);
                $mapper->flush();
            }
            
        }
        writeln('Finished!');
        exit (0);

    } catch (Exception $exc) {
        writeln_error($exc->getMessage());
        writeln_error($exc->getTraceAsString());
        exit (1);
    }

}
