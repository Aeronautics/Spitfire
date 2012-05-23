<?php
header("Content-type: text/plain; charset=utf-8");

$url = 'http://www.tse.jus.br/partidos/partidos-politicos';
$html = file_get_contents($url);

try {

    $dom = new DOMDocument('1.0', 'utf-8');
    $dom->preserveWhiteSpace = false;

    if (!$dom->loadHTML($html)) {
        throw new Exception('Erro ao parsear o DOM em ' . $url);
    }

    $xpath = new DOMXpath($dom);
    $entities = $xpath->query('*//div/table[@class="grid listing"]/tbody/tr[position()>1]');

    $partidos = function () use ($entities, $xpath) {
        $array = array();
        foreach ($entities as $key => $entity) {
            $columns = $entity->childNodes;
            $array[$key]['resourcePartido'] = trim($xpath->query('.//a', $columns->item(2))->item(0)->attributes->getNamedItem('href')->nodeValue);
            foreach ($columns as $column) {
                $text = trim($column->textContent);
                if (!empty($text)) {
                    $array[$key][] = trim($column->textContent);
                }
            }
        }

        end($array);
        unset($array[key($array)]);
        return $array;
    };

    $partidosInfo = function() use ($partidos, $dom) {
        $infos = array();
        foreach ($partidos() as $key => $partido) {
            echo "parsing data from {$partido['resourcePartido']}" . PHP_EOL;
            $partidoHTML = file_get_contents($partido['resourcePartido']);

            if (!$dom->loadHTML($partidoHTML)) {
                throw new Exception('Erro ao parsear o DOM em ' . $partido['resourcePartido']);
            }

            $xpath = new DOMXpath($dom);
            $entities = $xpath->query('*//div/table[1]/tbody/tr[position()>1]');
            // parsers rulez :} lazy too.
            if ($entities->length == 0) {
                $entities = $xpath->query('*//div/table[2]/tbody/tr[position()>1]');
                if ($entities->length == 0) { // lets go! just one more.
                    $entities = $xpath->query('*//div/div/table[1]/tbody/tr[position()>1]');
                }
            }
            $object = new stdClass;
            $object->nome = trim($entities->item(0)->childNodes->item(2)->textContent);
            $object->sigla = trim($entities->item(0)->childNodes->item(6)->textContent);
            $object->presidenteNacional = trim($entities->item(1)->childNodes->item(2)->textContent);
            $object->endereco = trim($entities->item(2)->childNodes->item(2)->textContent);
            $object->telefone = trim($entities->item(3)->childNodes->item(2)->textContent);
            $object->site = trim($entities->item(4)->childNodes->item(2)->textContent);
            $object->cep = trim($entities->item(2)->childNodes->item(6)->textContent);
            $object->fax = trim($entities->item(3)->childNodes->item(6)->textContent);

            $emailList = $xpath->query('a', $entities->item(4)->childNodes->item(6));
            foreach ($emailList as $email) {
                $email = trim($email->textContent);
                if (!empty($email)) {
                    $object->email .= "{$email};";
                }
            }
            $infos[$key][] = $object;
        }
        return $infos;
    };

    var_dump($partidosInfo());
} catch (Exception $e) {
    echo $e->getMessage() .PHP_EOL;
    echo $e->getTraceAsString();
}
