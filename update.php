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


// version 3

// remove old plugins 
if (rex_plugin::exists('statistics', 'api')) {

    // copy old config
    rex_config::set("statistics_api_enable", rex_config::get("statistics/api", "statistics_api_enable"));

    // delete uninstall.php of plugin
    rex_file::delete(rex_path::plugin('statistics', 'api', 'uninstall.php'));

    $pl_api = rex_package::get('statistics', 'api');
    $pl_api_manager = rex_package_manager::factory($pl_api);
    $pl_api_manager->delete();
}

if (rex_plugin::exists('statistics', 'media')) {
    rex_config::set("statistics_media_log_all", rex_config::get("statistics/media", "statistics_media_log_all"));
    rex_config::set("statistics_media_log_mm", rex_config::get("statistics/media", "statistics_media_log_mm"));

    rex_file::delete(rex_path::plugin('statistics', 'media', 'uninstall.php'));

    $pl_api = rex_package::get('statistics', 'media');
    $pl_api_manager = rex_package_manager::factory($pl_api);
    $pl_api_manager->delete();
}
