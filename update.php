<?php

$this->includeFile(__DIR__ . '/install.php');

// migrate to new tables
if (rex_sql_table::get(rex::getTable('pagestats_dump'))->exists()) {
    $sql = rex_sql::factory();


    // BROWSER
    $query = '
    select browser, count(browser) as "count"
    from rex_pagestats_dump
    group by browser;
    ';
    $data = $sql->getArray($query);
    // dump($data);

    foreach ($data as $e) {
        $sql_insert = 'INSERT INTO ' . rex::getTable('pagestats_data') . ' (type,name,count) VALUES 
        ("browser","' . $e['browser'] . '",' . $e['count'] . ');';

        $res = $sql->setQuery($sql_insert);
    }

    // os
    $query = '
    select os, count(os) as "count"
    from rex_pagestats_dump
    group by os;
    ';
    $data = $sql->getArray($query);
    // dump($data);

    foreach ($data as $e) {
        $sql_insert = 'INSERT INTO ' . rex::getTable('pagestats_data') . ' (type,name,count) VALUES 
        ("os","' . $e['os'] . '",' . $e['count'] . ');';

        $res = $sql->setQuery($sql_insert);
    }

    // browsertype
    $query = '
    select browsertype, count(browsertype) as "count"
    from rex_pagestats_dump
    group by browsertype;
    ';
    $data = $sql->getArray($query);
    // dump($data);

    foreach ($data as $e) {
        $sql_insert = 'INSERT INTO ' . rex::getTable('pagestats_data') . ' (type,name,count) VALUES 
        ("browsertype","' . $e['browsertype'] . '",' . $e['count'] . ');';

        $res = $sql->setQuery($sql_insert);
    }

    // brand
    $query = '
    select brand, count(brand) as "count"
    from rex_pagestats_dump
    group by brand;
    ';
    $data = $sql->getArray($query);
    // dump($data);

    foreach ($data as $e) {
        $sql_insert = 'INSERT INTO ' . rex::getTable('pagestats_data') . ' (type,name,count) VALUES 
        ("brand","' . $e['brand'] . '",' . $e['count'] . ');';

        $res = $sql->setQuery($sql_insert);
    }

    // model
    $query = '
    select model, count(model) as "count"
    from rex_pagestats_dump
    group by model;
    ';
    $data = $sql->getArray($query);
    // dump($data);

    foreach ($data as $e) {
        $sql_insert = 'INSERT INTO ' . rex::getTable('pagestats_data') . ' (type,name,count) VALUES 
        ("model","' . $e['model'] . '",' . $e['count'] . ');';

        $res = $sql->setQuery($sql_insert);
    }

    // hour
    $query = '
    select hour, count(hour) as "count"
    from rex_pagestats_dump
    group by hour;
    ';
    $data = $sql->getArray($query);
    // dump($data);

    foreach ($data as $e) {
        $sql_insert = 'INSERT INTO ' . rex::getTable('pagestats_data') . ' (type,name,count) VALUES 
        ("hour","' . $e['hour'] . '",' . $e['count'] . ');';

        $res = $sql->setQuery($sql_insert);
    }

    // weekday
    $query = '
    select weekday, count(weekday) as "count"
    from rex_pagestats_dump
    group by weekday;
    ';
    $data = $sql->getArray($query);
    // dump($data);

    foreach ($data as $e) {
        $sql_insert = 'INSERT INTO ' . rex::getTable('pagestats_data') . ' (type,name,count) VALUES 
        ("weekday","' . $e['weekday'] . '",' . $e['count'] . ');';

        $res = $sql->setQuery($sql_insert);
    }


    // views per day
    $query = '
    select date, count(date) as "count"
    from rex_pagestats_dump
    group by date;
    ';
    $data = $sql->getArray($query);
    // dump($data);

    foreach ($data as $e) {
        $sql_insert = 'INSERT INTO ' . rex::getTable('pagestats_visits_per_day') . ' (date,count) VALUES 
        ("' . $e['date'] . '",' . $e['count'] . ');';

        $res = $sql->setQuery($sql_insert);
    }


    // views per url per day
    $query = '
    select date, url, count(url) as "count"
    from rex_pagestats_dump
    group by date;
    ';
    $data = $sql->getArray($query);
    // dump($data);

    foreach ($data as $e) {
        $sql_insert = 'INSERT INTO ' . rex::getTable('pagestats_visits_per_url') . ' (hash,date,url,count) VALUES 
        ("' . md5($e['date'] . $e['url']) . '","' . $e['date'] . '","' . $e['url'] . '",' . $e['count'] . ');';

        $res = $sql->setQuery($sql_insert);
    }

    // delete old table
    rex_sql_table::get(rex::getTable('pagestats_dump'))->drop();
}
