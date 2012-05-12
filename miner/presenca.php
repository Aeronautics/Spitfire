<?php

require_once __DIR__ . '/common-inc.php';

$return = call_user_func(

    function ($time, $index)
    {
        $url = sprintf(
            'http://www2.camara.sp.gov.br/SIP/BaixarXML.aspx?arquivo=Presencas_%s_[%d].xml',
            date('Y_m_d', strtotime($time)),
            $index
        );
        echo $url . PHP_EOL;
        $content = file_get_contents($url);
        if (empty($content)) {
            writeln_error('Sem resultado');
            return false;
        }

        $xml = simplexml_load_string($content);
        if (false === $xml || ($xml instanceof SimpleXMLElement && !$xml->Presencas)) {
            writeln_error('XML inválido');
            return false;
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
        writeln(count($array) . ' resultados');
        return $array;
    },
    isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : date('Y-m-d'),
    isset($_SERVER['argv'][2]) ? $_SERVER['argv'][2] : 0
);
echo PHP_EOL;
writeln('Finished!');
exit($return ? 0 : 1);