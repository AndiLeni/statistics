<?php
class stats_weekday_dashboard extends rex_dashboard_item_chart_bar
{
    public function getChartData()
    {
        $weekday = new stats_weekday();
        return $weekday->get_data_dashboard();
    }

}
