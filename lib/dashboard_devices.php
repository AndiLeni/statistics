<?php

namespace AndiLeni\Statistics;

use InvalidArgumentException;
use rex_sql_exception;

/**
 * Provides data for the dashboard addon
 *
 */
class rex_dashboard_browsertype extends \rex_dashboard_item_chart_pie
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
        $browsertype = new stats_browsertype();
        return $browsertype->get_data_dashboard();
    }
}
