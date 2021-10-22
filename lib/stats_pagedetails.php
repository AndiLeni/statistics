<?php

/**
 * Used on the page "pages.php" to handle and retreive data for a single url in the "details-section"
 *
 * @author Andreas Lenhardt
 */
class stats_pagedetails
{
    private $url;
    private $min_date;
    private $max_date;

    /**
     *
     *
     * @param string $url
     * @return void
     * @author Andreas Lenhardt
     */
    public function __construct(string $url, $min_date, $max_date)
    {
        $this->url = $url;
        $this->min_date = $min_date;
        $this->max_date = $max_date;
    }

    

    /**
     *
     *
     * @return string
     * @throws InvalidArgumentException
     * @throws rex_exception
     * @author Andreas Lenhardt
     */
    public function get_list()
    {
        $list = rex_list::factory('SELECT date, count FROM ' . rex::getTable('pagestats_visits_per_url') . ' WHERE url = "' . $this->url . '" and date between "' . $this->min_date->format('Y-m-d') . '" and "' . $this->max_date->format('Y-m-d') . '" ORDER BY count DESC', 10000);

        $list->setColumnLabel('date', 'Datum');
        $list->setColumnLabel('count', 'Anzahl');
        $list->setColumnParams('url', ['url' => '###url###']);
        $list->addTableAttribute('class', 'table-bordered dt_order_first');

        return $list->get();
    }
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

        $details_page_total->setQuery('SELECT sum(count) as "count" FROM ' . rex::getTable('pagestats_visits_per_url') . ' WHERE url = :url', ['url' => $this->url]);

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

        // modify to include end date in period because SQL BETWEEN includes start and end date, but DatePeriod excludes end date
        // without modification an additional day would be fetched from database
        $end = clone $this->max_date;
        $end->modify('+1 day');

        $period = new DatePeriod(
            $this->min_date,
            new DateInterval('P1D'),
            $end
        );

        $array = [];

        foreach ($period as $value) {
            $array[$value->format("d.m.Y")] = "0";
        }

        $sum_per_day = $sql->setQuery('SELECT date, count from ' . rex::getTable('pagestats_visits_per_url') . ' WHERE url = :url and date between :start and :end ORDER BY date ASC', ['url' => $this->url, 'start' => $this->min_date->format('Y-m-d'), 'end' => $this->max_date->format('Y-m-d')]);

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
