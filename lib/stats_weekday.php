<?php

/**
 * Handles the "weekday" data for statistics
 *
 * @author Andreas Lenhardt
 */
class stats_weekday
{

    private $addon;
    private $start_date = '';
    private $end_date = '';

    /**
     * 
     * 
     * @param mixed $start_date 
     * @param mixed $end_date 
     * @return void 
     * @author Andreas Lenhardt
     */
    public function __construct($start_date, $end_date)
    {
        $this->addon = rex_addon::get('statistics');
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }


    /**
     *
     *
     * @param mixed $weekday
     * @return string|void
     * @author Andreas Lenhardt
     */
    public function get_weekday_string($weekday)
    {
        switch ($weekday['value']) {
            case 1:
                return $this->addon->i18n('statistics_monday');
                break;
            case 2:
                return $this->addon->i18n('statistics_tuesday');
                break;
            case 3:
                return $this->addon->i18n('statistics_wednesday');
                break;
            case 4:
                return $this->addon->i18n('statistics_thursday');
                break;
            case 5:
                return $this->addon->i18n('statistics_friday');
                break;
            case 6:
                return $this->addon->i18n('statistics_saturday');
                break;
            case 7:
                return $this->addon->i18n('statistics_sunday');
                break;
        }
    }


    /**
     *
     *
     * @return array
     * @throws InvalidArgumentException
     * @throws rex_sql_exception
     * @author Andreas Lenhardt
     */
    private function get_sql()
    {
        $sql = rex_sql::factory();

        $result = $sql->setQuery('SELECT name, count FROM ' . rex::getTable('pagestats_data') . ' WHERE type = "weekday" ORDER BY count DESC');

        return $sql;
    }


    /**
     *
     *
     * @return (string|false)[]
     * @throws InvalidArgumentException
     * @throws rex_sql_exception
     * @author Andreas Lenhardt
     */
    public function get_data()
    {
        $sql = $this->get_sql();

        $data = [
            0 => 0,
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 0,
        ];

        foreach ($sql as $row) {
            $data[intval($row->getValue('name')) - 1] = $row->getValue('count');
        }

        return $data;
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
        $list = rex_list::factory('SELECT name, count FROM ' . rex::getTable('pagestats_data') . ' where type = "weekday" ORDER BY count DESC', 10000);


        $list->setColumnLabel('name', $this->addon->i18n('statistics_name'));
        $list->setColumnLabel('count', $this->addon->i18n('statistics_count'));
        $list->setColumnFormat('name', 'custom',  function ($params) {
            switch ($params['value']) {
                case 1:
                    return $this->addon->i18n('statistics_monday');
                    break;
                case 2:
                    return $this->addon->i18n('statistics_tuesday');
                    break;
                case 3:
                    return $this->addon->i18n('statistics_wednesday');
                    break;
                case 4:
                    return $this->addon->i18n('statistics_thursday');
                    break;
                case 5:
                    return $this->addon->i18n('statistics_friday');
                    break;
                case 6:
                    return $this->addon->i18n('statistics_saturday');
                    break;
                case 7:
                    return $this->addon->i18n('statistics_sunday');
                    break;
            }
        });
        $list->addTableAttribute('class', 'dt_order_second statistics_table');

        if ($list->getRows() == 0) {
            $table = rex_view::info($this->addon->i18n('statistics_no_data'));
        } else {
            $table = $list->get();
        }

        return $table;
    }


    /**
     *
     *
     * @return array
     * @throws InvalidArgumentException
     * @throws rex_sql_exception
     * @author Andreas Lenhardt
     */
    public function get_data_dashboard()
    {
        $sql = $this->get_sql();

        $data = [
            0 => 0,
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 0,
        ];

        foreach ($sql as $row) {
            $data[intval($row->getValue('name')) - 1] = $row->getValue('count');
        }

        return $data;
    }
}
