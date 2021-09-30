<?php


/**
 * Helper class for the backend page "pages"
 * 
 * @author Andreas Lenhardt
 */
class pages_helper
{

    private $addon;
    public $date_start;
    public $date_end;



    /**
     * 
     * 
     * @param mixed $date_start 
     * @param mixed $date_end 
     * @return void 
     * @throws InvalidArgumentException 
     * @author Andreas Lenhardt
     */
    public function __construct($date_start, $date_end)
    {
        $this->addon = rex_addon::get('statistics');
        $this->date_start = $date_start;
        $this->date_end = $date_end;
    }


    /**
     * 
     * 
     * @return (string|false)[] 
     * @throws InvalidArgumentException 
     * @throws rex_sql_exception 
     * @author Andreas Lenhardt
     */
    public function sum_per_page()
    {
        $sql = rex_sql::factory();

        $sum_per_page = $sql->setQuery('SELECT url, COUNT(url) AS "count" from ' . rex::getTable('pagestats_dump') . ' where date between :start and :end GROUP BY url ORDER BY count DESC, url ASC', ['start' => $this->date_start->format('Y-m-d'), 'end' => $this->date_end->format('Y-m-d')]);

        $sum_per_page_labels = [];
        $sum_per_page_values = [];


        foreach ($sum_per_page as $row) {
            $sum_per_page_labels[] = $row->getValue('url');
        }
        if (array_keys($sum_per_page_labels) == []) {
            echo rex_view::error($this->addon->i18n('statistics_no_data'));
        }
        $sum_per_page_labels = json_encode($sum_per_page_labels);


        foreach ($sum_per_page as $row) {
            $sum_per_page_values[] = $row->getValue('count');
        }
        $sum_per_page_values = json_encode($sum_per_page_values);

        

        return [
            'labels' => $sum_per_page_labels,
            'values' => $sum_per_page_values,
        ];
    }

    /**
     * 
     * 
     * @param mixed $request_url 
     * @return null|int 
     * @throws InvalidArgumentException 
     * @throws rex_sql_exception 
     * @author Andreas Lenhardt
     */
    public function ignore_page($request_url)
    {
        $ignored_paths = $this->addon->getConfig('statistics_ignored_paths');
        $this->addon->setConfig('statistics_ignored_paths', $ignored_paths . PHP_EOL . $request_url);

        $sql = rex_sql::factory();
        $sql->setQuery('delete from ' . rex::getTable('pagestats_dump') . ' where url = :url', ['url' => $request_url]);

        return $sql->getRows();
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
        $list = rex_list::factory('SELECT url, COUNT(url) AS "count" from ' . rex::getTable('pagestats_dump') . ' where date between "' . $this->date_start->format('Y-m-d') . '" and "' . $this->date_end->format('Y-m-d') . '" GROUP BY url ORDER BY count DESC, url ASC', 1000);

        $list->setColumnLabel('url', $this->addon->i18n('statistics_url'));
        $list->setColumnLabel('count', $this->addon->i18n('statistics_count'));
        $list->setColumnParams('url', ['url' => '###url###', 'date_start' => $this->date_start->format('Y-m-d'), 'date_end' => $this->date_end->format('Y-m-d')]);

        $list->addColumn('edit', $this->addon->i18n('statistics_ignore_and_delete'));
        $list->setColumnLabel('edit', $this->addon->i18n('statistics_ignore'));
        $list->addLinkAttribute('edit', 'data-confirm', '###url###:' . PHP_EOL . $this->addon->i18n('statistics_confirm_ignore_delete'));
        $list->setColumnParams('edit', ['url' => '###url###', 'ignore_page' => true]);
        $list->addFormAttribute('style', 'margin-top: 3rem');
        $list->addTableAttribute('class', 'table-bordered dt_order_second');

        return $list->get();
    }
}
