<?php

namespace AndiLeni\Statistics;

use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeImmutable;
use InvalidArgumentException;
use rex;
use rex_addon;
use rex_sql;
use rex_sql_exception;

class chartData
{

    private filterDateHelper $filter_date_helper;
    private rex_addon $addon;



    /**
     * 
     * 
     * @param filterDateHelper $filter_date_helper 
     * @return void 
     * @throws InvalidArgumentException 
     */
    public function __construct(filterDateHelper $filter_date_helper)
    {
        $this->filter_date_helper = $filter_date_helper;
        $this->addon = rex_addon::get('statistics');
    }


    /**
     * 
     * 
     * @return array 
     */
    private function get_labels(): array
    {
        // modify end date, because sql includes start and end, php ommits end
        $period = new DatePeriod(
            $this->filter_date_helper->date_start,
            new DateInterval('P1D'),
            $this->filter_date_helper->date_end->modify('+1 day')
        );

        $labels = [];
        foreach ($period as $value) {
            $labels[] = $value->format("d.m.Y");
        }

        return $labels;
    }


    /**
     * 
     * 
     * @return array 
     * @throws InvalidArgumentException 
     * @throws rex_sql_exception 
     */
    public function get_main_chart_data(): array
    {
        $data_visits = $this->get_visits_per_day();

        $data_visitors = $this->get_visitors_per_day();

        $data_chart = array_merge($data_visits, $data_visitors);

        $xaxis_values = $this->get_labels();

        $legend_values = array_column($data_chart, 'name');

        return [
            'series' => $data_chart,
            'legend' => $legend_values,
            'xaxis' => $xaxis_values,
        ];
    }


    /**
     * 
     * 
     * @return array 
     * @throws InvalidArgumentException 
     * @throws rex_sql_exception 
     */
    private function get_visits_per_day(): array
    {
        // DATA COLLECTION FOR MAIN CHART, "VIEWS PER DAY"

        // modify end date, because sql includes start and end, php ommits end
        $period = new DatePeriod(
            $this->filter_date_helper->date_start,
            new DateInterval('P1D'),
            $this->filter_date_helper->date_end->modify('+1 day')
        );

        $sql = rex_sql::factory();
        $domains = $sql->getArray('select distinct domain from ' . rex::getTable('pagestats_visits_per_day'));

        $data_chart_visits = [];

        // "total"
        $sql_data = $sql->setQuery('SELECT date, ifnull(sum(count),0) as "count" from ' . rex::getTable('pagestats_visits_per_day') . ' where date between :start and :end group by date ORDER BY date ASC', ['start' => $this->filter_date_helper->date_start->format('Y-m-d'), ':end' => $this->filter_date_helper->date_end->format('Y-m-d')]);

        $dates_array = [];
        foreach ($period as $value) {
            $dates_array[$value->format("d.m.Y")] = "0";
        }

        $complete_dates_counts = [];
        $date_counts = [];

        if ($sql_data->getRows() != 0) {
            foreach ($sql_data as $row) {
                $date = DateTime::createFromFormat('Y-m-d', $row->getValue('date'))->format('d.m.Y');
                $date_counts[$date] = $row->getValue('count');
            }

            $complete_dates_counts = array_merge($dates_array, $date_counts);
        }

        $values = array_values($complete_dates_counts);

        $data_chart_visits[] = [
            'data' => $values,
            'name' => 'Aufrufe Gesamt',
            'type' => 'line',
        ];

        // include stats for each domain if "combine_all_domains" is disabled
        if ($this->addon->getConfig('statistics_combine_all_domains') == false) {
            foreach ($domains as $domain) {
                $sql_data = $sql->setQuery('SELECT date, ifnull(count,0) as "count" from ' . rex::getTable('pagestats_visits_per_day') . ' where date between :start and :end and domain = :domain ORDER BY date ASC', ['start' => $this->filter_date_helper->date_start->format('Y-m-d'), ':end' => $this->filter_date_helper->date_end->format('Y-m-d'), 'domain' => $domain['domain']]);

                $visits_per_day = [];
                foreach ($period as $value) {
                    $visits_per_day[$value->format("d.m.Y")] = "0";
                }

                $complete_dates_counts = [];
                $date_counts = [];

                if ($sql_data->getRows() != 0) {
                    foreach ($sql_data as $row) {
                        $date = DateTime::createFromFormat('Y-m-d', $row->getValue('date'))->format('d.m.Y');
                        $visits_per_day[$date] = $row->getValue('count');
                    }
                }

                $values = array_values($visits_per_day);

                $data_chart_visits[] = [
                    // 'x' => $labels,
                    'data' => $values,
                    'name' => 'Aufrufe ' . $domain['domain'],
                    'type' => 'line',
                ];
            }
        }

        return $data_chart_visits;
    }


