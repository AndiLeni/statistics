<?php

/**
 * Handles the "browsertype" data for statistics
 *
 * @author Andreas Lenhardt
 */
class stats_browsertype
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
        $result = $sql->setQuery('SELECT browsertype, COUNT(browsertype) as "count" FROM ' . rex::getTable('pagestats_dump') . ' GROUP BY browsertype ORDER BY count DESC');

        $data = [];

        foreach ($result as $row) {
            $data[$row->getValue('browsertype')] = $row->getValue('count');
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
        $list = rex_list::factory('SELECT browsertype, COUNT(browsertype) as "count" FROM ' . rex::getTable('pagestats_dump') . ' GROUP BY browsertype ORDER BY count DESC');
        $list->setColumnLabel('browsertype', 'Name');
        $list->setColumnLabel('count', 'Anzahl');
        $list->setColumnSortable('browsertype', $direction = 'asc');
        $list->setColumnSortable('count', $direction = 'asc');

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
