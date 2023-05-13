<?php

/**
 * Media Manager Effect to provide a method to log specific media files
 *
 */
class rex_effect_stats_mm extends rex_effect_abstract
{

    /**
     *
     *
     * @return void
     * @throws InvalidArgumentException
     * @throws rex_sql_exception
     */
    public function execute(): void
    {

        rex_extension::register('MEDIA_MANAGER_AFTER_SEND', function () {
            $addon = rex_addon::get('statistics');

            if ($addon->getConfig('statistics_media_log_mm') == true) {
                $url = rex_server('REQUEST_URI', 'string', '');

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
        });
    }


    /**
     *
     *
     * @return string
     */
    public function getName(): string
    {
        return 'Datei in Statistik loggen';
    }
}
