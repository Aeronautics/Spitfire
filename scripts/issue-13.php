<?php

/* Common */
require_once __DIR__ . '/common-inc.php';

try {

    $mapper = config()->relational;
    foreach ($mapper->politico->fetchAll() as $politico) {
        if (!$politico->foto 
            || ($politico->foto && ctype_print($politico->foto))) {
            continue;
        }
        writeln('Atualizando foto de ' . $politico->nome);
        $politico->foto = base64_encode($politico->foto);
        $mapper->politico->persist($politico);
    }

    writeln('Enviando alterações para o banco de dados');
    $mapper->flush();

} catch (Exception $e) {
    writeln_error($e->getMessage());
    writeln_error($e->getTraceAsString());
    exit(1);
}

writeln();
writeln('Fim do script!');
exit (0);
