<?php

// rex_sql_table::get(rex::getTable('pagestats_dump'))
//     ->ensureColumn(new rex_sql_column('id', 'int', false, null, 'auto_increment'))
//     ->ensureColumn(new rex_sql_column('browser', 'varchar(255)'))
//     ->ensureColumn(new rex_sql_column('os', 'varchar(255)'))
//     ->ensureColumn(new rex_sql_column('browsertype', 'varchar(255)'))
//     ->ensureColumn(new rex_sql_column('brand', 'varchar(255)'))
//     ->ensureColumn(new rex_sql_column('model', 'varchar(255)'))
//     ->ensureColumn(new rex_sql_column('url', 'text'))
//     ->ensureColumn(new rex_sql_column('date', 'date'))
//     ->ensureColumn(new rex_sql_column('hour', 'int'))
//     ->ensureColumn(new rex_sql_column('weekday', 'int'))
//     ->setPrimaryKey('id')
//     ->ensure();

rex_sql_table::get(rex::getTable('pagestats_data'))
    ->ensureColumn(new rex_sql_column('type', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('name', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('count', 'int'))
    ->setPrimaryKey(['type', 'name'])
    ->ensure();

rex_sql_table::get(rex::getTable('pagestats_visits_per_day'))
    ->ensureColumn(new rex_sql_column('date', 'date'))
    ->ensureColumn(new rex_sql_column('count', 'int'))
    ->setPrimaryKey(['date'])
    ->ensure();

rex_sql_table::get(rex::getTable('pagestats_visits_per_url'))
    ->ensureColumn(new rex_sql_column('hash', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('date', 'date'))
    ->ensureColumn(new rex_sql_column('url', 'varchar(2048)'))
    ->ensureColumn(new rex_sql_column('count', 'int'))
    ->setPrimaryKey(['hash'])
    ->ensure();

rex_sql_table::get(rex::getTable('pagestats_bot'))
    ->ensureColumn(new rex_sql_column('id', 'int', false, null, 'auto_increment'))
    ->ensureColumn(new rex_sql_column('name', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('category', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('producer', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('count', 'int'))
    ->setPrimaryKey('id')
    ->ensure();

rex_sql_table::get(rex::getTable('pagestats_hash'))
    ->ensureColumn(new rex_sql_column('id', 'int', false, null, 'auto_increment'))
    ->ensureColumn(new rex_sql_column('hash', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('datetime', 'datetime'))
    ->setPrimaryKey('id')
    ->ensure();

rex_sql_table::get(rex::getTable('pagestats_referer'))
    ->ensureColumn(new rex_sql_column('id', 'int', false, null, 'auto_increment'))
    ->ensureColumn(new rex_sql_column('referer', 'text'))
    ->ensureColumn(new rex_sql_column('count', 'int'))
    ->ensureColumn(new rex_sql_column('date', 'date'))
    ->setPrimaryKey('id')
    ->ensure();
