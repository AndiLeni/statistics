<?php

$addon = rex_addon::get('statistics');

// fix table rex_pagestats_referer

$sql = rex_sql::factory();
$query = '
select referer, date, count(count) as "count"
from ' . rex::getTable('pagestats_referer') . '
group by referer, date;
';
$data = $sql->getArray($query);


// delete old table
rex_sql_table::get(rex::getTable('pagestats_referer'))->drop();

// create new tables
$addon->includeFile(__DIR__ . '/install.php');

foreach ($data as $e) {
    $sql_insert = 'INSERT INTO ' . rex::getTable('pagestats_referer') . ' (hash,referer,date,count) VALUES 
    ("' . md5($e['date'] . $e['referer']) . '","' . addslashes($e['referer']) . '","' . $e['date'] . '",' . $e['count'] . ');';

    $res = $sql->setQuery($sql_insert);
}
