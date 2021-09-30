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
     * @return (string|false)[]
     * @throws InvalidArgumentException
     * @throws rex_sql_exception
     * @author Andreas Lenhardt
     */
    public function get_browser()
    {
        $sql = rex_sql::factory();

        if ($this->min_date != '' && $this->max_date != '') {
            $result = $sql->setQuery('SELECT browser, COUNT(browser) as "count" FROM ' . rex::getTable('pagestats_dump') . ' WHERE url = :url and date between :start and :end GROUP BY browser ORDER BY count DESC', ['url' => $this->url, 'start' => $this->min_date->format('Y-m-d'), 'end' => $this->max_date->format('Y-m-d')]);
        } else {
            $result = $sql->setQuery('SELECT browser, COUNT(browser) as "count" FROM ' . rex::getTable('pagestats_dump') . ' WHERE url = :url GROUP BY browser ORDER BY count DESC', ['url' => $this->url]);
        }

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

        if ($this->min_date != '' && $this->max_date != '') {
            $result = $sql->setQuery('SELECT browsertype, COUNT(browsertype) as "count" FROM ' . rex::getTable('pagestats_dump') . ' WHERE url = :url and date between :start and :end GROUP BY browsertype ORDER BY count DESC', ['url' => $this->url, 'start' => $this->min_date->format('Y-m-d'), 'end' => $this->max_date->format('Y-m-d')]);
        } else {
            $result = $sql->setQuery('SELECT browsertype, COUNT(browsertype) as "count" FROM ' . rex::getTable('pagestats_dump') . ' WHERE url = :url GROUP BY browsertype ORDER BY count DESC', ['url' => $this->url]);
        }

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

        if ($this->min_date != '' && $this->max_date != '') {
            $result = $sql->setQuery('SELECT os, COUNT(os) as "count" FROM ' . rex::getTable('pagestats_dump') . ' WHERE url = :url and date between :start and :end GROUP BY os ORDER BY count DESC', ['url' => $this->url, 'start' => $this->min_date->format('Y-m-d'), 'end' => $this->max_date->format('Y-m-d')]);
        } else {
            $result = $sql->setQuery('SELECT os, COUNT(os) as "count" FROM ' . rex::getTable('pagestats_dump') . ' WHERE url = :url GROUP BY os ORDER BY count DESC', ['url' => $this->url]);
        }

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
        if ($this->min_date != '' && $this->max_date != '') {
            $list = rex_list::factory('SELECT date, COUNT(date) as "count" FROM ' . rex::getTable('pagestats_dump') . ' WHERE url = "' . $this->url . '" and date between "' . $this->min_date->format('Y-m-d') . '" and "' . $this->max_date->format('Y-m-d') . '" GROUP BY date ORDER BY count DESC', 500);
        } else {
            $list = rex_list::factory('SELECT date, COUNT(date) as "count" FROM ' . rex::getTable('pagestats_dump') . ' WHERE url = "' . $this->url . '" GROUP BY date ORDER BY count DESC', 500);
        }

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

        $sum_per_day = $sql->setQuery('SELECT date, COUNT(date) AS "count" from ' . rex::getTable('pagestats_dump') . ' WHERE url = :url and date between :start and :end GROUP BY date ORDER BY date ASC', ['url' => $this->url, 'start' => $this->min_date->format('Y-m-d'), 'end' => $this->max_date->format('Y-m-d')]);

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
