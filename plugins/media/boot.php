<?php


// request url
$url = $_SERVER['REQUEST_URI'];


$media_request = new stats_media_request($url);

if ($media_request->is_media()) {

    $media_request->save_media();
}
