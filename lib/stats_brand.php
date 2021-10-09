<?php

/**
 * Handles the "brand" data for statistics
 *
 * @author Andreas Lenhardt
 */
class stats_brand
{

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
        $this->start_date = $start_date;
        $this->end_date = $end_date;
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

        // $result = $sql->setQuery('SELECT brand, COUNT(brand) as "count" FROM ' . rex::getTable('pagestats_dump') . ' where date between :start and :end GROUP BY brand ORDER BY count DESC', ['start' => $this->start_date->format('Y-m-d'), 'end' => $this->end_date->format('Y-m-d')]);

        $result = $sql->setQuery('SELECT name, count FROM ' . rex::getTable('pagestats_data') . ' WHERE type = "brand" ORDER BY count DESC');

        $data = [];

        foreach ($result as $row) {
            $data[$row->getValue('name')] = $row->getValue('count');
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

        // $list = rex_list::factory('SELECT brand, COUNT(brand) as "count" FROM ' . rex::getTable('pagestats_dump') . ' where date between "' . $this->start_date->format('Y-m-d') . '" and "' . $this->end_date->format('Y-m-d') . '" GROUP BY brand ORDER BY count DESC', 10000);

        $list = rex_list::factory('SELECT name, count FROM ' . rex::getTable('pagestats_data') . ' where type = "brand" ORDER BY count DESC', 10000);

        $list->setColumnLabel('brand', $addon->i18n('statistics_name'));
        $list->setColumnLabel('count', $addon->i18n('statistics_count'));
        $list->addTableAttribute('class', 'dt_order_second');

        return $list->get();
    }
}
