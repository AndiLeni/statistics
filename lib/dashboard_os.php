<?php

/**
 * Provides data for the dashboard addon
 *
 * @author Andreas Lenhardt
 */
class rex_dashboard_os extends rex_dashboard_item_chart_pie
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
        $os = new stats_os('1900-01-01', '2100-12-31');
        return $os->get_data_dashboard();
    }
}
