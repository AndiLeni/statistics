<?php

/**
 * Used on the page "campaigns.php" to handle and retreive data for a single api-request
 *
 * @author Andreas Lenhardt
 */
class stats_campaign_details
{
    private $name;
    private $min_date;
    private $max_date;

    /**
     *
     *
     * @param string $url
     * @return void
     * @author Andreas Lenhardt
     */
    public function __construct(string $name, $min_date, $max_date)
    {
        $this->name = $name;
        $this->min_date = $min_date;
        $this->max_date = $max_date;
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
        $details_page_total->setQuery('SELECT SUM(count) as "count" FROM ' . rex::getTable('pagestats_api') . ' WHERE name = :name', ['name' => $this->name]);
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

        $period = new DatePeriod(
            new DateTime($this->min_date->format('Y-m-d')),
            new DateInterval('P1D'),
            new DateTime($this->max_date->format('Y-m-d'))
        );

        foreach ($period as $value) {
            $array[$value->format("d.m.Y")] = "0";
        }

        if ($this->min_date != '' && $this->max_date != '') {
            $sum_per_day = $sql->setQuery('SELECT date, count from ' . rex::getTable('pagestats_api') . ' WHERE name = :name and date between :start and :end GROUP BY date ORDER BY date ASC', ['name' => $this->name, 'start' => $this->min_date->format('Y-m-d'), 'end' => $this->max_date->format('Y-m-d')]);
        } else {
            $sum_per_day = $sql->setQuery('SELECT date, count from ' . rex::getTable('pagestats_api') . ' WHERE name = :name GROUP BY date ORDER BY date ASC', ['name' => $this->name]);
        }

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
