<?php
class rex_dashboard_browsertype extends rex_dashboard_item_chart_pie
{
    public function getChartData()
    {

        $sql = rex_sql::factory();
        $result = $sql->setQuery('SELECT browsertype, count(browsertype) as "count" FROM ' . rex::getTable('pagestats_dump') . ' GROUP BY browsertype ORDER BY browsertype ASC');

        foreach ($result as $row) {
            $data[$row->getValue('browsertype')] = $row->getValue('count');
        }

        return $data;

    }
}
