<?php
class rex_dashboard_hour extends rex_dashboard_item_chart_bar
{
    public function getChartData()
    {
        $hour = new stats_hour();
        return $hour->get_data_dashboard();
    }
}
