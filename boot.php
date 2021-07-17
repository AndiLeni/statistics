<?php


use Vectorface\Whip\Whip;



// dashboard addon integration
if (rex::isBackend() && rex_addon::get('dashboard')->isAvailable()) {
    rex_dashboard::addItem(
        rex_dashboard_views_total::factory('stats_views_total', 'Statistik | Seitenaufrufe')
    );
    rex_dashboard::addItem(
        rex_dashboard_browser::factory('stats_browser', 'Statistik | Browser')->setDonut()
    );
    rex_dashboard::addItem(
        rex_dashboard_browsertype::factory('stats_browsertype', 'Statistik | Gerätetypen')->setDonut()
    );
    rex_dashboard::addItem(
        rex_dashboard_os::factory('stats_os', 'Statistik | Betriebssysteme')->setDonut()
    );
    rex_dashboard::addItem(
        rex_dashboard_hour::factory('stats_hour', 'Statistik | Seitenaufrufe: Uhrzeiten')
    );
    rex_dashboard::addItem(
        stats_weekday_dashboard::factory('stats_weekday', 'Statistik | Seitenaufrufe: Wochentage')
    );
}



// NOTICE: EP 'RESPONSE_SHUTDOWN' is not called on madia request
// do actions after content is delivered
rex_extension::register('RESPONSE_SHUTDOWN', function () {

    if (!rex::isBackend()) {

        require_once __DIR__ . '/vendors/autoload.php';

        $addon = rex_addon::get('statistics');
        $log_all = $addon->getConfig('statistics_log_all');

        $response_code = rex_response::getStatus();


        // check responsecode and if non-200 requests should be logged
        if ($response_code == '200 OK' || $log_all) {


            // get ip from visitor, set to 0.0.0.0 when ip can not be determined
            $whip = new Whip();
            $clientAddress = $whip->getValidIpAddress();
            $clientAddress = $clientAddress ? $clientAddress : '0.0.0.0';

            // page url
            $url = $_SERVER['REQUEST_URI'];

            // user agent
            $userAgent = $_SERVER['HTTP_USER_AGENT'];

            $visit = new stats_visit($clientAddress, $url, $userAgent);


            // Track only frontend requests if page url should not be ignored
            if (!rex::isBackend() && !$visit->ignore_visit()) {

                // visit is not a media request, hence either bot or human visitor

                // parse useragent
                $visit->parse_ua();

                if ($visit->is_bot()) {

                    // visitor is a bot
                    $visit->save_bot();
                } else {

                    if ($visit->save_visit()) {

                        // visitor is human
                        // check hash with save_visit, if true then save visit

                        // check if referer exists, if yes safe it
                        if (isset($_SERVER['HTTP_REFERER'])) {
                            $referer = $_SERVER['HTTP_REFERER'];

                            if (!str_starts_with($referer, rex::getServer())) {
                                $visit->save_referer($referer);
                            }
                        }


                        $visit->persist();
                    }
                }
            }
        }
    }
});
