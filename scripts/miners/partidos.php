<?php

/* Common */
require_once __DIR__ . '/../common-inc.php';

try {
    $url = 'http://www.tse.jus.br/partidos/partidos-politicos';
    $html = file_get_contents($url);

    $dom = new DOMDocument('1.0', 'utf-8');
    $dom->preserveWhiteSpace = false;

    if (!@$dom->loadHTML($html)) {
        throw new Exception('Erro ao parsear o DOM em ' . $url);
    }

    $xpath = new DOMXpath($dom);
    $entities = $xpath->query('*//div/table[@class="grid listing"]/tbody/tr[position()>1]');

    $partidos = function () use ($entities, $xpath) {
        $array = array();
        foreach ($entities as $key => $entity) {
            $columns = $entity->childNodes;            
            foreach ($columns as $column) {
                $text = trim($column->textContent);
                if (!empty($text)) {
                    $array[$key][] = trim($column->textContent);
                }
            }            
            array_shift($array[$key]);
            $el = count($array[$key]);
            if ($el > 5 || $el < 5) continue; 
            $combined = array_combine(array('sigla', 'nome', 'deferimento', 'presidente', 'numero'), $array[$key]);
            $combined['resourcePartido'] = trim($xpath->query('.//a', $columns->item(2))->item(0)->attributes->getNamedItem('href')->nodeValue);
            $array[$key] = (object) $combined;
        }
        array_pop($array);
        return $array;
    };
    
    $mapper = config()->relational;
    // yeah, I could do this up there, but I do not want. 
    $partidosInfo = function() use ($partidos, $dom, $mapper) {
        $infos = array();
        foreach ($partidos() as $key => $partido) {
            writeln(">> {$partido->sigla} parsing data from {$partido->resourcePartido}");
            $partidoHTML = file_get_contents($partido->resourcePartido);

            if (!@$dom->loadHTML($partidoHTML)) {
                throw new Exception('Erro ao parsear o DOM em ' . $partido['resourcePartido']);
            }

            $xpath = new DOMXpath($dom);
            $entities = $xpath->query('*//div/table[1]/tbody/tr[position()>1]');
            // parsers rulez ;} lazy too.
            if ($entities->length == 0) {
                $entities = $xpath->query('*//div/table[2]/tbody/tr[position()>1]');
            }
            $partido->sigla = preg_replace('/\s+/', '', $partido->sigla);
            //parsers facts.
            if ($partido->sigla == 'PSTU') {
                $entities = $xpath->query('*//div/table[1]/tbody[2]/tr');
            }

            $mPartido = $mapper->partido(array('sigla' => $partido->sigla))->fetch();
            $message = '';
            if (!$mPartido) {
                $object = new stdClass;
                $object->id = null;
                $object->criacao = date('Y-m-d H:i:s');
                $message = 'Criado partido id: %d';
            } else {
                $object = $mPartido;    
                $message = 'Atualizado partido id: %d';
            }

            $object->nome = trim($entities->item(0)->childNodes->item(2)->textContent);
            $object->sigla = $partido->sigla;
            //$object->presidenteNacional = trim($entities->item(1)->childNodes->item(2)->textContent);
            $object->deferimento = trim($partido->deferimento);
            $object->numero = trim($partido->numero);
            $object->endereco = trim($entities->item(2)->childNodes->item(2)->textContent);
            $object->telefone = trim($entities->item(3)->childNodes->item(2)->textContent);
            $object->site = trim($entities->item(4)->childNodes->item(2)->textContent);
            $object->cep = trim($entities->item(2)->childNodes->item(6)->textContent);
            $object->fax = trim($entities->item(3)->childNodes->item(6)->textContent);

            $emailList = $xpath->query('a', $entities->item(4)->childNodes->item(6));
            if ($emailList->length == 0) {
                $emailList = $xpath->query('div//a', $entities->item(4)->childNodes->item(6));
                if ($emailList->length == 0) {
                    $emailList = $xpath->query('b//a', $entities->item(4)->childNodes->item(6));
                }
            } 
            
            foreach ($emailList as $email) {
                $email = trim($email->textContent);
                if (!empty($email)) {
                    $object->email .= "{$email};";
                }
            }

            $mapper->partido->persist($object);
            //aos que sabem implemente a clausula like e be happy.
            $politico = $mapper->politico(array('nome' => $partido->presidente))->fetch();
            
            if ($politico) {
                $pp = new StdClass;
                $pp->id = null;
                $pp->desde = date('Y-m-d H:i:s');
                $pp->partido_id = $object->id;
                $pp->politico_id = $politico->id;
                $mapper->partido_presidente->persist($pp);
            }

            $mapper->flush();
            writeln(sprintf($message, $object->id));
            $infos[$partido->sigla] = $object;
        }
        return $infos;
    };
    writeln('parseando e dançando...' . PHP_EOL);
    $result = $partidosInfo();
    writeln('total de partidos ' . count($result));
} catch (Exception $e) {
    writeln_error($e->getMessage());
    writeln_error($e->getTraceAsString());
    exit(1);
}
