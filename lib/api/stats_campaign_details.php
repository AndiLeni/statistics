<?php

namespace AndiLeni\Statistics;

use DateInterval;
use DatePeriod;
use DateTime;
use rex;
use rex_sql;
use rex_sql_exception;
use InvalidArgumentException;

/**
 * Used on the page "campaigns.php" to handle and retreive data for a single api-request
 *
 */
class stats_campaign_details
{
    private string $name;
    private filterDateHelper $filterDateHelper;



    /**
     * 
     * 
     * @param string $name 
     * @param filterDateHelper $filterDateHelper 
     * @return void 
     */
    public function __construct(string $name, filterDateHelper $filterDateHelper)
    {
        $this->name = $name;
        $this->filterDateHelper = $filterDateHelper;
    }


    /**
     * 
     * 
     * @return array 
     * @throws InvalidArgumentException 
     * @throws rex_sql_exception 
     */
    public function get_sum_per_day(): array
    {
        $sql = rex_sql::factory();

        // modify to include end date in period because SQL BETWEEN includes start and end date, but DatePeriod excludes end date
        // without modification an additional day would be fetched from database
        $period = new DatePeriod(
            $this->filterDateHelper->date_start,
            new DateInterval('P1D'),
            $this->filterDateHelper->date_end->modify('+1 day')
        );

        $array = [];
        foreach ($period as $value) {
            $array[$value->format("d.m.Y")] = "0";
        }

        $sum_per_day = $sql->setQuery('SELECT date, count from ' . rex::getTable('pagestats_api') . ' WHERE name = :name and date between :start and :end GROUP BY date ORDER BY date ASC', ['name' => $this->name, 'start' => $this->filterDateHelper->date_start->format('Y-m-d'), 'end' => $this->filterDateHelper->date_end->format('Y-m-d')]);

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
