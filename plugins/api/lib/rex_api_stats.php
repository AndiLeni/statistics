<?php

use Vectorface\Whip\Whip;

/**
 * API class
 *
 */
class rex_api_stats extends rex_api_function
{

    protected $published = true;


    /**
     * 
     * 
     * @return rex_api_result 
     * @throws InvalidArgumentException 
     * @throws Exception 
     * @throws rex_sql_exception 
     */
    public function execute(): rex_api_result
    {
        $plugin = rex_plugin::get('statistics', 'api');
        if ($plugin->getConfig('statistics_api_enable') == true) {

            require_once __DIR__ . '/../../../vendors/autoload.php';

            // get ip from visitor, set to 0.0.0.0 when ip can not be determined
            $whip = new Whip();
            $clientAddress = $whip->getValidIpAddress();
            $clientAddress = $clientAddress ? $clientAddress : '0.0.0.0';

            // campaign url
            $name = rex_request('name', 'string', '');

            if ($name != '') {

                // user agent
                $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

                $visit = new stats_campaign_visit($clientAddress, $name, $userAgent);

                // parse useragent
                $visit->parse_ua();

                if (!$visit->is_bot() && $visit->save_visit()) {

                    $visit->save();
                }


                $result = new rex_api_result(true);
            } else {
                $result = new rex_api_result(false);
            }

            return $result;
        }
        $result = new rex_api_result(true);
        return $result;
    }
}
