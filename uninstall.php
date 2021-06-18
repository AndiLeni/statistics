<?php

rex_sql_table::get(rex::getTable('pagestats_views'))->drop();
rex_sql_table::get(rex::getTable('pagestats_browser'))->drop();
rex_sql_table::get(rex::getTable('pagestats_os'))->drop();
rex_sql_table::get(rex::getTable('pagestats_browsertype'))->drop();
rex_sql_table::get(rex::getTable('pagestats_brand'))->drop();
rex_sql_table::get(rex::getTable('pagestats_model'))->drop();
rex_sql_table::get(rex::getTable('pagestats_bot'))->drop();
