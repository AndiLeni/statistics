<?php

namespace AndiLeni\Statistics;

use InvalidArgumentException;
use rex_sql_exception;

/**
 * Provides data for the dashboard addon
 *
 */
class stats_weekday_dashboard extends \rex_dashboard_item_chart_bar
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
        $weekday = new stats_weekday();
        return $weekday->get_data_dashboard();
    }
}
