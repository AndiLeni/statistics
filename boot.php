<?php

require_once __DIR__ . '/vendors/autoload.php';


use DeviceDetector\DeviceDetector;
use Vectorface\Whip\Whip;


$addon = rex_addon::get('stats');



function ignore_visit($addon)
{
    // check if visit should be ignored
    $ignored_paths = $addon->getConfig('pagestats_ignored_paths');

    if ($ignored_paths == '') {
        return false;
    }

    $ignored_paths = explode("\n", str_replace("\r", "", $ignored_paths));

    foreach ($ignored_paths as $path) {
        if (str_starts_with($_SERVER['REQUEST_URI'], $path)) {
            return true;
        }
    }
    return false;
}




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


// Track only frontend requests if page url should not be ignored
if (!rex::isBackend() && !ignore_visit($addon)) {

    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $dd = new DeviceDetector($userAgent);
    $dd->parse();

    if ($dd->isBot()) {
        $botInfo = $dd->getBot();

        $botname = $botInfo['name'];
        $botcategory = $botInfo['category'];
        $botproducer = $botInfo['producer']['name'];

        $sql = rex_sql::factory();
        $result = $sql->setQuery('UPDATE ' . rex::getTable('pagestats_bot') . ' SET count = count + 1 WHERE name = :name AND category = :category AND producer = :producer', ['name' => $botname, 'category' => $botcategory, 'producer' => $botproducer]);

        if ($result->getRows() === 0) {
            $bot = rex_sql::factory();
            $bot->setTable(rex::getTable('pagestats_bot'));
            $bot->setValue('name', $botname);
            $bot->setValue('category', $botcategory);
            $bot->setValue('producer', $botproducer);
            $bot->setValue('count', 1);
            $bot->insert();
        }
    } else {
        $clientInfo = $dd->getClient();
        $osInfo = $dd->getOs();
        $device = $dd->getDeviceName();
        $brand = $dd->getBrandName();
        $model = $dd->getModel();


        $browser = $clientInfo['name'] ?? 'Undefiniert';
        $os = $osInfo['name'] ?? 'Undefiniert';
        $osVer = $osInfo['version'] ?? 'Undefiniert';
        $device_type = trim($device) != '' ? ucfirst($device) : 'Undefiniert';
        $brand = trim($brand) != '' ? ucfirst($brand) : 'Undefiniert';
        $model = trim($model) != '' ? ucfirst($model) : 'Undefiniert';

        $url = $_SERVER['REQUEST_URI'];


        $whip = new Whip();
        $clientAddress = $whip->getValidIpAddress();

        // check if recurring user
        if ($clientAddress !== false) {
            $hash_string = $userAgent . $browser . $os . " " . $osVer . $device_type . $brand . $model . $clientAddress . $url;
            $hash = hash('sha1', $hash_string);

            $datetime_now = new DateTime();

            $sql = rex_sql::factory();
            $sql->setTable(rex::getTable('pagestats_hash'));
            $sql->setWhere(['hash' => $hash]);
            $sql->select();

            // if hash is found check if last visit < 'pagestats_visit_duration' minutes then save visit, else dont save visit
            if ($sql->getRows() == 1) {
                $origin = new DateTime($sql->getValue('datetime'));
                $target = new DateTime();
                $interval = $origin->diff($target);
                $minute_diff = $interval->i + ($interval->h * 60) + ($interval->d * 3600) + ($interval->m * 43800) + ($interval->y * 525599);

                // hash was found, if last visit < 'pagestats_visit_duration' min save visit
                $max_visit_length = intval($this->getConfig('pagestats_visit_duration'));
                if ($minute_diff > $max_visit_length) {
                    // update set last visit to now
                    $sql->setQuery('UPDATE ' . rex::getTable('pagestats_hash') . ' SET datetime = :datetime WHERE hash = :hash ', ['hash' => $hash, 'datetime' => $datetime_now->format('Y-m-d H:i:s')]);

                    $sql = rex_sql::factory();
                    $sql->setTable(rex::getTable('pagestats_dump'));
                    $sql->setValue('browser', $browser);
                    $sql->setValue('os', $os . " " . $osVer);
                    $sql->setValue('browsertype', $device_type);
                    $sql->setValue('brand', $brand);
                    $sql->setValue('model', $model);
                    $sql->setValue('url', $url);
                    $sql->setValue('date', $datetime_now->format('Y-m-d'));
                    $sql->setValue('hour', $datetime_now->format('H'));
                    $sql->setValue('weekday', $datetime_now->format('N'));
                    $sql->insert();
                }
            } else {
                // hash was not found, save hash with current datetime, then save visit
                $sql = rex_sql::factory();
                $sql->setTable(rex::getTable('pagestats_hash'));
                $sql->setValue('hash', $hash);
                $sql->setValue('datetime', date('Y-m-d H:i:s'));
                $sql->insert();

                $sql = rex_sql::factory();
                $sql->setTable(rex::getTable('pagestats_dump'));
                $sql->setValue('browser', $browser);
                $sql->setValue('os', $os . " " . $osVer);
                $sql->setValue('browsertype', $device_type);
                $sql->setValue('brand', $brand);
                $sql->setValue('model', $model);
                $sql->setValue('url', $url);
                $sql->setValue('date', $datetime_now->format('Y-m-d'));
                $sql->setValue('hour', $datetime_now->format('H'));
                $sql->setValue('weekday', $datetime_now->format('N'));
                $sql->insert();
            }
        }
    }
}
