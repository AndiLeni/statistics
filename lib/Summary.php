<?php

namespace AndiLeni\Statistics;

use rex;
use rex_sql;
use rex_sql_exception;
use InvalidArgumentException;


class Summary
{

    private DateFilter $filter_date_helper;


    /**
     * 
     * 
     * @param DateFilter $filter_date_helper 
     * @return void 
     */
    public function __construct(DateFilter $filter_date_helper)
    {
        $this->filter_date_helper = $filter_date_helper;
    }


    /**
     * 
     * 
     * @return array 
     * @throws InvalidArgumentException 
     * @throws rex_sql_exception 
     */
    public function getSummaryData(): array
    {
        $sql = rex_sql::factory();
        $today = date('Y-m-d');

        $visits = $sql->getArray(
            'SELECT '
            . 'IFNULL(SUM(count), 0) AS total, '
            . 'IFNULL(SUM(CASE WHEN date = :today THEN count ELSE 0 END), 0) AS today, '
            . 'IFNULL(SUM(CASE WHEN date BETWEEN :start AND :end THEN count ELSE 0 END), 0) AS filtered '
            . 'FROM ' . rex::getTable('pagestats_visits_per_day'),
            [
                'today' => $today,
                'start' => $this->filter_date_helper->date_start->format('Y-m-d'),
                'end' => $this->filter_date_helper->date_end->format('Y-m-d'),
            ]
        );

        $visitors = $sql->getArray(
            'SELECT '
            . 'IFNULL(SUM(count), 0) AS total, '
            . 'IFNULL(SUM(CASE WHEN date = :today THEN count ELSE 0 END), 0) AS today, '
            . 'IFNULL(SUM(CASE WHEN date BETWEEN :start AND :end THEN count ELSE 0 END), 0) AS filtered '
            . 'FROM ' . rex::getTable('pagestats_visitors_per_day'),
            [
                'today' => $today,
                'start' => $this->filter_date_helper->date_start->format('Y-m-d'),
                'end' => $this->filter_date_helper->date_end->format('Y-m-d'),
            ]
        );

        $visitsRow = $visits[0] ?? ['total' => 0, 'today' => 0, 'filtered' => 0];
        $visitorsRow = $visitors[0] ?? ['total' => 0, 'today' => 0, 'filtered' => 0];

        return [
            'visits_datefilter' => $visitsRow['filtered'],
            'visitors_datefilter' => $visitorsRow['filtered'],
            'visits_today' => $visitsRow['today'],
            'visitors_today' => $visitorsRow['today'],
            'visits_total' => $visitsRow['total'],
            'visitors_total' => $visitorsRow['total'],
        ];
    }
}
