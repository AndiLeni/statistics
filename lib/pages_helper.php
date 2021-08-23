<?php


/**
 * Helper class for the backend page "pages"
 * 
 * @author Andreas Lenhardt
 */
class PagesHelper
{

    private $addon;
    public $request_date_start;
    public $request_date_end;
    public $max_date;
    public $min_date;


    /**
     * 
     * 
     * @param mixed $request_date_start 
     * @param mixed $request_date_end 
     * @return void 
     * @throws InvalidArgumentException 
     * @author Andreas Lenhardt
     */
    public function __construct($request_date_start, $request_date_end)
    {
        $this->addon = rex_addon::get('statistics');
        $this->request_date_start = $request_date_start;
        $this->request_date_end = $request_date_end;
    }

    /**
     * 
     * 
     * @return bool 
     * @throws InvalidArgumentException 
     * @throws rex_sql_exception 
     * @author Andreas Lenhardt
     */
    public function filterValid()
    {
        // date filter
        $sql = rex_sql::factory();

        if ($this->request_date_end == '' || $this->request_date_start == '') {

            $max_date = $sql->setQuery('SELECT MAX(date) AS "date" from ' . rex::getTable('pagestats_dump'));
            $max_date = $max_date->getValue('date');
            $this->max_date = new DateTime($max_date);
            $this->max_date->modify('+1 day');

            $min_date = $sql->setQuery('SELECT MIN(date) AS "date" from ' . rex::getTable('pagestats_dump'));
            $min_date = $min_date->getValue('date');
            $this->min_date = new DateTime($min_date);

            return true;
        } else {

            $this->max_date = new DateTime($this->request_date_end);
            $this->min_date = new DateTime($this->request_date_start);

            if ($this->min_date > $this->max_date) {
                $this->min_date = new DateTime();
                $this->max_date = new DateTime();
                $this->max_date->modify('+1 day');
                return false;
            }
            return true;
        }
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

        if ($this->request_date_start != '' && $this->request_date_end != '') {
            $sum_per_page = $sql->setQuery('SELECT url, COUNT(url) AS "count" from ' . rex::getTable('pagestats_dump') . ' where date between :start and :end GROUP BY url ORDER BY count DESC, url ASC', ['start' => $this->min_date->format('Y-m-d'), 'end' => $this->max_date->format('Y-m-d')]);
        } else {
            $sum_per_page = $sql->setQuery('SELECT url, COUNT(url) as "count" from ' . rex::getTable('pagestats_dump') . ' GROUP BY url ORDER BY count DESC, url ASC');
        }

        $sum_per_page_labels = [];
        $sum_per_page_values = [];


        foreach ($sum_per_page as $row) {
            $sum_per_page_labels[] = $row->getValue('url');
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
        $ignored_paths = $this->addon->getConfig('pagestats_ignored_paths');
        $this->addon->setConfig('pagestats_ignored_paths', $ignored_paths . PHP_EOL . $request_url);

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
        if ($this->request_date_start != '' and $this->request_date_end != '') {
            $list = rex_list::factory('SELECT url, COUNT(url) AS "count" from ' . rex::getTable('pagestats_dump') . ' where date between "' . $this->min_date->format('Y-m-d') . '" and "' . $this->max_date->format('Y-m-d') . '" GROUP BY url ORDER BY count DESC, url ASC', 1000);
        } else {
            $list = rex_list::factory('SELECT url, COUNT(url) AS "count" from ' . rex::getTable('pagestats_dump') . ' GROUP BY url ORDER BY count DESC, url ASC', 1000);
        }

        $list->setColumnLabel('url', $this->addon->i18n('statistics_url'));
        $list->setColumnLabel('count', $this->addon->i18n('statistics_count'));
        $list->setColumnParams('url', ['url' => '###url###', 'date_start' => $this->request_date_start, 'date_end' => $this->request_date_end]);

        $list->addColumn('edit', $this->addon->i18n('statistics_ignore_and_delete'));
        $list->setColumnLabel('edit', $this->addon->i18n('statistics_ignore'));
        $list->addLinkAttribute('edit', 'data-confirm', '###url###:' . PHP_EOL . $this->addon->i18n('statistics_confirm_ignore_delete'));
        $list->setColumnParams('edit', ['url' => '###url###', 'ignore_page' => true]);
        $list->addFormAttribute('style', 'margin-top: 3rem');
        $list->addTableAttribute('class', 'table-bordered');
        $list->addTableAttribute('class', 'dt_order_second');

        return $list->get();
    }
}
