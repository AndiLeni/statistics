<?php

/**
 * Helper class for handling date filters on backend pages
 * 
 * @author Andreas Lenhardt
 */
class filter_date_helper
{

    public $date_start;
    public $date_end;

    private $table;
    private $addon;

    /**
     * 
     * 
     * @param mixed $date_start 
     * @param mixed $date_end 
     * @param mixed $table 
     * @return void 
     * @throws InvalidArgumentException 
     * @throws rex_sql_exception 
     * @author Andreas Lenhardt
     */
    function __construct($date_start, $date_end, $table)
    {
        $this->date_start = $date_start;
        $this->date_end = $date_end;
        $this->table = $table;
        $this->addon = rex_addon::get('statistics');

        if ($date_start == '') {
            $this->date_start = $this->getMinDateFromTable();

            // design decision, uncomment this line to default show only timespan where data was collected
            // $this->date_end = $this->getMaxDateFromTable();

            $this->date_end = new DateTime();
            // $this->date_end->modify('+1 day');
        } else {
            $this->date_start = new DateTime($date_start);
            $this->date_end = new DateTime($date_end);
        }

        if ($this->date_start > $this->date_end) {
            echo rex_view::error($this->addon->i18n('statistics_dates'));
        }
    }

    /**
     * 
     * 
     * @return DateTime 
     * @throws InvalidArgumentException 
     * @throws rex_sql_exception 
     * @author Andreas Lenhardt
     */
    private function getMaxDateFromTable()
    {
        $sql = rex_sql::factory();
        $max_date = $sql->setQuery('SELECT MAX(date) AS "date" from ' . rex::getTable($this->table));
        $max_date = $max_date->getValue('date');
        $max_date = new DateTime($max_date);
        $max_date->modify('+1 day');

        return $max_date;
    }

    /**
     * 
     * 
     * @return DateTime 
     * @throws InvalidArgumentException 
     * @throws rex_sql_exception 
     * @author Andreas Lenhardt
     */
    private function getMinDateFromTable()
    {
        $sql = rex_sql::factory();
        $min_date = $sql->setQuery('SELECT MIN(date) AS "date" from ' . rex::getTable($this->table));
        $min_date = $min_date->getValue('date');
        $min_date = new DateTime($min_date);

        return $min_date;
    }
}
