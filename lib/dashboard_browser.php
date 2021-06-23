<?php
class rex_dashboard_browser extends rex_dashboard_item_chart_pie
{
    public function getChartData()
    {

        $sql = rex_sql::factory();
        $result = $sql->setQuery('SELECT browser, count(browser) as "count" FROM ' . rex::getTable('pagestats_dump') . ' GROUP BY browser ORDER BY browser ASC');

        foreach ($result as $row) {
            $data[$row->getValue('browser')] = $row->getValue('count');
        }

        return $data;

    }
}
