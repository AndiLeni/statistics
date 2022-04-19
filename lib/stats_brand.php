<?php

/**
 * Handles the "brand" data for statistics
 *
 */
class stats_brand
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

        $result = $sql->setQuery('SELECT name, count FROM ' . rex::getTable('pagestats_data') . ' WHERE type = "brand" ORDER BY count DESC');

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

        $data = [];

        foreach ($sql as $row) {
            $data[] = [
                'name' => $row->getValue('name'),
                'value' => $row->getValue('count')
            ];
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
        $addon = rex_addon::get('statistics');

        $list = rex_list::factory('SELECT name, count FROM ' . rex::getTable('pagestats_data') . ' where type = "brand" ORDER BY count DESC', 10000);

        $list->setColumnLabel('name', $addon->i18n('statistics_name'));
        $list->setColumnLabel('count', $addon->i18n('statistics_count'));
        $list->addTableAttribute('class', 'dt_order_second statistics_table');

        if ($list->getRows() == 0) {
            $table = rex_view::info($addon->i18n('statistics_no_data'));
        } else {
            $table = $list->get();
        }

        return $table;
    }
}