    /**
     * 
     * 
     * @return array 
     * @throws InvalidArgumentException 
     * @throws rex_sql_exception 
     */
    private function get_visitors_per_day(): array
    {
        // DATA COLLECTION FOR MAIN CHART, "VISITORS PER DAY"

        // modify end date, because sql includes start and end, php ommits end
        $period = new DatePeriod(
            $this->filter_date_helper->date_start,
            new DateInterval('P1D'),
            $this->filter_date_helper->date_end->modify('+1 day')
        );


        $sql = rex_sql::factory();
        $domains = $sql->getArray('select distinct domain from ' . rex::getTable('pagestats_visitors_per_day'));

        $data_chart_visitors = [];


        // "total"
        $sql_data = $sql->setQuery('SELECT date, ifnull(sum(count),0) as "count" from ' . rex::getTable('pagestats_visitors_per_day') . ' where date between :start and :end group by date ORDER BY date ASC', ['start' => $this->filter_date_helper->date_start->format('Y-m-d'), ':end' => $this->filter_date_helper->date_end->format('Y-m-d')]);

        $dates_array = [];
        foreach ($period as $value) {
            $dates_array[$value->format("d.m.Y")] = "0";
        }

        $complete_dates_counts = [];
        $date_counts = [];

        if ($sql_data->getRows() != 0) {
            foreach ($sql_data as $row) {
                $date = DateTime::createFromFormat('Y-m-d', $row->getValue('date'))->format('d.m.Y');
                $date_counts[$date] = $row->getValue('count');
            }

            $complete_dates_counts = array_merge($dates_array, $date_counts);
        }

        $values = array_values($complete_dates_counts);

        $data_chart_visitors[] = [
            'data' => $values,
            'name' => 'Besucher Gesamt',
            'type' => 'line',
        ];

        // include stats for each domain if "combine_all_domains" is disabled
        if ($this->addon->getConfig('statistics_combine_all_domains') == false) {
            foreach ($domains as $domain) {
                $sql_data = $sql->setQuery('SELECT date, ifnull(count,0) as "count" from ' . rex::getTable('pagestats_visitors_per_day') . ' where date between :start and :end and domain = :domain ORDER BY date ASC', ['start' => $this->filter_date_helper->date_start->format('Y-m-d'), ':end' => $this->filter_date_helper->date_end->format('Y-m-d'), 'domain' => $domain['domain']]);

                $visitors_per_day = [];
                foreach ($period as $value) {
                    $visitors_per_day[$value->format("d.m.Y")] = "0";
                }

                if ($sql_data->getRows() != 0) {
                    foreach ($sql_data as $row) {
                        $date = DateTime::createFromFormat('Y-m-d', $row->getValue('date'))->format('d.m.Y');
                        $visitors_per_day[$date] = $row->getValue('count');
                    }
                }

                $values = array_values($visitors_per_day);

                $data_chart_visitors[] = [
                    'data' => $values,
                    'name' => 'Besucher ' . $domain['domain'],
                    'type' => 'line',
                ];
            }
        }

        return $data_chart_visitors;
    }


