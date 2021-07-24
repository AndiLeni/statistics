<?php


use DeviceDetector\DeviceDetector;


/**
 * Main class to handle saving of page visitors.
 * Performs checks to decide if visit should be ignored
 *
 * @author Andreas Lenhardt
 */
class stats_visit
{

    const IGNORE_WHEN_STARTS = [
        '/robots.txt',
        '/sitemap.xml',
    ];

    const IGNORE_WHEN_CONTAINS = [
        'rex_version=1',
        'search_it_build_index',
        'rex-api-call',
    ];

    const IGNORE_WHEN_ENDS = [
        '.css',
        '.js',
        'favicon.ico',
    ];


    private $datetime_now;

    private $addon;

    /**
     * @var string
     */
    private $clientIPAddress;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $userAgent;

    public $DeviceDetector;

    /**
     * @var string
     */
    private $browser = 'Undefiniert';

    /**
     * @var string
     */
    private $os = 'Undefiniert';

    /**
     * @var string
     */
    private $osVer = 'Undefiniert';

    /**
     * @var string
     */
    private $device_type = 'Undefiniert';

    /**
     * @var string
     */
    private $brand = 'Undefiniert';

    /**
     * @var string
     */
    private $model = 'Undefiniert';



    /**
     *
     *
     * @param string $clientIPAddress
     * @param string $url
     * @param string $userAgent
     * @return void
     * @throws InvalidArgumentException
     * @author Andreas Lenhardt
     */
    public function __construct(string $clientIPAddress, string $url, string $userAgent)
    {
        $this->addon = rex_addon::get('statistics');
        $this->clientIPAddress = $clientIPAddress;
        $this->url = $url;
        $this->datetime_now = new DateTime();
        $this->userAgent = $userAgent;
    }


    /**
     *
     *
     * @return bool
     * @author Andreas Lenhardt
     */
    public function ignore_visit()
    {
        // check if visit should be ignored
        $ignored_paths = $this->addon->getConfig('pagestats_ignored_paths');
        $ignored_ips = $this->addon->getConfig('pagestats_ignored_ips');
        $ignored_regex = $this->addon->getConfig('pagestats_ignored_regex');

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

        foreach (self::IGNORE_WHEN_ENDS as $el) {
            if (str_ends_with($this->url, $el)) {
                return true;
            }
        }

        foreach (self::IGNORE_WHEN_STARTS as $el) {
            if (str_starts_with($this->url, $el)) {
                return true;
            }
        }

        foreach (self::IGNORE_WHEN_CONTAINS as $el) {
            if (str_contains($this->url, $el)) {
                return true;
            }
        }

        if (trim($ignored_regex != '')) {
            $ignored_regex = explode("\n", str_replace("\r", "", $ignored_regex));

            foreach ($ignored_regex as $regex) {                
                if (preg_match($regex, $this->url) === true) {
                    return true;
                }
            }
        }

        return false;
    }


    /**
     *
     *
     * @return void
     * @throws InvalidArgumentException
     * @throws rex_sql_exception
     * @author Andreas Lenhardt
     */
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
        $sql->setValue('model', $this->brand . ' - ' . $this->model);
        $sql->setValue('url', $this->url);
        $sql->setValue('date', $this->datetime_now->format('Y-m-d'));
        $sql->setValue('hour', $this->datetime_now->format('H'));
        $sql->setValue('weekday', $this->datetime_now->format('N'));
        $sql->insert();
    }



    /**
     *
     *
     * @return bool
     * @throws InvalidArgumentException
     * @throws rex_sql_exception
     * @author Andreas Lenhardt
     */
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


    /**
     *
     *
     * @return void
     * @throws Exception
     * @author Andreas Lenhardt
     */
    public function parse_ua()
    {
        $this->DeviceDetector = new DeviceDetector($this->userAgent);
        $this->DeviceDetector->parse();
    }


    /**
     *
     *
     * @return void
     * @throws InvalidArgumentException
     * @throws rex_sql_exception
     * @author Andreas Lenhardt
     */
    public function save_bot()
    {
        $botInfo = $this->DeviceDetector->getBot();

        $botname = $botInfo['name'] ?? '-';
        $botcategory = $botInfo['category'] ?? '-';
        $botproducer = $botInfo['producer']['name'] ?? '-';

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


    /**
     *
     *
     * @param string $referer
     * @return void
     * @throws InvalidArgumentException
     * @throws rex_sql_exception
     * @author Andreas Lenhardt
     */
    public function save_referer(string $referer)
    {
        $sql = rex_sql::factory();
        $result = $sql->setQuery('UPDATE ' . rex::getTable('pagestats_referer') . ' SET count = count + 1 WHERE referer = :referer', ['referer' => $referer]);

        if ($result->getRows() === 0) {
            $ref = rex_sql::factory();
            $ref->setTable(rex::getTable('pagestats_referer'));
            $ref->setValue('referer', $referer);
            $ref->setValue('count', 1);
            $ref->insert();
        }
    }


    /**
     *
     *
     * @return mixed
     * @author Andreas Lenhardt
     */
    public function is_bot()
    {
        return $this->DeviceDetector->isBot();
    }
}
