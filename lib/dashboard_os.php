<?php

/**
 * Provides data for the dashboard addon
 *
 */
class rex_dashboard_os extends rex_dashboard_item_chart_pie
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
        $os = new stats_os();
        return $os->get_data_dashboard();
    }
}
