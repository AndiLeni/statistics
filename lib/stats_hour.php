<?php

/**
 * Handles the "hour" data for statistics
 *
 */
class stats_hour
{

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

        $result = $sql->setQuery('SELECT name, count FROM ' . rex::getTable('pagestats_data') . ' WHERE type = "hour" ORDER BY count DESC');

        return $result;
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

        $hours = [0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0, 11 => 0, 12 => 0, 13 => 0, 14 => 0, 15 => 0, 16 => 0, 17 => 0, 18 => 0, 19 => 0, 20 => 0, 21 => 0, 22 => 0, 23 => 0];

        foreach ($sql as $row) {
            $hours[intval($row->getValue('name'))] = $row->getValue('count');
        }

        return $hours;
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
        $addon = rex_addon::get('statistics');

        $list = rex_list::factory('SELECT name, count FROM ' . rex::getTable('pagestats_data') . ' where type = "hour" ORDER BY count DESC', 10000);

        $list->setColumnLabel('name', $addon->i18n('statistics_name'));
        $list->setColumnLabel('count', $addon->i18n('statistics_count'));
        $list->setColumnFormat('hour', 'custom',  function ($params) {

            $hour = $params['value'];
            if (strlen($hour) == 1) {
                return '0' . $hour . ' Uhr';
            } else {
                return $hour . ' Uhr';
            }
        });
        $list->addTableAttribute('class', 'dt_order_second statistics_table');

        if ($list->getRows() == 0) {
            $table = rex_view::info($addon->i18n('statistics_no_data'));
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

        $hours = [0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0, 11 => 0, 12 => 0, 13 => 0, 14 => 0, 15 => 0, 16 => 0, 17 => 0, 18 => 0, 19 => 0, 20 => 0, 21 => 0, 22 => 0, 23 => 0];

        foreach ($sql as $row) {
            $hours[intval($row->getValue('name'))] = $row->getValue('count');
        }

        return $hours;
    }
}
