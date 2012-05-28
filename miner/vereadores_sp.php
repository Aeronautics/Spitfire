<?php
/**
 * Lista de vereadores do estado de São Paulo
 * 
 * nome     : Nome do Vereador
 * img      : Foto do Vereador
 * biografia: Pequeno texto biográfico do Vereador
 */

/* Common */
require_once __DIR__ . '/common-inc.php';

/**
 * @param   string $date
 * @return  array
 * @throws  Exception
 */
function vereadores_sp()
{
    $base   = 'http://www1.camara.sp.gov.br';
    $url    = $base . '/vereadores_joomla.asp';
    debug('Baixando "%s"', $url);
    $content = file_get_contents($url);

    if (empty($content)) {
        throw new Exception('Sem resultado em ' . $url);
    }

    debug('Parseando dados ...');

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
            $id                    = $matches['id'];
            $nome                  = utf8_encode(trim($matches['nome']));
            $vereadores[$id]       = new StdClass;
            $vereadores[$id]->nome = preg_replace('/([^(]+)\s?\(.+/', '$1', $nome);
            debug('Identificado vereador: '.$vereadores[$id]->nome);
        },
        $content
    );

    foreach ($vereadores as $id=>$vereador) {
        $url      = $base. '/vereador_joomla2.asp?vereador=' . $id;
        debug('Baixando  "%s"', $url);
        /* @var $data tidy */
        $data   = @file_get_contents($url);
        if (!$data) {
            writeln_error($url);
            continue;
        }
        debug('Parseando');
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
            $vereador->img = $img;
        } else {
            $vereador->img = file_get_contents($img);
        }
        $vereador->biografia = $biografia;
        $vereadores[$id]     = $vereador;
    }
    return $vereadores;
};

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {

    try {
        writeln('Baixando e parseando XML de vereadores em São Paulo');
        $criacao = date('Y-m-d H:i:s');
        $mapper  = config()->relational;
        $return  = vereadores_sp();
        debug('Gravar dados no banco ...');
        foreach ($return as $row) {
            debug('Mantendo vereador: '.$row->nome);
            $vereador = $mapper->politico(array('nome'=>$row->nome))->fetch();
            if (!$vereador) {
                $vereador            = new StdClass;
                $vereador->id        = null;
                $vereador->nome      = $row->nome;
                $mapper->politico->persist($vereador);
                debug('Inserido veriador: '.$row->nome);
                $mapper->flush();
            }
            $vereador->biografia = $row->biografia;
            $vereador->foto      = $row->img;
            debug('Atualizado (foto e biografia): '.$row->nome);
            $mapper->politico->persist($vereador);
        }
        debug('Executando alterações ...');
        $mapper->flush();
        writeln(count($return) . ' vereadores encontrados em São Paulo');
        writeln('Finished!');
        exit (0);

    } catch (Exception $exc) {
        writeln_error($exc->getMessage());
        writeln_error($exc->getTraceAsString());
        exit (1);
    }
}
