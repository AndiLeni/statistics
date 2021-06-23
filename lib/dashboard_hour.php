<?php
class rex_dashboard_hour extends rex_dashboard_item_chart_bar
{
    public function getChartData()
    {
        $sql = rex_sql::factory();
        $result = $sql->setQuery('SELECT hour, COUNT(hour) as "count" FROM ' . rex::getTable('pagestats_dump') . ' GROUP BY hour ORDER BY hour ASC');

        $data = [0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0, 11 => 0, 12 => 0, 13 => 0, 14 => 0, 15 => 0, 16 => 0, 17 => 0, 18 => 0, 19 => 0, 20 => 0, 21 => 0, 22 => 0, 23 => 0];

        foreach ($result as $row) {
            $data[$row->getValue('hour')] = $row->getValue('count');
        }

        return $data;
    }
}
