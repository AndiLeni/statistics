<?php

/**
 * Used on the page "media.php" to handle and retreive data for a single media url
 *
 * @author Andreas Lenhardt
 */
class stats_media_details
{
    private $url;

    /**
     *
     *
     * @param string $url
     * @return void
     * @author Andreas Lenhardt
     */
    public function __construct(string $url)
    {
        $this->url = $url;
    }


    // /**
    //  *
    //  *
    //  * @return string
    //  * @throws InvalidArgumentException
    //  * @throws rex_exception
    //  * @author Andreas Lenhardt
    //  */
    // public function get_list()
    // {
    //     $list = rex_list::factory('SELECT date, COUNT(date) as "count" FROM ' . rex::getTable('pagestats_dump') . ' WHERE url = "' . $this->url . '" GROUP BY date ORDER BY count DESC');
    //     $list->setColumnLabel('date', 'Datum');
    //     $list->setColumnLabel('count', 'Anzahl');
    //     $list->setColumnSortable('date', $direction = 'desc');
    //     $list->setColumnSortable('count', $direction = 'desc');
    //     $list->setColumnParams('url', ['url' => '###url###']);

    //     return $list->get();
    // }


    /**
     *
     *
     * @return mixed
     * @throws InvalidArgumentException
     * @throws rex_sql_exception
     * @author Andreas Lenhardt
     */
    public function get_page_total()
    {
        $details_page_total = rex_sql::factory();
        $details_page_total->setQuery('SELECT SUM(count) as "count" FROM ' . rex::getTable('pagestats_media') . ' WHERE url = :url', ['url' => $this->url]);
        $details_page_total = $details_page_total->getValue('count');

        return $details_page_total;
    }


    /**
     *
     *
     * @return (string|false)[]
     * @throws InvalidArgumentException
     * @throws rex_sql_exception
     * @author Andreas Lenhardt
     */
    public function get_sum_per_day()
    {
        $sql = rex_sql::factory();

        $max_date = $sql->setQuery('SELECT MAX(date) AS "date" from ' . rex::getTable('pagestats_media') . ' WHERE url = :url', ['url' => $this->url]);
        $max_date = $max_date->getValue('date');
        $max_date = new DateTime($max_date);
        $max_date->modify('+1 day');
        $max_date = $max_date->format('d.m.Y');

        $min_date = $sql->setQuery('SELECT MIN(date) AS "date" from ' . rex::getTable('pagestats_media') . ' WHERE url = :url', ['url' => $this->url]);
        $min_date = $min_date->getValue('date');

        $period = new DatePeriod(
            new DateTime($min_date),
            new DateInterval('P1D'),
            new DateTime($max_date)
        );

        foreach ($period as $value) {
            $array[$value->format("d.m.Y")] = "0";
        }

        $sum_per_day = $sql->setQuery('SELECT date, count from ' . rex::getTable('pagestats_media') . ' WHERE url = :url GROUP BY date ORDER BY date ASC', ['url' => $this->url]);

        $data = [];

        if ($sum_per_day->getRows() != 0) {
            foreach ($sum_per_day as $row) {
                $date = DateTime::createFromFormat('Y-m-d', $row->getValue('date'))->format('d.m.Y');
                $arr2[$date] = $row->getValue('count');
            }

            $data = array_merge($array, $arr2);
        }

        return [
            'labels' => json_encode(array_keys($data)),
            'values' => json_encode(array_values($data)),
        ];
    }
}
