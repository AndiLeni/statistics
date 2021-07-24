<?php

/**
 * Handles the devices-"os" data for statistics
 *
 * @author Andreas Lenhardt
 */
class stats_os
{
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
        $result = $sql->setQuery('SELECT os, COUNT(os) as "count" FROM ' . rex::getTable('pagestats_dump') . ' GROUP BY os ORDER BY count DESC');

        $data = [];

        foreach ($result as $row) {
            $data[$row->getValue('os')] = $row->getValue('count');
        }

        return $data;
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
        $data = $this->get_sql();

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
        $addon = rex_addon::get('statistics');
        $list = rex_list::factory('SELECT os, COUNT(os) as "count" FROM ' . rex::getTable('pagestats_dump') . ' GROUP BY os ORDER BY count DESC');
        $list->setColumnLabel('os', $addon->i18n('statistics_name'));
        $list->setColumnLabel('count', $addon->i18n('statistics_count'));
        $list->addTableAttribute('class', 'dt_order_second');

        return $list->get();
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
        $data = $this->get_sql();

        return $data;
    }
}
