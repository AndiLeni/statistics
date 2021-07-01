<?php

require_once __DIR__ . '/vendors/autoload.php';


use Vectorface\Whip\Whip;



// dashboard addon integration
if (rex::isBackend() && rex_addon::get('dashboard')->isAvailable()) {
    rex_dashboard::addItem(
        rex_dashboard_views_total::factory('stats_views_total', 'Seitenaufrufe')
    );
    rex_dashboard::addItem(
        rex_dashboard_browser::factory('stats_browser', 'Browser')->setDonut()
    );
    rex_dashboard::addItem(
        rex_dashboard_browsertype::factory('stats_browsertype', 'Gerätetypen')->setDonut()
    );
    rex_dashboard::addItem(
        rex_dashboard_os::factory('stats_os', 'Betriebssysteme')->setDonut()
    );
    rex_dashboard::addItem(
        rex_dashboard_hour::factory('stats_hour', 'Uhrzeiten')
    );
    rex_dashboard::addItem(
        stats_weekday_dashboard::factory('stats_weekday', 'Wochentage')
    );
}



// NOTICE: EP prevents media tracking
// do actions after content is delivered
// rex_extension::register('RESPONSE_SHUTDOWN', function () {
// });

// get ip from visitor, set to 0.0.0.0 when ip can not be determined
$whip = new Whip();
$clientAddress = $whip->getValidIpAddress();
$clientAddress = $clientAddress ? $clientAddress : '0.0.0.0';

// page url
$url = $_SERVER['REQUEST_URI'];

// user agent
$userAgent = $_SERVER['HTTP_USER_AGENT'];

$visit = new stats_visit($clientAddress, $url, $userAgent);


// Track only frontend requests if page url should not be ignoredm
if (!rex::isBackend() && !$visit->ignore_visit()) {

    if ($visit->is_media()) {

        // request is a media request and should not be logged to the page-visits
        $visit->save_media();
    } else {

        // visit is not a media request, hence either bot or human visitor

        // parse useragent
        $visit->parse_ua();

        if ($visit->DeviceDetector->isBot()) {

            // visitor is a bot
            $visit->save_bot();
        } else {

            if ($visit->save_visit()) {

                // visitor is human
                // check hash with save_visit, if true then save visit
                $visit->persist();
            }
        }
    }
}
