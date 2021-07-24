<?php

/**
 * Used on the page "pages.php" to handle and retreive data for a single url in the "details-section"
 *
 * @author Andreas Lenhardt
 */
class stats_pagedetails
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

    /**
     *
     *
     * @return (string|false)[]
     * @throws InvalidArgumentException
     * @throws rex_sql_exception
     * @author Andreas Lenhardt
     */
    public function get_browser()
    {
        $sql = rex_sql::factory();
        $result = $sql->setQuery('SELECT browser, COUNT(browser) as "count" FROM ' . rex::getTable('pagestats_dump') . ' WHERE url = :url GROUP BY browser ORDER BY count DESC', ['url' => $this->url]);

        $data = [];

        foreach ($result as $row) {
            $data[$row->getValue('browser')] = $row->getValue('count');
        }

        return [
            'labels' => json_encode(array_keys($data)),
            'values' => json_encode(array_values($data)),
        ];
    }

    /**
     *
     *
     * @return (string|false)[]
     * @throws InvalidArgumentException
     * @throws rex_sql_exception
     * @author Andreas Lenhardt
     */
    public function get_browsertype()
    {
        $sql = rex_sql::factory();
        $result = $sql->setQuery('SELECT browsertype, COUNT(browsertype) as "count" FROM ' . rex::getTable('pagestats_dump') . ' WHERE url = :url GROUP BY browsertype ORDER BY count DESC', ['url' => $this->url]);

        $data = [];

        foreach ($result as $row) {
            $data[$row->getValue('browsertype')] = $row->getValue('count');
        }

        return [
            'labels' => json_encode(array_keys($data)),
            'values' => json_encode(array_values($data)),
        ];
    }

    /**
     *
     *
     * @return (string|false)[]
     * @throws InvalidArgumentException
     * @throws rex_sql_exception
     * @author Andreas Lenhardt
     */
    public function get_os()
    {
        $sql = rex_sql::factory();
        $result = $sql->setQuery('SELECT os, COUNT(os) as "count" FROM ' . rex::getTable('pagestats_dump') . ' WHERE url = :url GROUP BY os ORDER BY count DESC', ['url' => $this->url]);

        $data = [];

        foreach ($result as $row) {
            $data[$row->getValue('os')] = $row->getValue('count');
        }

        return [
            'labels' => json_encode(array_keys($data)),
            'values' => json_encode(array_values($data)),
        ];
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
        $list = rex_list::factory('SELECT date, COUNT(date) as "count" FROM ' . rex::getTable('pagestats_dump') . ' WHERE url = "' . $this->url . '" GROUP BY date ORDER BY count DESC', 500);
        $list->setColumnLabel('date', 'Datum');
        $list->setColumnLabel('count', 'Anzahl');
        $list->setColumnParams('url', ['url' => '###url###']);
        $list->addTableAttribute('class', 'table-bordered');
        $list->addTableAttribute('class', 'dt_order_first');

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
        $details_page_total->setQuery('SELECT COUNT(url) as "count" FROM ' . rex::getTable('pagestats_dump') . ' WHERE url = :url', ['url' => $this->url]);
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

        $max_date = $sql->setQuery('SELECT MAX(date) AS "date" from ' . rex::getTable('pagestats_dump') . ' WHERE url = :url', ['url' => $this->url]);
        $max_date = $max_date->getValue('date');
        $max_date = new DateTime($max_date);
        $max_date->modify('+1 day');
        $max_date = $max_date->format('d.m.Y');

        $min_date = $sql->setQuery('SELECT MIN(date) AS "date" from ' . rex::getTable('pagestats_dump') . ' WHERE url = :url', ['url' => $this->url]);
        $min_date = $min_date->getValue('date');

        $period = new DatePeriod(
            new DateTime($min_date),
            new DateInterval('P1D'),
            new DateTime($max_date)
        );

        foreach ($period as $value) {
            $array[$value->format("d.m.Y")] = "0";
        }

        $sum_per_day = $sql->setQuery('SELECT date, COUNT(date) AS "count" from ' . rex::getTable('pagestats_dump') . ' WHERE url = :url GROUP BY date ORDER BY date ASC', ['url' => $this->url]);

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
