<?php

rex_sql_table::get(rex::getTable('pagestats_api'))
    ->ensureColumn(new rex_sql_column('name', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('count', 'int'))
    ->ensureColumn(new rex_sql_column('date', 'date'))
    ->ensure();
