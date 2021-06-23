<?php
class rex_dashboard_browsertype extends rex_dashboard_item_chart_pie
{
    public function getChartData()
    {
        $browsertype = new stats_browsertype();
        return $browsertype->get_data_dashboard();

    }
}
