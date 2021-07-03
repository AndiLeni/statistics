<?php



/**
     * Media Manager Effect to provide a method to log specific media files 
 * 
 * @author Andreas Lenhardt
 */
class rex_effect_stats_mm extends rex_effect_abstract
{

    
    /**
     * 
     * 
     * @return void 
     * @throws InvalidArgumentException 
     * @throws rex_sql_exception 
     * @author Andreas Lenhardt
     */
    public function execute()
    {

        $plugin = rex_plugin::get('statistics', 'media');

        if ($plugin->getConfig('pagestats_media_log_mm') == true) {

            $url = $_SERVER['REQUEST_URI'];

            $sql = rex_sql::factory();
            $result = $sql->setQuery('UPDATE ' . rex::getTable('pagestats_media') . ' SET count = count + 1 WHERE url = :url AND date = :date', ['url' => $url, 'date' => date('Y-m-d')]);

            if ($result->getRows() === 0) {
                $bot = rex_sql::factory();
                $bot->setTable(rex::getTable('pagestats_media'));
                $bot->setValue('url', $url);
                $bot->setValue('date', date('Y-m-d'));
                $bot->setValue('count', 1);
                $bot->insert();
            }
        }
    }

    
    /**
     * 
     * 
     * @return string 
     * @author Andreas Lenhardt
     */
    public function getName()
    {
        return 'Datei in Statistik loggen';
    }
}
