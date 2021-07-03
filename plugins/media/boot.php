<?php

$plugin = rex_plugin::get('statistics', 'media');

if (rex_addon::get('media_manager')->isAvailable()) {
    rex_media_manager::addEffect(rex_effect_stats_mm::class);
}


if ($plugin->getConfig('pagestats_media_log_all') == true) {

    $url = $_SERVER['REQUEST_URI'];

    $media_request = new stats_media_request($url);

    if ($media_request->is_media()) {

        $media_request->save_media();
    }
}
