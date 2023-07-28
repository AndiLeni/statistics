<?php

use AndiLeni\Statistics\Ip2Geo;

rex_sql_table::get(rex::getTable('pagestats_data'))
    ->ensureColumn(new rex_sql_column('type', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('name', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('count', 'int'))
    ->setPrimaryKey(['type', 'name'])
    ->ensure();

rex_sql_table::get(rex::getTable('pagestats_visits_per_day'))
    ->ensureColumn(new rex_sql_column('date', 'date'))
    ->ensureColumn(new rex_sql_column('domain', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('count', 'int'))
    ->setPrimaryKey(['date', 'domain'])
    ->ensure();

rex_sql_table::get(rex::getTable('pagestats_visitors_per_day'))
    ->ensureColumn(new rex_sql_column('date', 'date'))
    ->ensureColumn(new rex_sql_column('domain', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('count', 'int'))
    ->setPrimaryKey(['date', 'domain'])
    ->ensure();

rex_sql_table::get(rex::getTable('pagestats_visits_per_url'))
    ->ensureColumn(new rex_sql_column('hash', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('date', 'date'))
    ->ensureColumn(new rex_sql_column('url', 'varchar(2048)'))
    ->ensureColumn(new rex_sql_column('count', 'int'))
    ->setPrimaryKey(['hash'])
    ->ensure();

rex_sql_table::get(rex::getTable('pagestats_urlstatus'))
    ->ensureColumn(new rex_sql_column('hash', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('url', 'varchar(2048)'))
    ->ensureColumn(new rex_sql_column('status', 'varchar(255)'))
    ->setPrimaryKey(['hash'])
    ->ensure();

rex_sql_table::get(rex::getTable('pagestats_bot'))
    ->ensureColumn(new rex_sql_column('name', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('category', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('producer', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('count', 'int'))
    ->setPrimaryKey(['name', 'category', 'producer'])
    ->ensure();

rex_sql_table::get(rex::getTable('pagestats_hash'))
    ->ensureColumn(new rex_sql_column('hash', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('datetime', 'datetime'))
    ->setPrimaryKey(['hash'])
    ->ensure();

rex_sql_table::get(rex::getTable('pagestats_referer'))
    ->removeColumn('id')
    ->ensureColumn(new rex_sql_column('hash', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('referer', 'varchar(2048)'))
    ->ensureColumn(new rex_sql_column('date', 'date'))
    ->ensureColumn(new rex_sql_column('count', 'int'))
    ->setPrimaryKey(['hash'])
    ->ensure();

rex_sql_table::get(rex::getTable('pagestats_sessionstats'))
    ->ensureColumn(new rex_sql_column('token', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('lastpage', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('lastvisit', 'datetime'))
    ->ensureColumn(new rex_sql_column('visitduration', 'int'))
    ->ensureColumn(new rex_sql_column('pagecount', 'int'))
    ->setPrimaryKey(['token'])
    ->ensure();

// media
rex_sql_table::get(rex::getTable('pagestats_media'))
    ->ensureColumn(new rex_sql_column('url', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('date', 'date'))
    ->ensureColumn(new rex_sql_column('count', 'int'))
    ->setPrimaryKey(['url', 'date'])
    ->ensure();


// api
rex_sql_table::get(rex::getTable('pagestats_api'))
    ->ensureColumn(new rex_sql_column('name', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('date', 'date'))
    ->ensureColumn(new rex_sql_column('count', 'int'))
    ->setPrimaryKey(['name', 'date'])
    ->ensure();

// ip 2 geo database installation
Ip2Geo::updateDatabase();
