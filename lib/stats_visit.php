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
        '.css.map',
        '.js.map',
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
        $ignored_paths = $this->addon->getConfig('statistics_ignored_paths');
        $ignored_ips = $this->addon->getConfig('statistics_ignored_ips');
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
                if (preg_match($regex, $this->url) === 1) {
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

        $sql_insert = 'INSERT INTO ' . rex::getTable('pagestats_data') . ' (type,name,count) VALUES 
        ("browser","' . addslashes($this->browser) . '",1), 
        ("os","' . addslashes($this->os) . ' ' . addslashes($this->osVer) . '",1), 
        ("browsertype","' . addslashes($this->device_type) . '",1), 
        ("brand","' . addslashes($this->brand) . '",1), 
        ("model","' . addslashes($this->brand) . ' - ' . $this->model . '",1),  
        ("hour","' . $this->datetime_now->format('H') . '",1), 
        ("weekday","' . $this->datetime_now->format('N') . '",1) 
        ON DUPLICATE KEY UPDATE count = count + 1;';

        $sql->setQuery($sql_insert);


        $sql_insert = 'INSERT INTO ' . rex::getTable('pagestats_visits_per_day') . ' (date,count) VALUES 
        ("' . $this->datetime_now->format('Y-m-d') . '",1)  
        ON DUPLICATE KEY UPDATE count = count + 1;';

        $sql->setQuery($sql_insert);


        $sql_insert = 'INSERT INTO ' . rex::getTable('pagestats_visits_per_url') . ' (hash,date,url,count) VALUES 
        ("' . md5($this->datetime_now->format('Y-m-d') . $this->url) . '","' . $this->datetime_now->format('Y-m-d') . '","' . addslashes($this->url) . '",1) 
        ON DUPLICATE KEY UPDATE count = count + 1;';

        $sql->setQuery($sql_insert);
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

            // hash was found, if last visit < 'statistics_visit_duration' min save visit
            $max_visit_length = intval($this->addon->getConfig('statistics_visit_duration'));

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

        $sql->setQuery('
        INSERT INTO ' . rex::getTable('pagestats_bot') . ' (name,category,producer,count) VALUES 
        (:botname,:botcategory,:botproducer,1) 
        ON DUPLICATE KEY UPDATE count = count + 1;', ['botname' => $botname, 'botcategory' => $botcategory, 'botproducer' => $botproducer]);
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

        $sql->setQuery('
        INSERT INTO ' . rex::getTable('pagestats_referer') . ' (referer,date,count) VALUES 
        (:referer,:date,1) 
        ON DUPLICATE KEY UPDATE count = count + 1;', ['referer' => $referer, 'date' => $this->datetime_now->format('Y-m-d')]);
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


    /**
     * removes parameters from a url
     * 
     * @param mixed $url 
     * @return string|false 
     * @author Andreas Lenhardt
     */
    public static function remove_url_parameters($url)
    {
        $url = strtok($url, '?');

        if ($url === false) {
            return '/';
        }

        return $url;
    }
}
