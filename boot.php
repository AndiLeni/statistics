<?php

require_once __DIR__ . '/vendor/autoload.php';

use DeviceDetector\DeviceDetector;



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

        $sql = rex_sql::factory();
        $sql->setTable(rex::getTable('pagestats_dump'));
        $sql->setValue('browser', $browser);
        $sql->setValue('os', $os . " " . $osVer);
        $sql->setValue('browsertype', $device_type);
        $sql->setValue('brand', $brand);
        $sql->setValue('model', $model);
        $sql->setValue('url', $url);
        $sql->setValue('date', date('d.m.Y'));
        $sql->insert();
    }
}
