<?php

/**
 * Provides data for the dashboard addon
 *
 * @author Andreas Lenhardt
 */
class rex_dashboard_hour extends rex_dashboard_item_chart_bar
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
        $hour = new stats_hour();
        return $hour->get_data_dashboard();
    }
}
