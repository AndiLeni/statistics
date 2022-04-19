<?php

/**
 * Handles the "weekday" data for statistics
 *
 */
class stats_weekday
{

    private rex_addon $addon;


    /**
     * 
     * 
     * @return void 
     * @throws InvalidArgumentException 
     */
    public function __construct()
    {
        $this->addon = rex_addon::get('statistics');
    }


    /**
     * 
     * 
     * @return rex_sql 
     * @throws InvalidArgumentException 
     * @throws rex_sql_exception 
     */
    private function get_sql(): rex_sql
    {
        $sql = rex_sql::factory();

        $result = $sql->setQuery('SELECT name, count FROM ' . rex::getTable('pagestats_data') . ' WHERE type = "weekday" ORDER BY count DESC');

        return $sql;
    }


    /**
     * 
     * 
     * @return array 
     * @throws InvalidArgumentException 
     * @throws rex_sql_exception 
     */
    public function get_data(): array
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
     */
    public function get_list(): string
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
     */
    public function get_data_dashboard(): array
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
