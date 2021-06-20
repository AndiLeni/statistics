<?php

rex_sql_table::get(rex::getTable('pagestats_views'))
    ->ensureColumn(new rex_sql_column('url', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('date', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('count', 'int'))
    ->ensure();

rex_sql_table::get(rex::getTable('pagestats_browser'))
    ->ensureColumn(new rex_sql_column('name', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('count', 'int'))
    ->ensure();

rex_sql_table::get(rex::getTable('pagestats_os'))
    ->ensureColumn(new rex_sql_column('name', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('count', 'int'))
    ->ensure();

rex_sql_table::get(rex::getTable('pagestats_browsertype'))
    ->ensureColumn(new rex_sql_column('name', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('count', 'int'))
    ->ensure();

rex_sql_table::get(rex::getTable('pagestats_brand'))
    ->ensureColumn(new rex_sql_column('name', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('count', 'int'))
    ->ensure();

rex_sql_table::get(rex::getTable('pagestats_model'))
    ->ensureColumn(new rex_sql_column('name', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('count', 'int'))
    ->ensure();

rex_sql_table::get(rex::getTable('pagestats_bot'))
    ->ensureColumn(new rex_sql_column('name', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('category', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('producer', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('count', 'int'))
    ->ensure();

rex_sql_table::get(rex::getTable('pagestats_dump'))
    ->ensureColumn(new rex_sql_column('browser', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('os', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('browsertype', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('brand', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('model', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('url', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('date', 'varchar(255)'))
    ->ensure();
