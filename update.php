<?php

$addon = rex_addon::get('statistics');


// version 3 migrations

// remove old plugins 
if (rex_plugin::exists('statistics', 'api')) {

    // copy old config
    if (rex_config::has("statistics/api", "statistics_api_enable")) {
        rex_config::set("statistics_api_enable", rex_config::get("statistics/api", "statistics_api_enable"));
    }

    // delete uninstall.php of plugin
    rex_file::delete(rex_path::plugin('statistics', 'api', 'uninstall.php'));

    $pl_api = rex_package::get('statistics', 'api');
    $pl_api_manager = rex_package_manager::factory($pl_api);
    $pl_api_manager->delete();
}

if (rex_plugin::exists('statistics', 'media')) {

    if (rex_config::has("statistics/media", "statistics_media_log_all")) {
        rex_config::set("statistics_media_log_all", rex_config::get("statistics/media", "statistics_media_log_all"));
    }
    if (rex_config::has("statistics/media", "statistics_media_log_mm")) {
        rex_config::set("statistics_media_log_mm", rex_config::get("statistics/media", "statistics_media_log_mm"));
    }

    rex_file::delete(rex_path::plugin('statistics', 'media', 'uninstall.php'));

    $pl_api = rex_package::get('statistics', 'media');
    $pl_api_manager = rex_package_manager::factory($pl_api);
    $pl_api_manager->delete();
}
