<?php

use AndiLeni\Statistics\MediaRequest;
use AndiLeni\Statistics\Visit;
use Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException;
use Vectorface\Whip\Whip;



if (rex::isBackend()) {
    $addon = rex_addon::get('statistics');


    // permissions
    rex_perm::register('statistics[]', null);
    rex_perm::register('statistics[settings]', null, rex_perm::OPTIONS);


    rex_view::addCssFile($addon->getAssetsUrl('datatables.min.css'));
    rex_view::addCssFile($addon->getAssetsUrl('statistics.css'));

    rex_view::addJsFile($addon->getAssetsUrl('echarts.min.js'));
    rex_view::addJsFile($addon->getAssetsUrl('dark.js'));
    rex_view::addJsFile($addon->getAssetsUrl('shine.js'));
    rex_view::addJsFile($addon->getAssetsUrl('datatables.min.js'));

    rex_view::addJsFile($addon->getAssetsUrl('statistics.js'));

    $pagination_scroll = $addon->getConfig('statistics_scroll_pagination');
    if ($pagination_scroll == 'panel') {
        rex_view::addJsFile($addon->getAssetsUrl('statistics_scroll_container.js'));
    } elseif ($pagination_scroll == 'table') {
        rex_view::addJsFile($addon->getAssetsUrl('statistics_scroll_table.js'));
    }
}


// set variable to check in EP whether the visit is coming from a logged-in user or not
if (rex::isFrontend()) {
    $addon = rex_addon::get('statistics');
    $ignore_backend_loggedin = $addon->getConfig('statistics_ignore_backend_loggedin');

    if ($ignore_backend_loggedin) {
        $statistics_has_backend_login = rex_backend_login::hasSession();
    } else {
        $statistics_has_backend_login = false;
    }

    rex_login::startSession();

    $token = rex_session("statistics_token", "string", null);

    if ($token === null) {
        $bytes = random_bytes(20);
        $token = bin2hex($bytes);

        rex_set_session('statistics_token', $token);
    }
} else {
    $statistics_has_backend_login = true;
    $token = "";
}



// NOTICE: EP 'RESPONSE_SHUTDOWN' is not called on madia request
// do actions after content is delivered
rex_extension::register('RESPONSE_SHUTDOWN', function () use ($statistics_has_backend_login, $token) {

    if (rex::isFrontend()) {

        $addon = rex_addon::get('statistics');
        $log_all = $addon->getConfig('statistics_log_all');
        $ignore_backend_loggedin = $addon->getConfig('statistics_ignore_backend_loggedin');


        // return when visit is coming from a logged-in user
        if ($ignore_backend_loggedin && $statistics_has_backend_login) {
            return;
        }


        // domain
        try {
            $domain = rex::getRequest()->getHost();
        } catch (SuspiciousOperationException $e) {
            $domain = 'undefined';
        }

        // page url
        $url = $domain . rex::getRequest()->getRequestUri();



        if (rex_config::get("statistics", "statistics_rec_session_stats", false) === true && rex::getRequest()->getRequestUri() != "/favicon.ico") {
            $sql = rex_sql::factory();
            $sql->setQuery("INSERT INTO " . rex::getTable('pagestats_visitduration') . " (token, lastvisit, duration) VALUES (:token, NOW(), 0) ON DUPLICATE KEY UPDATE duration = duration + (NOW() - lastvisit), lastvisit = NOW();", [":token" => $token]);

            // last visited page
            $sql = rex_sql::factory();
            $sql->setQuery("INSERT INTO " . rex::getTable('pagestats_lastpage') . " (token, url) VALUES (:token, :url) ON DUPLICATE KEY UPDATE url = VALUES(url);", [":token" => $token, ":url" => $url]);

            // number pages visited
            $sql = rex_sql::factory();
            $sql->setQuery("INSERT INTO " . rex::getTable('pagestats_pagecount') . " (token, count) VALUES (:token, 1) ON DUPLICATE KEY UPDATE count = count + 1;", [":token" => $token]);
        }


        $response_code = rex_response::getStatus();


        // check responsecode and if non-200 requests should be logged
        if ($response_code == rex_response::HTTP_OK || $log_all) {


            // get ip from visitor, set to 0.0.0.0 when ip can not be determined
            $whip = new Whip();
            $clientAddress = $whip->getValidIpAddress();
            $clientAddress = $clientAddress ? $clientAddress : '0.0.0.0';


            // optionally ignore url parameters
            if ($addon->getConfig('statistics_ignore_url_params')) {
                $url = Visit::removeUrlParameters($url);
            }

            // user agent
            $userAgent = rex_server('HTTP_USER_AGENT', 'string', '');

            $visit = new Visit($clientAddress, $url, $userAgent, $domain);


            // Track only frontend requests if page url should not be ignored
            // ignore requests with empty user agent
            if (!rex::isBackend() && $userAgent != '' && !$visit->shouldIgnore()) {

                // visit is not a media request, hence either bot or human visitor

                // parse useragent
                $visit->parseUA();

                if ($visit->isBot()) {

                    // visitor is a bot
                    $visit->saveBot();
                } else {

                    if ($visit->shouldSaveVisit()) {

                        // visitor is human
                        // check hash with save_visit, if true then save visit

                        // check if referer exists, if yes safe it
                        $referer = rex_server('HTTP_REFERER', 'string', '');
                        if ($referer != '') {
                            $referer = urldecode($referer);

                            if (!str_starts_with($referer, rex::getServer())) {
                                $visit->saveReferer($referer);
                            }
                        }


                        // check if unique visitor
                        if ($visit->shouldSaveVisitor()) {

                            // save visitor
                            $visit->persistVisitor();
                        }


                        $visit->persist();
                    }
                }
            }
        }
    }
});


// media
if (rex::isBackend()) {

    if (rex_addon::get('media_manager')->isAvailable()) {
        rex_media_manager::addEffect(rex_effect_stats_mm::class);
    }
} else {

    rex_extension::register('MEDIA_MANAGER_AFTER_SEND', function () {
        $addon = rex_addon::get('statistics');

        if ($addon->getConfig('statistics_media_log_all') == true) {

            $url = rex_server('REQUEST_URI', 'string', '');

            $media_request = new MediaRequest($url);

            if ($media_request->isMedia()) {

                $media_request->save();
            }
        }
    });
}
