<?php
class rex_dashboard_weekday extends rex_dashboard_item_chart_bar
{
    private function get_weekday($weekday)
    {
        switch ($weekday["value"]) {
            case 1:
                return "Montag";
                break;
            case 2:
                return "Dienstag";
                break;
            case 3:
                return "Mittwoch";
                break;
            case 4:
                return "Donnerstag";
                break;
            case 5:
                return "Freitag";
                break;
            case 6:
                return "Samstag";
                break;
            case 7:
                return "Sonntag";
                break;
        }
    }
    public function getChartData()
    {
        $sql = rex_sql::factory();
        $result = $sql->setQuery('SELECT weekday, COUNT(weekday) as "count" FROM ' . rex::getTable('pagestats_dump') . ' GROUP BY weekday ORDER BY weekday ASC');

        $data = ["Montag" => 0, "Dienstag" => 0, "Mittwoch" => 0, "Donnerstag" => 0, "Freitag" => 0, "Samstag" => 0, "Sonntag" => 0];

        foreach ($result as $row) {
            $data[$this->get_weekday($row->getValue('weekday'))] = $row->getValue('count');
        }

        return $data;
    }
}
