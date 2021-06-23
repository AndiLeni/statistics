<?php
class rex_dashboard_browser extends rex_dashboard_item_chart_pie
{
    public function getChartData()
    {
        $browser = new stats_browser();
        return $browser->get_data_dashboard();
    }
}
