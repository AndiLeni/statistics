<?php

/**
 * Handles the "brand" data for statistics
 *
 * @author Andreas Lenhardt
 */
class stats_brand
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
        $result = $sql->setQuery('SELECT brand, COUNT(brand) as "count" FROM ' . rex::getTable('pagestats_dump') . ' GROUP BY brand ORDER BY count DESC');

        $data = [];

        foreach ($result as $row) {
            $data[$row->getValue('brand')] = $row->getValue('count');
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
        $list = rex_list::factory('SELECT brand, COUNT(brand) as "count" FROM ' . rex::getTable('pagestats_dump') . ' GROUP BY brand ORDER BY count DESC');
        $list->setColumnLabel('brand', $addon->i18n('statistics_name'));
        $list->setColumnLabel('count', $addon->i18n('statistics_count'));

        return $list->get();
    }
}