    /**
     * 
     * 
     * @return array 
     * @throws InvalidArgumentException 
     * @throws rex_sql_exception 
     */
    public function get_heatmap_visits(): array
    {
        // data for heatmap chart

        $sql = rex_sql::factory();

        $jan_first = new DateTimeImmutable('first day of january this year');
        $dec_last = new DateTimeImmutable('first day of january next year');
        $visits_per_day = $sql->getArray('SELECT date, ifnull(sum(count),0) as "count" from ' . rex::getTable('pagestats_visits_per_day') . ' where date between :start and :end group by date ORDER BY date ASC', ['start' => $jan_first->format('Y-m-d'), ':end' => $dec_last->format('Y-m-d')]);

        $heatmap_calendar = [];
        foreach ($visits_per_day as $row) {
            $heatmap_calendar[$row['date']] = $row['count'];
        }

        $period = new DatePeriod(
            $jan_first,
            new DateInterval('P1D'),
            $dec_last
        );

        $data_visits_heatmap_values = [];
        foreach ($period as $value) {
            if (in_array($value->format("Y-m-d"), array_keys($heatmap_calendar))) {
                $data_visits_heatmap_values[] = [$value->format("Y-m-d"), $heatmap_calendar[$value->format("Y-m-d")]];
            } else {
                $data_visits_heatmap_values[] = [$value->format("Y-m-d"), 0];
            }
        }

        if (count($heatmap_calendar) == 0) {
            $max_value = 0;
        } else {
            $max_value = max(array_values($heatmap_calendar));
        }

        return [
            'data' => $data_visits_heatmap_values,
            'max' => $max_value,
        ];
    }


