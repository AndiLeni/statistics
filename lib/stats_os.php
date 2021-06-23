<?php
class stats_os
{
    private function get_sql()
    {
        $sql = rex_sql::factory();
        $result = $sql->setQuery('SELECT os, COUNT(os) as "count" FROM ' . rex::getTable('pagestats_dump') . ' GROUP BY os ORDER BY count DESC');

        foreach ($result as $row) {
            $data[$row->getValue('os')] = $row->getValue('count');
        }

        return $data;
    }
    public function get_data()
    {
        $data = $this->get_sql();

        return [
            'labels' => json_encode(array_keys($data)),
            'values' => json_encode(array_values($data)),
        ];
    }

    public function get_list()
    {
        $list = rex_list::factory('SELECT os, COUNT(os) as "count" FROM ' . rex::getTable('pagestats_dump') . ' GROUP BY os ORDER BY count DESC');
        $list->setColumnLabel('os', 'Name');
        $list->setColumnLabel('count', 'Anzahl');
        $list->setColumnSortable('os', $direction = 'asc');
        $list->setColumnSortable('count', $direction = 'asc');

        return $list->get();
    }

    public function get_data_dashboard()
    {
        $data = $this->get_sql();

        return $data;
    }
}
