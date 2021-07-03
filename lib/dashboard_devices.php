<?php

/**
 * Provides data for the dashboard addon
 *
 * @author Andreas Lenhardt
 */
class rex_dashboard_browsertype extends rex_dashboard_item_chart_pie
{

    /**
     *
     *
     * @return array
     * @throws InvalidArgumentException
     * @throws rex_sql_exception
     * @author Andreas Lenhardt
     */
    public function getChartData()
    {
        $browsertype = new stats_browsertype();
        return $browsertype->get_data_dashboard();
    }
}