    /**
     * 
     * 
     * @return array 
     * @throws InvalidArgumentException 
     * @throws rex_sql_exception 
     */
    public function get_chart_data_monthly(): array
    {
        $legend = [];
        $xaxis = [];
        $series = [];

        // VISITS

        $sql = rex_sql::factory();
        $domains = $sql->getArray('select distinct domain from ' . rex::getTable('pagestats_visitors_per_day'));

        $min_max_date = $sql->getArray('SELECT MIN(date) AS "min_date", MAX(date) AS "max_date" FROM ' . rex::getTable('pagestats_visits_per_day'));


        if ($min_max_date[0]['min_date'] == null) {
            $min_date = new DateTimeImmutable();
            $max_date = new DateTimeImmutable();
        } else {
            $min_date = DateTimeImmutable::createFromFormat('Y-m-d', $min_max_date[0]['min_date']);
            $max_date = DateTimeImmutable::createFromFormat('Y-m-d', $min_max_date[0]['max_date']);
        }


        $period = new DatePeriod(
            $min_date,
            new DateInterval('P1M'),
            $max_date->modify("+1 month")
        );

        $serie_data = [];
        foreach ($period as $value) {
            $xaxis[] = $value->format("M Y"); // generate xaxis values once
            $serie_data[$value->format("M Y")] = 0; // initialize each month with 0
        }

        // get total visits
        $result_total = $sql->getArray('SELECT DATE_FORMAT(date,"%b %Y") AS "month", IFNULL(SUM(count),0) AS "count" FROM ' . rex::getTable('pagestats_visits_per_day') . ' GROUP BY month ORDER BY date ASC');


        // set count to each month
        foreach ($result_total as $row) {
            $serie_data[$row['month']] = $row['count'];
        }

        // combine data to series array for chart
        $serie = [
            'data' => array_values($serie_data),
            'name' => 'Aufrufe Gesamt',
            'type' => 'line',
        ];

        // append to legend
        $legend[] = 'Aufrufe Gesamt';

        // add serie to series
        $series[] = $serie;

        // do this procedure for each domain
        if ($this->addon->getConfig('statistics_combine_all_domains') == false) {
            foreach ($domains as $domain) {
                $result_domain = $sql->getArray('SELECT DATE_FORMAT(date,"%b %Y") AS "month", IFNULL(SUM(count),0) AS "count" FROM ' . rex::getTable('pagestats_visits_per_day') . ' WHERE domain = :domain GROUP BY month ORDER BY date ASC', ['domain' => $domain['domain']]);

                $serie_data = [];
                foreach ($period as $value) {
                    $serie_data[$value->format("M Y")] = "0";
                }

                foreach ($result_domain as $row) {
                    $serie_data[$row['month']] = $row['count'];
                }

                $serie = [
                    'data' => array_values($serie_data),
                    'name' => 'Aufrufe ' . $domain['domain'],
                    'type' => 'line',
                ];

                $legend[] = 'Aufrufe ' . $domain['domain'];

                $series[] = $serie;
            }
        }



        // VISITORS

        // get total visits
        $result_total = $sql->getArray('SELECT DATE_FORMAT(date,"%b %Y") AS "month", IFNULL(SUM(count),0) AS "count" FROM ' . rex::getTable('pagestats_visitors_per_day') . ' GROUP BY month ORDER BY date ASC');

        $serie_data = [];
        foreach ($period as $value) {
            $serie_data[$value->format("M Y")] = 0; // initialize each month with 0
        }

        // set count to each month
        foreach ($result_total as $row) {
            $serie_data[$row['month']] = $row['count'];
        }

        // combine data to series array for chart
        $serie = [
            'data' => array_values($serie_data),
            'name' => 'Besucher Gesamt',
            'type' => 'line',
        ];

        // append to legend
        $legend[] = 'Besucher Gesamt';

        // add serie to series
        $series[] = $serie;

        // do this procedure for each domain
        if ($this->addon->getConfig('statistics_combine_all_domains') == false) {
            foreach ($domains as $domain) {
                $result_domain = $sql->getArray('SELECT DATE_FORMAT(date,"%b %Y") AS "month", IFNULL(SUM(count),0) AS "count" FROM ' . rex::getTable('pagestats_visitors_per_day') . ' WHERE domain = :domain GROUP BY month ORDER BY date ASC', ['domain' => $domain['domain']]);

                $serie_data = [];
                foreach ($period as $value) {
                    $serie_data[$value->format("M Y")] = "0";
                }

                foreach ($result_domain as $row) {
                    $serie_data[$row['month']] = $row['count'];
                }

                $serie = [
                    'data' => array_values($serie_data),
                    'name' => 'Besucher ' . $domain['domain'],
                    'type' => 'line',
                ];

                $legend[] = 'Besucher ' . $domain['domain'];

                $series[] = $serie;
            }
        }


        return [
            'series' => $series,
            'legend' => $legend,
            'xaxis' => $xaxis,
        ];
    }


