<?php

/* Common */
require_once __DIR__ . '/../common-inc.php';

/**
* @param string $date
* @return array
* @throws Exception
*/
function votacao_sp($date)
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
        $object->dataDaSessao = x2s($item['DataDaSessao']);
        $object->nomeDaSessao = x2s($item['NomeDaSessao']);
        $object->notasRodape = x2s($item['NotasRodape']);
        $object->ementa = x2s($item['Ementa']);
        $object->vereadores = $vereadores;
        $votacao[] = $object;
    }
    writeln(count($votacao) . ' votacao resultados');
    return array(
        'vereadores' => $vereadores,
        'votacao' => $votacao,
    );
};

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {

    try {
        $return = votacao_sp(
            isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : date('Y-m-d')
        );

        $mapper = config()->relational;        

        foreach ($return['votacao'] as $row) {
            list($sessao_codigo, $sessao_tipo) = explode('ª', $row->nomeDaSessao);

            $mSessaoTipo = $mapper->sessaoTipo(array('nome' => $sessao_tipo))->fetch();

            if (!$mSessaoTipo) {
                $sessaoTipo = new stdClass;
                $sessaoTipo->id = null;
                $sessaoTipo->nome = $sessao_tipo;
                $sessaoTipo->criacao = date('Y-m-d H:i:s');
                $mapper->sessaoTipo->persist($sessaoTipo);
                $mapper->flush();
                writeln('Criado Sessão Tipo ' . $sessaoTipo->nome);
            } else {
                $sessaoTipo = $mSessaoTipo;
            }

            $mEsfera = $mapper->esfera(array('sigla' => 'CMSP'))->fetch();

            if (!$mEsfera) {
                $esfera = new stdClass;
                $esfera->id = null;
                $esfera->sigla = "CMSP";
                $esfera->nome = "Câmara Municipal de São Paulo";
                $esfera->poder = "Legislativo";
                $esfera->limite = "Município";
                $esfera->criacao = date('Y-m-d H:i:s');
                $mapper->esfera->persist($esfera);
                $mapper->flush();
                writeln('Criado Esfera ' . $esfera->nome);
            } else {
                $esfera = $mEsfera;
            }

            $mSessao = $mapper->sessao(array('codigo' => $sessao_codigo))->fetch();

            if (!$mSessao) {
                $sessao = new stdClass;
                $sessao->id = null;
                $sessao->esfera_id = $esfera->id;
                $sessao->sessaoTipo_id = $sessaoTipo->id;
                $sessao->data = $row->dataDaSessao;
                $sessao->descricao = $row->nomeDaSessao;
                $sessao->codigo = $sessao_codigo;
                $sessao->criacao = date('Y-m-d H:i:s');
                $mapper->sessao->persist($sessao);
                $mapper->flush();
                writeln("Criado Sessão id: " . $sessao->id);
            } else {
                $sessao = $mSessao;
            }

            $mVotacao = $mapper->votacao(array('id_interno' => $row->votacaoId))->fetch();

            if (!$mVotacao) {
                $votacao = new stdClass;
                $votacao->id = null;
                $votacao->sessao_id = $sessao->id;
                $votacao->id_interno = $row->votacaoId;
                $votacao->materia = $row->materia;
                $votacao->data = $row->dataDaSessao;
                $votacao->nome = $row->nomeDaSessao;
                $votacao->tipo_votacao = $row->tipoVotacao;
                $votacao->resultado = $row->resultado;
                $votacao->ementa = $row->ementa;
                $votacao->notas_rodape = $row->notasRodape;
                $mapper->votacao->persist($votacao);
                $mapper->flush();
                writeln("Criado Votação id: " . $votacao->id);
            } else {
                $votacao = $mVotacao;
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