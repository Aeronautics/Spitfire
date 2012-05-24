<?php

/* Common */
require_once __DIR__ . '/common-inc.php';

$criacao = date('Y-m-d H:i:s');

/**
 * @throws  Exception
 * @param   string $time
 * @param   int $index
 * @return  SplFixedArray
 */
function vereadores()
{
    global $criacao;

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
        list($nome , $partido) = explode('-',x2s($item->VEREADOR));
        $object                = new StdClass;
        //$object->id          = $id_interno;
        $object->nome          = trim($nome);
        $object->nome_parlamentar = x2s($item->NOME_PARLAMENTAR);
        $object->partido       = x2s($item->PARTIDO);
        $object->ramal         = x2s($item->RAMAL);
        $object->fax           = x2s($item->FAX);
        $object->sala          = x2s($item->SALA);
        $object->gabinete_id   = x2s($item->GV);
        $object->promovente_id = x2s($item->COD_PRVM_APL);
        $object->criacao       = $criacao;
        $array[]               = $object;
    }
    return $array;
}

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    try {
        $mapper = config()->relational;
        writeln('Baixando e parseando XML...');
        $return = vereadores();
        writeln(count($return) . ' resultados');
        foreach ($return as $row) {
            $partido = $mapper->partido(array('sigla'=>$row->partido))->fetch();
            if (!$partido) { // Partido nao existe, criar
                $partido        = new StdClass;
                $partido->id    = null;
                $partido->sigla = $row->partido;
                $partido->criacao = $criacao;
                $mapper->partido->persist($partido);
                $mapper->flush();
                writeln('Criado partido: '.$partido->id.' - '.$partido->sigla);
            }
            $nome_alt = $row->nome_parlamentar; // TODO: gravar nome alternativo na tabela 'politicoNome'
            unset($row->nome_parlamentar);
            $politico = $mapper->politico(array('nome'=>$row->nome))->fetch();
            if (!$politico) {
                $mapper->politico->persist($politico = (object) array('id' => null, 'nome' => $row->nome));
                writeln('Criado politico: '.$politico->nome);
            } else {
                $politico->nome    = $row->nome;
                $politico->criacao = $criacao;
                $mapper->politico->persist($politico);
                writeln('Atualizado politico: '.$politico->nome);
            }

            $mapper->politico->persist($politico);
            $mapper->flush();
            $criteria        = array('politico_id'=>$politico->id, 'partido_id'=>$partido->id);
            $politicoPartido = $mapper->politico_partido($criteria)->fetch();
            if (!$politicoPartido) {// Relacionamento de politico<->partido nao existe
                $politicoPartido = new StdClass;
                $politicoPartido->id = null;
                $politicoPartido->partido_id  = $partido->id;
                $politicoPartido->politico_id = $politico->id;
                $politicoPartido->desde       = $criacao;
                $mapper->politico_partido->persist($politicoPartido);
                $mapper->flush();
                writeln('Associado politico ao partido');
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