    /**
     * 
     * 
     * @return array 
     * @throws InvalidArgumentException 
     * @throws rex_sql_exception 
     */
    public function get_chart_data_yearly(): array
    {

        $legend = [];
        $xaxis = [];
        $series = [];

        // VISITS

        $sql = rex_sql::factory();
        $domains = $sql->getArray('select distinct domain from ' . rex::getTable('pagestats_visitors_per_day'));

        $min_max_date = $sql->getArray('SELECT MIN(date) AS "min_date", MAX(date) AS "max_date" FROM ' . rex::getTable('pagestats_visits_per_day') . '');

        if ($min_max_date[0]['min_date'] == null) {
            $min_date = new DateTimeImmutable();
            $max_date = new DateTimeImmutable();
        } else {
            $min_date = DateTimeImmutable::createFromFormat('Y-m-d', $min_max_date[0]['min_date']);
            $max_date = DateTimeImmutable::createFromFormat('Y-m-d', $min_max_date[0]['max_date']);
        }

        $period = new DatePeriod(
            $min_date,
            new DateInterval('P1Y'),
            $max_date->modify('+1 year')
        );

        $serie_data = [];
        foreach ($period as $value) {
            $xaxis[] = $value->format("Y"); // generate xaxis values once
            $serie_data[$value->format("Y")] = 0; // initialize each year with 0
        }

        // get total visits
        $result_total = $sql->getArray('SELECT DATE_FORMAT(date,"%Y") AS "year", IFNULL(SUM(count),0) AS "count" FROM ' . rex::getTable('pagestats_visits_per_day') . ' GROUP BY year ORDER BY date ASC');

        // set count to each year
        foreach ($result_total as $row) {
            $serie_data[$row['year']] = $row['count'];
        }

        // combine data to series array for chart
        $serie = [
            'data' => array_values($serie_data),
            'name' => 'Aufrufe Gesamt',
            'type' => 'line',
        ];

        // append to legend
        $legend[] = 'Aufrufe Gesamt';

        // add serie to series
        $series[] = $serie;

        // do this procedure for each domain
        if ($this->addon->getConfig('statistics_combine_all_domains') == false) {
            foreach ($domains as $domain) {
                $result_domain = $sql->getArray('SELECT DATE_FORMAT(date,"%Y") AS "year", IFNULL(SUM(count),0) AS "count" FROM ' . rex::getTable('pagestats_visits_per_day') . ' WHERE domain = :domain GROUP BY year ORDER BY date ASC', ['domain' => $domain['domain']]);

                $serie_data = [];
                foreach ($period as $value) {
                    $serie_data[$value->format("Y")] = "0";
                }

                foreach ($result_domain as $row) {
                    $serie_data[$row['year']] = $row['count'];
                }

                $serie = [
                    'data' => array_values($serie_data),
                    'name' => 'Aufrufe ' . $domain['domain'],
                    'type' => 'line',
                ];

                $legend[] = 'Aufrufe ' . $domain['domain'];

                $series[] = $serie;
            }
        }



        // VISITORS

        // get total visits
        $result_total = $sql->getArray('SELECT DATE_FORMAT(date,"%Y") AS "year", IFNULL(SUM(count),0) AS "count" FROM ' . rex::getTable('pagestats_visitors_per_day') . ' GROUP BY year ORDER BY date ASC');

        $serie_data = [];
        foreach ($period as $value) {
            $serie_data[$value->format("Y")] = 0; // initialize each year with 0
        }

        // set count to each year
        foreach ($result_total as $row) {
            $serie_data[$row['year']] = $row['count'];
        }

        // combine data to series array for chart
        $serie = [
            'data' => array_values($serie_data),
            'name' => 'Besucher Gesamt',
            'type' => 'line',
        ];

        // append to legend
        $legend[] = 'Besucher Gesamt';

        // add serie to series
        $series[] = $serie;

        // do this procedure for each domain
        if ($this->addon->getConfig('statistics_combine_all_domains') == false) {
            foreach ($domains as $domain) {
                $result_domain = $sql->getArray('SELECT DATE_FORMAT(date,"%Y") AS "year", IFNULL(SUM(count),0) AS "count" FROM ' . rex::getTable('pagestats_visitors_per_day') . ' WHERE domain = :domain GROUP BY year ORDER BY date ASC', ['domain' => $domain['domain']]);

                $serie_data = [];
                foreach ($period as $value) {
                    $serie_data[$value->format("Y")] = "0";
                }

                foreach ($result_domain as $row) {
                    $serie_data[$row['year']] = $row['count'];
                }

                $serie = [
                    'data' => array_values($serie_data),
                    'name' => 'Besucher ' . $domain['domain'],
                    'type' => 'line',
                ];

                $legend[] = 'Besucher ' . $domain['domain'];

                $series[] = $serie;
            }
        }


        return [
            'series' => $series,
            'legend' => $legend,
            'xaxis' => $xaxis,
        ];
    }
}
