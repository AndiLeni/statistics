<?php

/**
 * Handles the device-"model" data for statistics
 *
 * @author Andreas Lenhardt
 */
class stats_model
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
        $result = $sql->setQuery('SELECT model, COUNT(model) as "count" FROM ' . rex::getTable('pagestats_dump') . ' GROUP BY model ORDER BY count DESC');

        $data = [];

        foreach ($result as $row) {
            $data[$row->getValue('model')] = $row->getValue('count');
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
        $list = rex_list::factory('SELECT model, COUNT(model) as "count" FROM ' . rex::getTable('pagestats_dump') . ' GROUP BY model ORDER BY count DESC');
        $list->setColumnLabel('model', 'Name');
        $list->setColumnLabel('count', 'Anzahl');
        $list->setColumnSortable('model', $direction = 'asc');
        $list->setColumnSortable('count', $direction = 'asc');

        return $list->get();
    }
}
