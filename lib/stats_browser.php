<?php

/**
 * Handles the "browser" data for statistics
 *
 * @author Andreas Lenhardt
 */
class stats_browser
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

        $result = $sql->setQuery('SELECT browser, COUNT(browser) as "count" FROM ' . rex::getTable('pagestats_dump') . ' where date between :start and :end GROUP BY browser ORDER BY count DESC', ['start' => $this->start_date->format('Y-m-d'), 'end' => $this->end_date->format('Y-m-d')]);

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
        $addon = rex_addon::get('statistics');

        $list = rex_list::factory('SELECT browser, COUNT(browser) as "count" FROM ' . rex::getTable('pagestats_dump') . ' where date between "' . $this->start_date->format('Y-m-d') . '" and "' . $this->end_date->format('Y-m-d') . '" GROUP BY browser ORDER BY count DESC');

        $list->setColumnLabel('browser', $addon->i18n('statistics_name'));
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
