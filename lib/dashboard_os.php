<?php
class rex_dashboard_os extends rex_dashboard_item_chart_pie
{
    public function getChartData()
    {
        $os = new stats_os();
        return $os->get_data_dashboard();
    }
}
