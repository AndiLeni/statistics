<?php


/**
 * Provides data for the dashboard addon
 * 
 * @author Andreas Lenhardt
 */
class rex_dashboard_browser extends rex_dashboard_item_chart_pie
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
        $browser = new stats_browser();
        return $browser->get_data_dashboard();
    }
}
