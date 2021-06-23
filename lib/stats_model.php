<?php
class stats_model
{
    private function get_sql()
    {
        $sql = rex_sql::factory();
        $result = $sql->setQuery('SELECT model, COUNT(model) as "count" FROM ' . rex::getTable('pagestats_dump') . ' GROUP BY model ORDER BY count DESC');

        foreach ($result as $row) {
            $data[$row->getValue('model')] = $row->getValue('count');
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
        $list = rex_list::factory('SELECT model, COUNT(model) as "count" FROM ' . rex::getTable('pagestats_dump') . ' GROUP BY model ORDER BY count DESC');
        $list->setColumnLabel('model', 'Name');
        $list->setColumnLabel('count', 'Anzahl');
        $list->setColumnSortable('model', $direction = 'asc');
        $list->setColumnSortable('count', $direction = 'asc');

        return $list->get();
    }
}
