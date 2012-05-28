<?php

/* Common */
require_once __DIR__ . '/../common-inc.php';

/**
* @param string $date
* @return array
* @throws Exception
*/
function votacao($date)
{
    $url = sprintf(
        'http://www2.camara.sp.gov.br/SIP/BaixarXML.aspx?arquivo=Votacoes_%s.xml',
        date('Y', strtotime($date))
    );
    $content = file_get_contents($url);
    if (empty($content)) {
        throw new Exception('Sem resultado em ' . $url);
    }

    $xml = @simplexml_load_string($content);
    if (false === $xml
            || ($xml instanceof SimpleXMLElement && !$xml->Votacao)) {
        throw new Exception('XML inválido em ' . $url);
    }

    $vereadores = array();
    $votacao = array();
    /* @var $item SimpleXMLElement */
    foreach ($xml->Votacao as $key => $item) {
        $object = new stdClass();
        $object->votacaoId = x2s($item['VotacaoID']);
        $object->materia = x2s($item['Materia']);
        $object->tipoVotacao = x2s($item['TipoVotacao']);
        $object->resultado = x2s($item['Resultado']);
        $object->presentes = x2s($item['nPresentes']);
        $object->sim = x2s($item['nSim']);
        $object->nao = x2s($item['nNao']);
        $object->branco = x2s($item['nBranco']);
        $object->abstencao = x2s($item['nAbstencao']);
        $object->nomeDaSessao = x2s($item['NomeDaSessao']);
        $votacao[] = $object;

        foreach ($item->Vereador as $vereador) {
            $object = new stdClass();
            $object->nome = x2s($vereador['NomeParlamentar']);
            $object->idParlamentar = x2s($vereador['IDParlamentar']);
            $object->partido = x2s($vereador['Partido']);
            $object->dataDaSessao = DateTime::createFromFormat('d/m/Y', (string) $vereador['DataDaSessao']);
            $object->voto = x2s($vereador['Voto']);
            $object->acao = x2s($vereador['Acao']);
            $object->votacaoId = x2s($vereador['VotacaoID']);
            $object->nomeDaSessao = x2s($vereador['NomeDaSessao']);
            $object->acao = x2s($vereador['Acao']);
            $vereadores[] = $object;
        }
    }
    writeln(count($votacao) . ' votacao resultados');
    return array(
        'vereadores' => $vereadores,
        'votacao' => $votacao,
    );
};

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {

    try {

        $return = votacao(
            isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : date('Y-m-d')
        );
        writeln('Finished!');
        exit (0);

    } catch (Exception $exc) {
        writeln_error($exc->getMessage());
        writeln_error($exc->getTraceAsString());
        exit (1);
    }

}