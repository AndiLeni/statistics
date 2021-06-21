<?php

require_once __DIR__ . '/vendor/autoload.php';

use DeviceDetector\DeviceDetector;
use Vectorface\Whip\Whip;

$value = $this->getConfig('pagestats_ignored_paths');
$value = explode("\n", str_replace("\r", "", $value));
dump($value);
dump($_SERVER['REQUEST_URI']);


foreach ($value as $el) {
    if (str_starts_with($_SERVER['REQUEST_URI'], $el)) {
        echo 'ignore: ' . $el;
    }
}



// Track only frontend requests
if (!rex::isBackend()) {

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
            $sql->setDebug(true);
            $sql->setTable(rex::getTable('pagestats_hash'));
            $sql->setWhere(['hash' => $hash]);
            $sql->select();

            // if hash is found check if last visit < 30 minutes then save visit, else dont save visit
            if ($sql->getRows() == 1) {
                $origin = new DateTime($sql->getValue('datetime'));
                $target = new DateTime();
                $interval = $origin->diff($target);
                $minute_diff = $interval->i + ($interval->h * 60) + ($interval->d * 3600) + ($interval->m * 43800) + ($interval->y * 525599);

                // hash was found, if last visit < 30 min save visit
                if ($minute_diff > 30) {
                    // update set last visit to now
                    $sql->setQuery('UPDATE ' . rex::getTable('pagestats_hash') . ' SET datetime = :datetime WHERE hash = :hash ', ['hash' => $hash, 'datetime' => $datetime_now->format('Y-m-d H:i:s')]);

                    $sql = rex_sql::factory();
                    $sql->setDebug(true);
                    $sql->setTable(rex::getTable('pagestats_dump'));
                    $sql->setValue('browser', $browser);
                    $sql->setValue('os', $os . " " . $osVer);
                    $sql->setValue('browsertype', $device_type);
                    $sql->setValue('brand', $brand);
                    $sql->setValue('model', $model);
                    $sql->setValue('url', $url);
                    $sql->setValue('date', $datetime_now->format('Y-m-d'));
                    $sql->insert();
                }
            } else {
                // hash was not found, save hash with current datetime, then save visit
                $sql = rex_sql::factory();
                $sql->setDebug(true);
                $sql->setTable(rex::getTable('pagestats_hash'));
                $sql->setValue('hash', $hash);
                $sql->setValue('datetime', date('Y-m-d H:i:s'));
                $sql->insert();

                $sql = rex_sql::factory();
                $sql->setDebug(true);
                $sql->setTable(rex::getTable('pagestats_dump'));
                $sql->setValue('browser', $browser);
                $sql->setValue('os', $os . " " . $osVer);
                $sql->setValue('browsertype', $device_type);
                $sql->setValue('brand', $brand);
                $sql->setValue('model', $model);
                $sql->setValue('url', $url);
                $sql->setValue('date', $datetime_now->format('Y-m-d'));
                $sql->insert();
            }
        }
    }
}
