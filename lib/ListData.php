<?php

class ListData
{
    private $filter_date_helper;
    private $addon;

    public function __construct($filter_date_helper)
    {
        $this->filter_date_helper = $filter_date_helper;
        $this->addon = rex_addon::get('statistics');
    }

    public function get_lists_daily()
    {
        $list_dates = rex_list::factory('SELECT date, sum(count) as "count" FROM ' . rex::getTable('pagestats_visits_per_day') . ' where date between "' . $this->filter_date_helper->date_start->format('Y-m-d') . '" and "' . $this->filter_date_helper->date_end->format('Y-m-d') . '" group by date ORDER BY count DESC', 10000);
        $list_dates->setColumnLabel('date', 'Datum');
        $list_dates->setColumnLabel('count', 'Anzahl');
        $list_dates->setColumnParams('url', ['url' => '###url###']);
        $list_dates->addTableAttribute('class', 'table-bordered dt_order_first statistics_table');
        $list_dates->setColumnLayout('date', ['<th>###VALUE###</th>', '<td data-sort="###date###">###VALUE###</td>']);
        $list_dates->setColumnFormat('date', 'date', 'd.m.Y');

        if ($list_dates->getRows() == 0) {
            $table = '<h3>Besuche:</h3>' . rex_view::info($this->addon->i18n('statistics_no_data'));
        } else {
            $table = '<h3>Besuche:</h3>' . $list_dates->get();
        }

        $table .= '<hr>';

        $list_dates = rex_list::factory('SELECT date, sum(count) as "count" FROM ' . rex::getTable('pagestats_visitors_per_day') . ' where date between "' . $this->filter_date_helper->date_start->format('Y-m-d') . '" and "' . $this->filter_date_helper->date_end->format('Y-m-d') . '" group by date ORDER BY count DESC', 10000);
        $list_dates->setColumnLabel('date', 'Datum');
        $list_dates->setColumnLabel('count', 'Anzahl');
        $list_dates->addTableAttribute('class', 'table-bordered dt_order_first statistics_table');
        $list_dates->setColumnLayout('date', ['<th>###VALUE###</th>', '<td data-sort="###date###">###VALUE###</td>']);
        $list_dates->setColumnFormat('date', 'date', 'd.m.Y');

        if ($list_dates->getRows() == 0) {
            $table .= '<h3>Besucher:</h3>' . rex_view::info($this->addon->i18n('statistics_no_data'));
        } else {
            $table .= '<h3>Besucher:</h3>' . $list_dates->get();
        }

        $fragment_collapse = new rex_fragment();
        $fragment_collapse->setVar('title', $this->addon->i18n('statistics_views_per_day'));
        $fragment_collapse->setVar('content', $table, false);

        return $fragment_collapse;
    }




    public function get_lists_monthly()
    {
        $list_dates = rex_list::factory('SELECT DATE_FORMAT(date,"%m.%Y") as "month", IFNULL(sum(count),0) as "count" FROM ' . rex::getTable('pagestats_visits_per_day') . ' GROUP BY month ORDER BY date DESC', 10000);
        $list_dates->setColumnLabel('date', 'Datum');
        $list_dates->setColumnLabel('count', 'Anzahl');
        $list_dates->setColumnParams('url', ['url' => '###url###']);
        $list_dates->addTableAttribute('class', 'table-bordered dt_order_first statistics_table');
        // $list_dates->setColumnLayout('date', ['<th>###VALUE###</th>', '<td data-sort="###date###">###VALUE###</td>']);
        $list_dates->setColumnFormat('date', 'date', 'M Y');

        if ($list_dates->getRows() == 0) {
            $table = '<h3>Besuche:</h3>' . rex_view::info($this->addon->i18n('statistics_no_data'));
        } else {
            $table = '<h3>Besuche:</h3>' . $list_dates->get();
        }

        $table .= '<hr>';

        $list_dates = rex_list::factory('SELECT DATE_FORMAT(date,"%m.%Y") as "month", IFNULL(sum(count),0) as "count" FROM ' . rex::getTable('pagestats_visitors_per_day') . ' GROUP BY month ORDER BY count DESC', 10000);
        $list_dates->setColumnLabel('date', 'Datum');
        $list_dates->setColumnLabel('count', 'Anzahl');
        $list_dates->addTableAttribute('class', 'table-bordered dt_order_first statistics_table');
        // $list_dates->setColumnLayout('date', ['<th>###VALUE###</th>', '<td data-sort="###date###">###VALUE###</td>']);
        $list_dates->setColumnFormat('date', 'date', 'M Y');

        if ($list_dates->getRows() == 0) {
            $table .= '<h3>Besucher:</h3>' . rex_view::info($this->addon->i18n('statistics_no_data'));
        } else {
            $table .= '<h3>Besucher:</h3>' . $list_dates->get();
        }

        $fragment_collapse = new rex_fragment();
        $fragment_collapse->setVar('title', $this->addon->i18n('statistics_views_per_day'));
        $fragment_collapse->setVar('content', $table, false);

        return $fragment_collapse;
    }



    public function get_lists_yearly()
    {
        $list_dates = rex_list::factory('SELECT DATE_FORMAT(date,"%Y") as "year", IFNULL(sum(count),0) as "count" FROM ' . rex::getTable('pagestats_visits_per_day') . ' GROUP BY year ORDER BY count DESC', 10000);
        $list_dates->setColumnLabel('date', 'Datum');
        $list_dates->setColumnLabel('count', 'Anzahl');
        $list_dates->setColumnParams('url', ['url' => '###url###']);
        $list_dates->addTableAttribute('class', 'table-bordered dt_order_first statistics_table');
        $list_dates->setColumnLayout('date', ['<th>###VALUE###</th>', '<td data-sort="###date###">###VALUE###</td>']);
        $list_dates->setColumnFormat('date', 'date', 'd.m.Y');

        if ($list_dates->getRows() == 0) {
            $table = '<h3>Besuche:</h3>' . rex_view::info($this->addon->i18n('statistics_no_data'));
        } else {
            $table = '<h3>Besuche:</h3>' . $list_dates->get();
        }

        $table .= '<hr>';

        $list_dates = rex_list::factory('SELECT DATE_FORMAT(date,"%Y") as "year", IFNULL(sum(count),0) as "count" FROM ' . rex::getTable('pagestats_visitors_per_day') . ' GROUP BY year ORDER BY count DESC', 10000);
        $list_dates->setColumnLabel('date', 'Datum');
        $list_dates->setColumnLabel('count', 'Anzahl');
        $list_dates->addTableAttribute('class', 'table-bordered dt_order_first statistics_table');
        $list_dates->setColumnLayout('date', ['<th>###VALUE###</th>', '<td data-sort="###date###">###VALUE###</td>']);
        $list_dates->setColumnFormat('date', 'date', 'd.m.Y');

        if ($list_dates->getRows() == 0) {
            $table .= '<h3>Besucher:</h3>' . rex_view::info($this->addon->i18n('statistics_no_data'));
        } else {
            $table .= '<h3>Besucher:</h3>' . $list_dates->get();
        }

        $fragment_collapse = new rex_fragment();
        $fragment_collapse->setVar('title', $this->addon->i18n('statistics_views_per_day'));
        $fragment_collapse->setVar('content', $table, false);

        return $fragment_collapse;
    }
}
