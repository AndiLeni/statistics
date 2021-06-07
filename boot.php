<?php

// $addon = rex_addon::get('stats');

require_once __DIR__ . '/vendor/autoload.php';


use DeviceDetector\DeviceDetector;



function save_data($column, $table, $data)
{
    $sql = rex_sql::factory();
    // $sql->setDebug(true);
    $result = $sql->setQuery('SELECT * FROM ' . rex::getTable($table) . ' WHERE ' . $column . ' = :data', ['data' => $data]);

    if ($result->getRows() === 0) {

        $sql->setDBQuery('INSERT INTO ' . rex::getTable($table) . ' (' . $column . ',count) VALUES (:data, :count)', ['data' => $data, 'count' => 1]);
    } else {

        foreach ($sql as $row) {
            $count = $row->getValue('count');
        }

        $sql->setDBQuery('UPDATE ' . rex::getTable($table) . ' SET count = :count WHERE ' . $column . ' = :data', ['data' => $data, 'count' => $count + 1]);
    }
}

function def_or_undefined($data)
{
    if ($data == NULL or trim($data) == "") {
        return "Undefiniert";
    } else {
        return ucfirst($data);
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
        $sql->setDebug(true);
        $result = $sql->setQuery('SELECT * FROM ' . rex::getTable('pagestats_bot') . ' WHERE name = :name AND category = :category AND producer = :producer', ['name' => $botname, 'category' => $botcategory, 'producer' => $botproducer]);

        if ($result->getRows() === 0) {

            $bot = rex_sql::factory();
            $bot->setTable(rex::getTable('pagestats_bot'));
            $bot->setValue('name', $botname);
            $bot->setValue('category', $botcategory);
            $bot->setValue('producer', $botproducer);
            $bot->setValue('count', 1);
            $bot->insert();

            // $sql->setDBQuery('INSERT INTO ' . rex::getTable('pagestats_bot') . ' (name, category, producer, count) VALUES (:name, :category, :producer, count)', ['name' => $botname, 'category' => $botcategory, 'producer' => $botproducer, 'count' => 1]);
        } else {

            foreach ($sql as $row) {
                $count = $row->getValue('count');
            }

            // $sql->setDBQuery('UPDATE ' . rex::getTable('pagestats_bot') . ' SET count = :count WHERE name = :name AND category = :category AND producer = :producer', ['name' => $botname, 'category' => $botcategory, 'producer' => $botproducer, 'count' => $count + 1]);
            $sql = rex_sql::factory();
            $sql->setTable(rex::getTable('pagestats_bot'));
            $sql->setWhere(['name' => $botname, 'category' => $botcategory, 'producer' => $botproducer]);
            $sql->select();

            $sql->setTable(rex::getTable('pagestats_bot'));
            $sql->setValue('count', $count + 1);
            $sql->update();
        }
    } else {
        $clientInfo = $dd->getClient();
        $osInfo = $dd->getOs();
        $device = $dd->getDeviceName();
        $brand = $dd->getBrandName();
        $model = $dd->getModel();

        $browser = def_or_undefined($clientInfo['name']);
        $os = def_or_undefined($osInfo['name']);
        $osVer = def_or_undefined($osInfo['version']);
        $device_type = def_or_undefined($device);
        $brand = def_or_undefined($brand);
        $model = def_or_undefined($model);

        // save_visit();
        // save_data('date', 'pagestats_views', date('d.m.Y'));

        // save_browser($browser);
        save_data('name', 'pagestats_browser', $browser);

        // save_os($os . " " . $osVer);
        save_data('name', 'pagestats_os', $os . " " . $osVer);

        // save device type
        save_data('name', 'pagestats_browsertype', $device_type);

        // save_brand($brand);
        save_data('name', 'pagestats_brand', $brand);

        // save_model($model);
        save_data('name', 'pagestats_model', $model);



        $url = $_SERVER['REQUEST_URI'];

        $sql = rex_sql::factory();
        $sql->setTable(rex::getTable('pagestats_views'));
        $sql->setWhere(['url' => $url, 'date' => date('d.m.Y')]);
        $sql->select();


        if ($sql->getRows() === 0) {
            $sql = rex_sql::factory();
            $sql->setTable(rex::getTable('pagestats_views'));
            $sql->setValue('url', $url);
            $sql->setValue('date', date('d.m.Y'));
            $sql->setValue('count', 1);
            $sql->insert();
        } else {
            $count = $sql->getValue('count');
            $sql = rex_sql::factory();
            $sql->setTable(rex::getTable('pagestats_views'));
            $sql->setWhere(['url' => $url, 'date' => date('d.m.Y')]);
            $sql->setValue('count', $count + 1);
            $sql->update();
        }
    }
}
