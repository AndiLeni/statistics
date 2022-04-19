<?php

/**
 * Helper class for handling date filters on backend pages
 * 
 */
class filterDateHelper
{

    public DateTimeImmutable $date_start;
    public DateTimeImmutable $date_end;

    public DateTimeImmutable $whole_time_start;

    private string $table;
    private rex_addon $addon;


    /**
     * 
     * 
     * @param string $date_start 
     * @param string $date_end 
     * @param string $table 
     * @return void 
     * @throws InvalidArgumentException 
     * @throws rex_sql_exception 
     */
    function __construct(string $date_start, string $date_end, string $table)
    {
        $this->table = $table;
        $this->addon = rex_addon::get('statistics');

        if ($date_start == '') {

            // prefered date range
            $date_range = $this->addon->getConfig('statistics_default_datefilter_range');

            if ($date_range == 'last7days') {
                $date = new DateTimeImmutable();
                $date = $date->modify("-7 day");
                $this->date_start = $date;
            } elseif ($date_range == 'last30days') {
                $date = new DateTimeImmutable();
                $date = $date->modify("-30 day");
                $this->date_start = $date;
            } elseif ($date_range == 'thisYear') {
                $date = new DateTimeImmutable();
                $date = $date->setTimestamp(strtotime('first day of january this year'));
                $this->date_start = $date;
            } else {
                $this->date_start = $this->getMinDateFromTable();
            }
            // design decision, uncomment this line to default show only timespan where data was collected
            // $this->date_end = $this->getMaxDateFromTable();

            $this->date_end = new DateTimeImmutable();
            // $this->date_end->modify('+1 day');
        } else {
            $this->date_start = new DateTimeImmutable($date_start);
            $this->date_end = new DateTimeImmutable($date_end);
        }

        // set total time range to use in datefilter fragment with javascript
        $this->whole_time_start = $this->getMinDateFromTable();

        if ($this->date_start > $this->date_end) {
            echo rex_view::error($this->addon->i18n('statistics_dates'));
        }
    }


    /**
     * 
     * 
     * @return DateTimeImmutable 
     * @throws InvalidArgumentException 
     * @throws rex_sql_exception 
     */
    private function getMinDateFromTable(): DateTimeImmutable
    {
        $sql = rex_sql::factory();
        $min_date = $sql->setQuery('SELECT MIN(date) AS "date" from ' . rex::getTable($this->table));
        $min_date = $min_date->getValue('date');
        $min_date = DateTimeImmutable::createFromFormat('Y-m-d', $min_date);

        return $min_date;
    }
}
