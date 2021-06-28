<?php

require_once __DIR__ . '/../vendors/autoload.php';

use DeviceDetector\DeviceDetector;

class stats_visit
{

    // media urls, for media detection
    const MEDIA_URLS = [
        '/index.php?rex_media_type',
        '/media',
        '/index.php?rex_media_file',
    ];

    const MEDIA_TYPES = [
        '.jpg',
        '.jpeg',
        '.png',
        '.webp',
        '.tiff',
        '.pdf',
        '.ico',
        '.svg',
        '.docx',
        '.odt',
    ];

    const IGNORE_WHEN_CONTAINS = [
        'rex_version=1',
    ];

    private $datetime_now;

    private $addon;
    private $clientIPAddress;
    private $url;
    private $userAgent;

    public $DeviceDetector;

    private $browser = 'Undefiniert';
    private $os = 'Undefiniert';
    private $osVer = 'Undefiniert';
    private $device_type = 'Undefiniert';
    private $brand = 'Undefiniert';
    private $model = 'Undefiniert';



    public function __construct(string $clientIPAddress, string $url, string $userAgent)
    {
        $this->addon = rex_addon::get('stats');
        $this->clientIPAddress = $clientIPAddress;
        $this->url = $url;
        $this->datetime_now = new DateTime();
        $this->userAgent = $userAgent;
    }

    public function ignore_visit()
    {
        // check if visit should be ignored
        $ignored_paths = $this->addon->getConfig('pagestats_ignored_paths');
        $ignored_ips = $this->addon->getConfig('pagestats_ignored_ips');

        if (trim($ignored_ips != '')) {
            $ignored_ips = explode("\n", str_replace("\r", "", $ignored_ips));

            foreach ($ignored_ips as $path) {
                if (str_starts_with($this->clientIPAddress, $path)) {
                    return true;
                }
            }
        }

        if (trim($ignored_paths != '')) {
            $ignored_paths = explode("\n", str_replace("\r", "", $ignored_paths));

            foreach ($ignored_paths as $path) {
                if (str_starts_with($this->url, $path)) {
                    return true;
                }
            }
        }

        foreach (self::IGNORE_WHEN_CONTAINS as $el) {
            if (str_contains($this->url, $el)) {
                return true;
            }
        }


        return false;
    }

    function is_media()
    {
        foreach (self::MEDIA_URLS as $el) {
            if (str_starts_with($this->url, $el)) {
                return true;
            }
        }

        foreach (self::MEDIA_TYPES as $el) {
            if (str_ends_with($this->url, $el)) {
                return true;
            }
        }

        return false;
    }

    public function persist()
    {
        $clientInfo = $this->DeviceDetector->getClient();
        $osInfo = $this->DeviceDetector->getOs();
        $deviceInfo = $this->DeviceDetector->getDeviceName();
        $brandInfo = $this->DeviceDetector->getBrandName();
        $modelInfo = $this->DeviceDetector->getModel();


        $this->browser = $clientInfo['name'] ?? 'Undefiniert';
        $this->os = $osInfo['name'] ?? 'Undefiniert';
        $this->osVer = $osInfo['version'] ?? 'Undefiniert';
        $this->device_type = trim($deviceInfo) != '' ? ucfirst($deviceInfo) : 'Undefiniert';
        $this->brand = trim($brandInfo) != '' ? ucfirst($brandInfo) : 'Undefiniert';
        $this->model = trim($modelInfo) != '' ? ucfirst($modelInfo) : 'Undefiniert';


        $sql = rex_sql::factory();
        $sql->setTable(rex::getTable('pagestats_dump'));
        $sql->setValue('browser', $this->browser);
        $sql->setValue('os', $this->os . " " . $this->osVer);
        $sql->setValue('browsertype', $this->device_type);
        $sql->setValue('brand', $this->brand);
        $sql->setValue('model', $this->model);
        $sql->setValue('url', $this->url);
        $sql->setValue('date', $this->datetime_now->format('Y-m-d'));
        $sql->setValue('hour', $this->datetime_now->format('H'));
        $sql->setValue('weekday', $this->datetime_now->format('N'));
        $sql->insert();
    }

    public function save_visit()
    {
        $hash_string = $this->userAgent . $this->browser . $this->os . " " . $this->osVer . $this->device_type . $this->brand . $this->model . $this->clientIPAddress . $this->url;
        $hash = hash('sha1', $hash_string);

        $sql = rex_sql::factory();
        $sql->setTable(rex::getTable('pagestats_hash'));
        $sql->setWhere(['hash' => $hash]);
        $sql->select();

        if ($sql->getRows() == 1) {
            $origin = new DateTime($sql->getValue('datetime'));
            $target = new DateTime();
            $interval = $origin->diff($target);
            $minute_diff = $interval->i + ($interval->h * 60) + ($interval->d * 3600) + ($interval->m * 43800) + ($interval->y * 525599);

            // hash was found, if last visit < 'pagestats_visit_duration' min save visit
            $max_visit_length = intval($this->addon->getConfig('pagestats_visit_duration'));

            if ($minute_diff > $max_visit_length) {
                // update set last visit to now
                $sql->setQuery('UPDATE ' . rex::getTable('pagestats_hash') . ' SET datetime = :datetime WHERE hash = :hash ', ['hash' => $hash, 'datetime' => $this->datetime_now->format('Y-m-d H:i:s')]);
                return true;
            } else {
                return false;
            }
        } else {
            // hash was not found, save hash with current datetime, then save visit
            $sql = rex_sql::factory();
            $sql->setTable(rex::getTable('pagestats_hash'));
            $sql->setValue('hash', $hash);
            $sql->setValue('datetime', $this->datetime_now->format('Y-m-d H:i:s'));
            $sql->insert();

            return true;
        }
    }

    public function save_media()
    {
        $sql = rex_sql::factory();
        $result = $sql->setQuery('UPDATE ' . rex::getTable('pagestats_media') . ' SET count = count + 1 WHERE url = :url AND date = :date', ['url' => $this->url, 'date' => date('Y-m-d')]);

        if ($result->getRows() === 0) {
            $bot = rex_sql::factory();
            $bot->setTable(rex::getTable('pagestats_media'));
            $bot->setValue('url', $this->url);
            $bot->setValue('date', $this->datetime_now->format('Y-m-d'));
            $bot->setValue('count', 1);
            $bot->insert();
        }
    }

    public function parse_ua()
    {
        $this->DeviceDetector = new DeviceDetector($this->userAgent);
        $this->DeviceDetector->parse();
    }

    public function save_bot()
    {
        $botInfo = $this->DeviceDetector->getBot();

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
    }
}
