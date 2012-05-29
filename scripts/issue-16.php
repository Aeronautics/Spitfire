<?php

/* Common */
require_once __DIR__ . '/common-inc.php';

try {

    /* @var $pdo PDO */
    $pdo    = config()->relational_connection;

    /* @var $mapper Respect\Relational\Mapper */
    $mapper = config()->relational;
    
    $sql    = 'SHOW TABLES';
    $result = $pdo->query($sql);
    $tables = $result->fetchAll(PDO::FETCH_COLUMN);
    
    $updates = array();
    foreach ($tables as $table) {
        
        $sql    = 'DESCRIBE ' . $table;
        $result = $pdo->query($sql);
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $column) {
            $type   = strtoupper($column['Type']);
            $field  = $column['Field'];
            if (0 === strpos($type, 'VARCHAR')
                    && !in_array($field, array('sigla', 'telefone', 'fax', 'codigo', 'email', 'site', 'abreviacao'))) {
                if (!isset($updates[$table])) {
                    $updates[$table] = array();
                }
                $updates[$table][] = $field;
            }
        }
    }
    
    foreach ($updates as $table => $columns) {
        foreach($mapper->{$table}->fetchAll() as $object) {
            foreach($columns as $colum) {
                if (!empty($object->{$colum})) {
                    $normalized = trim(normalize($object->{$colum}));
                    debug('"%s" para "%s"', $object->{$colum}, $normalized);
                    $object->{$colum} = $normalized;
                }
            }
            $mapper->{$table}->persist($object);
        }
    }

    echo PHP_EOL;

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
