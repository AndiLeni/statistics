<?php

/**
 * Provides data for the dashboard addon
 *
 */
class rex_dashboard_hour extends rex_dashboard_item_chart_bar
{

    /**
     *
     *
     * @return array
     * @throws InvalidArgumentException
     * @throws rex_sql_exception
     */
    public function getChartData(): array
    {
        $hour = new stats_hour();
        return $hour->get_data_dashboard();
    }
}
