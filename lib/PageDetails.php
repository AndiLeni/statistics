<?php

namespace AndiLeni\Statistics;

use DateInterval;
use DatePeriod;
use DateTime;
use rex;
use rex_list;
use rex_sql;
use InvalidArgumentException;
use rex_sql_exception;
use rex_exception;

/**
 * Used on the page "pages.php" to handle and retreive data for a single url in the "details-section"
 *
 */
class PageDetails
{
    private string $url;
    private DateFilter $filter_date_helper;


    /**
     * 
     * 
     * @param string $url 
     * @param DateFilter $filterDateHelper 
     * @return void 
     */
    public function __construct(string $url, DateFilter $filterDateHelper)
    {
        $this->url = $url;
        $this->filter_date_helper = $filterDateHelper;
    }


    /**
     * 
     * 
     * @return string 
     * @throws InvalidArgumentException 
     * @throws rex_exception 
     */
    public function getList(): string
    {
        $list = rex_list::factory('SELECT date, count FROM ' . rex::getTable('pagestats_visits_per_url') . ' WHERE url = "' . $this->url . '" and date between "' . $this->filter_date_helper->date_start->format('Y-m-d') . '" and "' . $this->filter_date_helper->date_end->format('Y-m-d') . '" ORDER BY count DESC', 10000);

        $list->setColumnLabel('date', 'Datum');
        $list->setColumnLabel('count', 'Anzahl');
        $list->setColumnParams('url', ['url' => '###url###']);
        $list->addTableAttribute('class', 'table-bordered dt_order_first statistics_table table-striped table-hover');
        $list->setColumnFormat('date', 'date', 'd.m.Y');
        $list->setColumnLayout('date', ['<th>###VALUE###</th>', '<td data-sort="###date###">###VALUE###</td>']);

        return $list->get();
    }


    /**
     * 
     * 
     * @return int 
     * @throws InvalidArgumentException 
     * @throws rex_sql_exception 
     */
    public function getPageTotal(): int
    {
        $details_page_total = rex_sql::factory();

        $details_page_total->setQuery('SELECT sum(count) as "count" FROM ' . rex::getTable('pagestats_visits_per_url') . ' WHERE url = :url', ['url' => $this->url]);

        $details_page_total = $details_page_total->getValue('count') ? intval($details_page_total->getValue('count')) : 0;

        return $details_page_total;
    }


    /**
     * 
     * 
     * @return array 
     * @throws InvalidArgumentException 
     * @throws rex_sql_exception 
     */
    public function getSumPerDay(): array
    {
        $sql = rex_sql::factory();

        // modify to include end date in period because SQL BETWEEN includes start and end date, but DatePeriod excludes end date
        // without modification an additional day would be fetched from database
        $period = new DatePeriod(
            $this->filter_date_helper->date_start,
            new DateInterval('P1D'),
            $this->filter_date_helper->date_end->modify('+1 day')
        );

        $array = [];

        foreach ($period as $value) {
            $array[$value->format("d.m.Y")] = "0";
        }

        $sum_per_day = $sql->setQuery('SELECT date, count from ' . rex::getTable('pagestats_visits_per_url') . ' WHERE url = :url and date between :start and :end ORDER BY date ASC', ['url' => $this->url, 'start' => $this->filter_date_helper->date_start->format('Y-m-d'), 'end' => $this->filter_date_helper->date_end->format('Y-m-d')]);

        $data = [];
        $arr2 = [];

        if ($sum_per_day->getRows() != 0) {
            foreach ($sum_per_day as $row) {
                $date = DateTime::createFromFormat('Y-m-d', $row->getValue('date'))->format('d.m.Y');
                $arr2[$date] = $row->getValue('count');
            }

            $data = array_merge($array, $arr2);
        }

        return [
            'labels' => array_keys($data),
            'values' => array_values($data),
        ];
    }
}
