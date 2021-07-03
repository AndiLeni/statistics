<?php


/**
 * Provides data for the dashboard addon
 * 
 * @author Andreas Lenhardt
 */
class stats_weekday_dashboard extends rex_dashboard_item_chart_bar
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
        $weekday = new stats_weekday();
        return $weekday->get_data_dashboard();
    }

}
