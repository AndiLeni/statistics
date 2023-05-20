<?php

rex_sql_table::get(rex::getTable('pagestats_dump'))->drop();
rex_sql_table::get(rex::getTable('pagestats_data'))->drop();
rex_sql_table::get(rex::getTable('pagestats_visits_per_day'))->drop();
rex_sql_table::get(rex::getTable('pagestats_visitors_per_day'))->drop();
rex_sql_table::get(rex::getTable('pagestats_visits_per_url'))->drop();
rex_sql_table::get(rex::getTable('pagestats_bot'))->drop();
rex_sql_table::get(rex::getTable('pagestats_hash'))->drop();
rex_sql_table::get(rex::getTable('pagestats_referer'))->drop();
rex_sql_table::get(rex::getTable('pagestats_visitduration'))->drop();
rex_sql_table::get(rex::getTable('pagestats_lastpage'))->drop();
rex_sql_table::get(rex::getTable('pagestats_pagecount'))->drop();

// media
rex_sql_table::get(rex::getTable('pagestats_media'))->drop();

// api
rex_sql_table::get(rex::getTable('pagestats_api'))->drop();
