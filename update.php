<?php

$addon = rex_addon::get('statistics');

// create tables
$addon->includeFile(__DIR__ . '/install.php');


// version 3 migrations

// copy old config settings
if (rex_config::has("statistics/api", "statistics_api_enable")) {
    rex_config::set("statistics", "statistics_api_enable", rex_config::get("statistics/api", "statistics_api_enable"));
}

if (rex_config::has("statistics/media", "statistics_media_log_all")) {
    rex_config::set("statistics", "statistics_media_log_all", rex_config::get("statistics/media", "statistics_media_log_all"));
}

if (rex_config::has("statistics/media", "statistics_media_log_mm")) {
    rex_config::set("statistics", "statistics_media_log_mm", rex_config::get("statistics/media", "statistics_media_log_mm"));
}

// remove plugins
rex_dir::delete(rex_path::addon('statistics', 'plugins'));
rex_package_manager::synchronizeWithFileSystem();
