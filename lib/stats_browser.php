<?php


/**
 * Handles the "browser" data for statistics
 * 
 * @author Andreas Lenhardt
 */
class stats_browser
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
        $result = $sql->setQuery('SELECT browser, COUNT(browser) as "count" FROM ' . rex::getTable('pagestats_dump') . ' GROUP BY browser ORDER BY count DESC');

        $data = [];

        foreach ($result as $row) {
            $data[$row->getValue('browser')] = $row->getValue('count');
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
        $list = rex_list::factory('SELECT browser, COUNT(browser) as "count" FROM ' . rex::getTable('pagestats_dump') . ' GROUP BY browser ORDER BY count DESC');
        $list->setColumnLabel('browser', 'Name');
        $list->setColumnLabel('count', 'Anzahl');
        $list->setColumnSortable('browser', $direction = 'asc');
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
