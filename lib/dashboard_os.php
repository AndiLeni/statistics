<?php
class rex_dashboard_os extends rex_dashboard_item_chart_pie
{
    public function getChartData()
    {

        $sql = rex_sql::factory();
        $result = $sql->setQuery('SELECT os, count(os) as "count" FROM ' . rex::getTable('pagestats_dump') . ' GROUP BY os ORDER BY os ASC');

        foreach ($result as $row) {
            $data[$row->getValue('os')] = $row->getValue('count');
        }

        return $data;

    }
}
