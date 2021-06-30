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

// do actions after content is delivered
rex_extension::register('RESPONSE_SHUTDOWN', function() {
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
});






















// Track only frontend requests if page url should not be ignoredm
// if (!rex::isBackend() && !ignore_visit($addon, $url, $clientAddress)) {

//     $userAgent = $_SERVER['HTTP_USER_AGENT'];
//     $dd = new DeviceDetector($userAgent);
//     $dd->parse();

//     // request is for media, save media but do not track page visit
//     if (is_media($_SERVER['REQUEST_URI'])) {
//         $sql = rex_sql::factory();
//         $result = $sql->setQuery('UPDATE ' . rex::getTable('pagestats_media') . ' SET count = count + 1 WHERE url = :url AND date = :date', ['url' => $_SERVER['REQUEST_URI'], 'date' => date('Y-m-d')]);

//         if ($result->getRows() === 0) {
//             $bot = rex_sql::factory();
//             $bot->setTable(rex::getTable('pagestats_media'));
//             $bot->setValue('url', $_SERVER['REQUEST_URI']);
//             $bot->setValue('date', date('Y-m-d'));
//             $bot->setValue('count', 1);
//             $bot->insert();
//         }
//     } else {
//         if ($dd->isBot()) {
//             $botInfo = $dd->getBot();

//             $botname = $botInfo['name'];
//             $botcategory = $botInfo['category'];
//             $botproducer = $botInfo['producer']['name'];

//             $sql = rex_sql::factory();
//             $result = $sql->setQuery('UPDATE ' . rex::getTable('pagestats_bot') . ' SET count = count + 1 WHERE name = :name AND category = :category AND producer = :producer', ['name' => $botname, 'category' => $botcategory, 'producer' => $botproducer]);

//             if ($result->getRows() === 0) {
//                 $bot = rex_sql::factory();
//                 $bot->setTable(rex::getTable('pagestats_bot'));
//                 $bot->setValue('name', $botname);
//                 $bot->setValue('category', $botcategory);
//                 $bot->setValue('producer', $botproducer);
//                 $bot->setValue('count', 1);
//                 $bot->insert();
//             }
//         } else {
//             $clientInfo = $dd->getClient();
//             $osInfo = $dd->getOs();
//             $device = $dd->getDeviceName();
//             $brand = $dd->getBrandName();
//             $model = $dd->getModel();


//             $browser = $clientInfo['name'] ?? 'Undefiniert';
//             $os = $osInfo['name'] ?? 'Undefiniert';
//             $osVer = $osInfo['version'] ?? 'Undefiniert';
//             $device_type = trim($device) != '' ? ucfirst($device) : 'Undefiniert';
//             $brand = trim($brand) != '' ? ucfirst($brand) : 'Undefiniert';
//             $model = trim($model) != '' ? ucfirst($model) : 'Undefiniert';


//             // check if recurring user
//             if ($clientAddress !== false) {
//                 $hash_string = $userAgent . $browser . $os . " " . $osVer . $device_type . $brand . $model . $clientAddress . $url;
//                 $hash = hash('sha1', $hash_string);

//                 $datetime_now = new DateTime();

//                 $sql = rex_sql::factory();
//                 $sql->setTable(rex::getTable('pagestats_hash'));
//                 $sql->setWhere(['hash' => $hash]);
//                 $sql->select();

//                 // if hash is found check if last visit < 'pagestats_visit_duration' minutes then save visit, else dont save visit
//                 if ($sql->getRows() == 1) {
//                     $origin = new DateTime($sql->getValue('datetime'));
//                     $target = new DateTime();
//                     $interval = $origin->diff($target);
//                     $minute_diff = $interval->i + ($interval->h * 60) + ($interval->d * 3600) + ($interval->m * 43800) + ($interval->y * 525599);

//                     // hash was found, if last visit < 'pagestats_visit_duration' min save visit
//                     $max_visit_length = intval($this->getConfig('pagestats_visit_duration'));

//                     if ($minute_diff > $max_visit_length) {
//                         // update set last visit to now
//                         $sql->setQuery('UPDATE ' . rex::getTable('pagestats_hash') . ' SET datetime = :datetime WHERE hash = :hash ', ['hash' => $hash, 'datetime' => $datetime_now->format('Y-m-d H:i:s')]);
//                         save_visit($browser, $os, $osVer, $device_type, $brand, $model, $url, $datetime_now);
//                     }
//                 } else {
//                     // hash was not found, save hash with current datetime, then save visit
//                     $sql = rex_sql::factory();
//                     $sql->setTable(rex::getTable('pagestats_hash'));
//                     $sql->setValue('hash', $hash);
//                     $sql->setValue('datetime', date('Y-m-d H:i:s'));
//                     $sql->insert();

//                     save_visit($browser, $os, $osVer, $device_type, $brand, $model, $url, $datetime_now);
//                 }
//             }
//         }
//     }
// }
