<?php

class ChartData
{
    private $filter_date_helper;
    private $addon;

    public function __construct($filter_date_helper)
    {
        $this->filter_date_helper = $filter_date_helper;
        $this->addon = rex_addon::get('statistics');
    }

    private function get_labels()
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

    public function get_main_chart_data()
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

    private function get_visits_per_day()
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

    private function get_visitors_per_day()
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

    public function get_heatmap_visits()
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
}
