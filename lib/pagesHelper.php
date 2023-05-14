<?php

namespace AndiLeni\Statistics;

use rex;
use rex_addon;
use rex_list;
use rex_sql;
use rex_view;
use InvalidArgumentException;
use rex_sql_exception;
use rex_exception;

/**
 * Helper class for the backend page "pages"
 * 
 */
class pagesHelper
{

    private rex_addon $addon;
    private filterDateHelper $filter_date_helper;


    /**
     * 
     * 
     * @param filterDateHelper $filter_date_helper 
     * @return void 
     * @throws InvalidArgumentException 
     */
    public function __construct(filterDateHelper $filter_date_helper)
    {
        $this->addon = rex_addon::get('statistics');
        $this->filter_date_helper = $filter_date_helper;
    }


    /**
     * 
     * 
     * @return array 
     * @throws InvalidArgumentException 
     * @throws rex_sql_exception 
     */
    public function sum_per_page(): array
    {
        $sql = rex_sql::factory();

        $sum_per_page = $sql->setQuery('SELECT url, ifnull(sum(count),0) as "count" from ' . rex::getTable('pagestats_visits_per_url') . ' where date between :start and :end group by url ORDER BY count DESC, url ASC', ['start' => $this->filter_date_helper->date_start->format('Y-m-d'), 'end' => $this->filter_date_helper->date_end->format('Y-m-d')]);

        $sum_per_page_labels = [];
        $sum_per_page_values = [];


        foreach ($sum_per_page as $row) {
            $sum_per_page_labels[] = $row->getValue('url');
        }
        if (array_keys($sum_per_page_labels) == []) {
            echo rex_view::error($this->addon->i18n('statistics_no_data'));
        }
        $sum_per_page_labels = $sum_per_page_labels;


        foreach ($sum_per_page as $row) {
            $sum_per_page_values[] = $row->getValue('count');
        }
        $sum_per_page_values = $sum_per_page_values;



        return [
            'labels' => $sum_per_page_labels,
            'values' => $sum_per_page_values,
        ];
    }



    /**
     * 
     * 
     * @param string $request_url 
     * @return int 
     * @throws InvalidArgumentException 
     * @throws rex_sql_exception 
     */
    public function ignore_page(string $request_url): int
    {
        $ignored_paths = $this->addon->getConfig('statistics_ignored_paths');
        if ($ignored_paths == "") {
            $this->addon->setConfig('statistics_ignored_paths', $request_url);
        } else {
            $this->addon->setConfig('statistics_ignored_paths', $ignored_paths . PHP_EOL . $request_url);
        }

        $sql = rex_sql::factory();

        // get sum per day for substraction
        $sum_per_day = $sql->getArray('select date, sum(count) as "count" from ' . rex::getTable('pagestats_visits_per_url') . ' where url = :url group by date', ['url' => $request_url]);

        // reduce visits per day by these factors
        foreach ($sum_per_day as $e) {
            $sql->setQuery('update ' . rex::getTable('pagestats_visits_per_day') . ' set count = count - :v where date = :date', ['v' => $e['count'], 'date' => $e['date']]);
        }

        $sql->setQuery('delete from ' . rex::getTable('pagestats_visits_per_url') . ' where url = :url', ['url' => $request_url]);

        return $sql->getRows() ?? 0;
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
        $list = rex_list::factory('SELECT url, sum(count) as "count" from ' . rex::getTable('pagestats_visits_per_url') . ' where date between "' . $this->filter_date_helper->date_start->format('Y-m-d') . '" and "' . $this->filter_date_helper->date_end->format('Y-m-d') . '" GROUP BY url ORDER BY count DESC, url ASC', 10000);

        $list->setColumnLabel('url', $this->addon->i18n('statistics_url'));
        $list->setColumnLabel('count', $this->addon->i18n('statistics_count'));
        $list->setColumnParams('url', ['url' => '###url###', 'date_start' => $this->filter_date_helper->date_start->format('Y-m-d'), 'date_end' => $this->filter_date_helper->date_end->format('Y-m-d')]);

        $list->addColumn('edit', $this->addon->i18n('statistics_ignore_and_delete'));
        $list->setColumnLabel('edit', $this->addon->i18n('statistics_ignore'));
        $list->addLinkAttribute('edit', 'data-confirm', '###url###:' . PHP_EOL . $this->addon->i18n('statistics_confirm_ignore_delete'));
        $list->setColumnParams('edit', ['url' => '###url###', 'ignore_page' => true]);
        $list->addFormAttribute('style', 'margin-top: 3rem');
        $list->addTableAttribute('class', 'table-bordered dt_order_second statistics_table');

        if ($list->getRows() == 0) {
            $table = rex_view::info($this->addon->i18n('statistics_no_data'));
        } else {
            $table = $list->get();
        }

        return $table;
    }
}
