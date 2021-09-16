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
        $browsertype = new stats_browsertype(new DateTime('1900-01-01'), new DateTime('2100-12-31'));
        return $browsertype->get_data_dashboard();
    }
}
