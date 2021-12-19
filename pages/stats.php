<?php

$addon = rex_addon::get('statistics');

// BASIC INITIALISATION 

$current_backend_page = rex_get('page', 'string', '');
$request_date_start = htmlspecialchars_decode(rex_request('date_start', 'string', ''));
$request_date_end = htmlspecialchars_decode(rex_request('date_end', 'string', ''));

$sql = rex_sql::factory();
// $sql->setDebug(true);

$filter_date_helper = new filter_date_helper($request_date_start, $request_date_end, 'pagestats_visits_per_day');





// DATA COLLECTION FOR MAIN CHART, "VIEWS PER DAY"
$sql = rex_sql::factory();
$domains = $sql->getArray('select distinct domain from ' . rex::getTable('pagestats_visits_per_day'));
$data_all_domains_visits = [];

foreach ($domains as $domain) {
    $visits_per_day = $sql->setQuery('SELECT date, count from ' . rex::getTable('pagestats_visits_per_day') . ' where date between :start and :end and domain = :domain ORDER BY date ASC', ['start' => $filter_date_helper->date_start->format('Y-m-d'), ':end' => $filter_date_helper->date_end->format('Y-m-d'), 'domain' => $domain['domain']]);

    $period = new DatePeriod(
        $filter_date_helper->date_start,
        new DateInterval('P1D'),
        $filter_date_helper->date_end
    );

    $dates_array = [];
    foreach ($period as $value) {
        $dates_array[$value->format("d.m.Y")] = "0";
    }

    $complete_dates_counts = [];
    $date_counts = [];

    if ($visits_per_day->getRows() != 0) {
        foreach ($visits_per_day as $row) {
            $date = DateTime::createFromFormat('Y-m-d', $row->getValue('date'))->format('d.m.Y');
            $date_counts[$date] = $row->getValue('count');
        }

        $complete_dates_counts = array_merge($dates_array, $date_counts);
    }

    $labels = json_encode(array_keys($complete_dates_counts));
    $values = json_encode(array_values($complete_dates_counts));

    $data_all_domains_visits[$domain['domain']] = [
        'labels' => $labels,
        'values' => $values,
    ];
}

// One line for total visits
if (count($domains) > 1) {
    $visits_per_day = $sql->setQuery('SELECT date, sum(count) as "count" from ' . rex::getTable('pagestats_visits_per_day') . ' where date between :start and :end group by date ORDER BY date ASC', ['start' => $filter_date_helper->date_start->format('Y-m-d'), ':end' => $filter_date_helper->date_end->format('Y-m-d')]);

    $period = new DatePeriod(
        $filter_date_helper->date_start,
        new DateInterval('P1D'),
        $filter_date_helper->date_end
    );

    $dates_array = [];
    foreach ($period as $value) {
        $dates_array[$value->format("d.m.Y")] = "0";
    }

    $complete_dates_counts = [];
    $date_counts = [];

    if ($visits_per_day->getRows() != 0) {
        foreach ($visits_per_day as $row) {
            $date = DateTime::createFromFormat('Y-m-d', $row->getValue('date'))->format('d.m.Y');
            $date_counts[$date] = $row->getValue('count');
        }

        $complete_dates_counts = array_merge($dates_array, $date_counts);
    }

    $labels = json_encode(array_keys($complete_dates_counts));
    $values = json_encode(array_values($complete_dates_counts));

    $data_all_domains_visits['Gesamt'] = [
        'labels' => $labels,
        'values' => $values,
    ];
}






// DATA COLLECTION FOR MAIN CHART, "VISITORS PER DAY"
$sql = rex_sql::factory();
$domains = $sql->getArray('select distinct domain from ' . rex::getTable('pagestats_visitors_per_day'));

$data_all_domains_visitors = [];

foreach ($domains as $domain) {
    $visitors_per_day = $sql->setQuery('SELECT date, count from ' . rex::getTable('pagestats_visitors_per_day') . ' where date between :start and :end and domain = :domain ORDER BY date ASC', ['start' => $filter_date_helper->date_start->format('Y-m-d'), ':end' => $filter_date_helper->date_end->format('Y-m-d'), 'domain' => $domain['domain']]);

    $period = new DatePeriod(
        $filter_date_helper->date_start,
        new DateInterval('P1D'),
        $filter_date_helper->date_end
    );

    $dates_array = [];
    foreach ($period as $value) {
        $dates_array[$value->format("d.m.Y")] = "0";
    }

    $complete_dates_counts = [];
    $date_counts = [];

    if ($visitors_per_day->getRows() != 0) {
        foreach ($visitors_per_day as $row) {
            $date = DateTime::createFromFormat('Y-m-d', $row->getValue('date'))->format('d.m.Y');
            $date_counts[$date] = $row->getValue('count');
        }

        $complete_dates_counts = array_merge($dates_array, $date_counts);
    }

    $labels = json_encode(array_keys($complete_dates_counts));
    $values = json_encode(array_values($complete_dates_counts));

    $data_all_domains_visitors[$domain['domain']] = [
        'labels' => $labels,
        'values' => $values,
    ];
}


// One line for total visitors
if (count($domains) > 1) {
    $visitors_per_day = $sql->setQuery('SELECT date, sum(count) as "count" from ' . rex::getTable('pagestats_visitors_per_day') . ' where date between :start and :end group by date ORDER BY date ASC', ['start' => $filter_date_helper->date_start->format('Y-m-d'), ':end' => $filter_date_helper->date_end->format('Y-m-d')]);

    $period = new DatePeriod(
        $filter_date_helper->date_start,
        new DateInterval('P1D'),
        $filter_date_helper->date_end
    );

    $dates_array = [];
    foreach ($period as $value) {
        $dates_array[$value->format("d.m.Y")] = "0";
    }

    $complete_dates_counts = [];
    $date_counts = [];

    if ($visitors_per_day->getRows() != 0) {
        foreach ($visitors_per_day as $row) {
            $date = DateTime::createFromFormat('Y-m-d', $row->getValue('date'))->format('d.m.Y');
            $date_counts[$date] = $row->getValue('count');
        }

        $complete_dates_counts = array_merge($dates_array, $date_counts);
    }

    $labels = json_encode(array_keys($complete_dates_counts));
    $values = json_encode(array_values($complete_dates_counts));

    $data_all_domains_visitors['Gesamt'] = [
        'labels' => $labels,
        'values' => $values,
    ];
}




// FRAGMENT TO SHOW TODAYS AND TOTAL COUNT OF VIEWS

$visits_total = $sql->setQuery('SELECT sum(count) as "count" from ' . rex::getTable('pagestats_visits_per_day'));
$visits_total = $visits_total->getValue('count');

$visits_today = $sql->setQuery('SELECT count from ' . rex::getTable('pagestats_visits_per_day') . ' where date = :date', ['date' => date('Y-m-d')]);
if ($visits_today->getRows() != 0) {
    $visits_today = $visits_today->getValue('count');
} else {
    $visits_today = 0;
}

$visitors_total = $sql->setQuery('SELECT sum(count) as "count" from ' . rex::getTable('pagestats_visitors_per_day'));
$visitors_total = $visitors_total->getValue('count');

$visitors_today = $sql->setQuery('SELECT count from ' . rex::getTable('pagestats_visitors_per_day') . ' where date = :date', ['date' => date('Y-m-d')]);
if ($visitors_today->getRows() != 0) {
    $visitors_today = $visitors_today->getValue('count');
} else {
    $visitors_today = 0;
}


$visits_datefilter = $sql->setQuery('SELECT sum(count) as "count" from ' . rex::getTable('pagestats_visits_per_day') . ' where date between :start and :end', ['start' => $filter_date_helper->date_start->format('Y-m-d'), ':end' => $filter_date_helper->date_end->format('Y-m-d')]);
if ($visits_datefilter->getRows() != 0) {
    $visits_datefilter = $visits_datefilter->getValue('count');
} else {
    $visits_datefilter = 0;
}

$visitors_datefilter = $sql->setQuery('SELECT sum(count) as "count" from ' . rex::getTable('pagestats_visitors_per_day') . ' where date between :start and :end', ['start' => $filter_date_helper->date_start->format('Y-m-d'), ':end' => $filter_date_helper->date_end->format('Y-m-d')]);
if ($visitors_datefilter->getRows() != 0) {
    $visitors_datefilter = $visitors_datefilter->getValue('count');
} else {
    $visitors_datefilter = 0;
}

$fragment_overview = new rex_fragment();
$fragment_overview->setVar('date_start', $filter_date_helper->date_start);
$fragment_overview->setVar('date_end', $filter_date_helper->date_end);
$fragment_overview->setVar('filtered_visits', $visits_datefilter);
$fragment_overview->setVar('filtered_visitors', $visitors_datefilter);
$fragment_overview->setVar('today_visits', $visits_today);
$fragment_overview->setVar('today_visitors', $visitors_today);
$fragment_overview->setVar('total_visits', $visits_total);
$fragment_overview->setVar('total_visitors', $visitors_total);





// CLASSES FOR WIDGETS AND CHARTS

$browser = new stats_browser($filter_date_helper->date_start, $filter_date_helper->date_end);
$browser_data = $browser->get_data();

$browsertype = new stats_browsertype($filter_date_helper->date_start, $filter_date_helper->date_end);
$browsertype_data = $browsertype->get_data();

$os = new stats_os($filter_date_helper->date_start, $filter_date_helper->date_end);
$os_data = $os->get_data();

$brand = new stats_brand($filter_date_helper->date_start, $filter_date_helper->date_end);
$brand_data = $brand->get_data();

$model = new stats_model($filter_date_helper->date_start, $filter_date_helper->date_end);
$model_data = $model->get_data();

$weekday = new stats_weekday($filter_date_helper->date_start, $filter_date_helper->date_end);
$weekday_data = $weekday->get_data();

$hour = new stats_hour($filter_date_helper->date_start, $filter_date_helper->date_end);
$hour_data = $hour->get_data();




// FRAGMENT FOR DATE FILTER
$filter_fragment = new rex_fragment();
$filter_fragment->setVar('current_backend_page', $current_backend_page);
$filter_fragment->setVar('date_start', $filter_date_helper->date_start);
$filter_fragment->setVar('date_end', $filter_date_helper->date_end);
$filter_fragment->setVar('wts', $filter_date_helper->whole_time_start->format("Y-m-d"));



echo $filter_fragment->parse('filter.php');

echo $fragment_overview->parse('overview.php');




// FRAGMENTS FOR
// - PANEL WITH CHART "VIEWS TOTAL"
// - TABLE WITH DATA FOR "VIEWS TOTAL"

$list_dates = rex_list::factory('SELECT date, sum(count) as "count" FROM ' . rex::getTable('pagestats_visits_per_day') . ' where date between "' . $filter_date_helper->date_start->format('Y-m-d') . '" and "' . $filter_date_helper->date_end->format('Y-m-d') . '" group by date ORDER BY count DESC', 10000);
$list_dates->setColumnLabel('date', 'Datum');
$list_dates->setColumnLabel('count', 'Anzahl');
$list_dates->setColumnParams('url', ['url' => '###url###']);
$list_dates->addTableAttribute('class', 'table-bordered dt_order_first statistics_table');
$list_dates->setColumnLayout('date', ['<th>###VALUE###</th>', '<td data-sort="###date###">###VALUE###</td>']);
$list_dates->setColumnFormat('date', 'date', 'd.m.Y');

if ($list_dates->getRows() == 0) {
    $table = '<h3>Besuche:</h3>' . rex_view::info($this->i18n('statistics_no_data'));
} else {
    $table = '<h3>Besuche:</h3>' . $list_dates->get();
}

$table .= '<hr>';

$list_dates = rex_list::factory('SELECT date, sum(count) as "count" FROM ' . rex::getTable('pagestats_visitors_per_day') . ' where date between "' . $filter_date_helper->date_start->format('Y-m-d') . '" and "' . $filter_date_helper->date_end->format('Y-m-d') . '" group by date ORDER BY count DESC', 10000);
$list_dates->setColumnLabel('date', 'Datum');
$list_dates->setColumnLabel('count', 'Anzahl');
$list_dates->addTableAttribute('class', 'table-bordered dt_order_first statistics_table');
$list_dates->setColumnLayout('date', ['<th>###VALUE###</th>', '<td data-sort="###date###">###VALUE###</td>']);
$list_dates->setColumnFormat('date', 'date', 'd.m.Y');

if ($list_dates->getRows() == 0) {
    $table .= '<h3>Besucher:</h3>' . rex_view::info($this->i18n('statistics_no_data'));
} else {
    $table .= '<h3>Besucher:</h3>' . $list_dates->get();
}

$fragment_collapse = new rex_fragment();
$fragment_collapse->setVar('title', $this->i18n('statistics_views_per_day'));
$fragment_collapse->setVar('content', $table, false);

$fragment = new rex_fragment();
$fragment->setVar('title', '<b>' . $this->i18n('statistics_views_per_day') . '</b>', false);
$fragment->setVar('body', '<div id="chart_visits"></div>' . $fragment_collapse->parse('collapse.php'), false);
echo $fragment->parse('core/page/section.php');




$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('statistics_browser'));
$fragment->setVar('chart', '<div id="chart_browser"></div>', false);
$fragment->setVar('table', $browser->get_list(), false);
echo $fragment->parse('data_vertical.php');

$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('statistics_devicetype'));
$fragment->setVar('chart', '<div id="chart_browsertype"></div>', false);
$fragment->setVar('table', $browsertype->get_list(), false);
echo $fragment->parse('data_vertical.php');

$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('statistics_os'));
$fragment->setVar('chart', '<div id="chart_os"></div>', false);
$fragment->setVar('table', $os->get_list(), false);
echo $fragment->parse('data_vertical.php');

$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('statistics_brand'));
$fragment->setVar('chart', '<div id="chart_brand"></div>', false);
$fragment->setVar('table', $brand->get_list(), false);
echo $fragment->parse('data_vertical.php');

$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('statistics_model'));
$fragment->setVar('chart', '<div id="chart_model"></div>', false);
$fragment->setVar('table', $model->get_list(), false);
echo $fragment->parse('data_vertical.php');

$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('statistics_days'));
$fragment->setVar('chart', '<div id="chart_weekday"></div>', false);
$fragment->setVar('table', $weekday->get_list(), false);
echo $fragment->parse('data_vertical.php');

$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('statistics_hours'));
$fragment->setVar('chart', '<div id="chart_hour"></div>', false);
$fragment->setVar('table', $hour->get_list(), false);
echo $fragment->parse('data_vertical.php');




$list = rex_list::factory('SELECT * FROM ' . rex::getTable('pagestats_bot') . ' ORDER BY count DESC');
$list->setColumnLabel('name', $this->i18n('statistics_name'));
$list->setColumnLabel('count', $this->i18n('statistics_count'));
$list->setColumnLabel('category', $this->i18n('statistics_category'));
$list->setColumnLabel('producer', $this->i18n('statistics_producer'));
$list->addTableAttribute('class', 'dt_order_default statistics_table');

if ($list->getRows() == 0) {
    $table = rex_view::info($addon->i18n('statistics_no_data'));
} else {
    $table = $list->get();
}

$fragment = new rex_fragment();
$fragment->setVar('title', 'Bots:');
$fragment->setVar('body', $table, false);
echo $fragment->parse('core/page/section.php');

?>


<script>
    var config = {
        responsive: true,
        toImageButtonOptions: {
            format: 'jpeg',
            filename: 'plot',
            height: 750,
            width: 1500,
            scale: 1,
        },
        displaylogo: false,
        displayModeBar: true,
    }
    var layout = {
        // autosize: true,
        // modebar: {
        //     orientation: 'h',
        // },
        margin: {
            r: 40, // r: 5,
            l: 40, // l: 35,
            t: 40,
            b: 90,
        },
        // legend: {
        //     bgcolor: '#f0f0f0',
        //     valign: 'bottom',
        //     xanchor: "left",
        //     yanchor: "bottom",
        //     y: 1.1,
        //     x: 0,
        //     orientation: "h"
        // },
    }


    <?php
    // VISITS
    foreach ($data_all_domains_visits as $name => $domain) {
        echo 'var stats_chart_visits_' . str_replace(['.', '-', 'ä', 'ö', 'ü', 'ß'], '_', $name) . ' = {
            x: ' . $domain['labels'] . ',
            y: ' . $domain['values'] . ',
            type: \'line\',
            name: \'Aufrufe ' . $name . '\'
        };' . PHP_EOL;
    }

    // VISITORS
    foreach ($data_all_domains_visitors as $name => $domain) {
        echo 'var stats_chart_visitors_' . str_replace(['.', '-', 'ä', 'ö', 'ü', 'ß'], '_', $name) . ' = {
            x: ' . $domain['labels'] . ',
            y: ' . $domain['values'] . ',
            type: \'line\',
            name: \'Besucher ' . $name . '\'
        };' . PHP_EOL;
    }


    // js array
    $js_vars = 'var data = [';
    foreach (array_keys($data_all_domains_visits) as $domain) {
        $js_vars .= 'stats_chart_visits_' . str_replace(['.', '-', 'ä', 'ö', 'ü', 'ß'], '_', $domain) . ', ';
    }
    foreach (array_keys($data_all_domains_visitors) as $domain) {
        $js_vars .= 'stats_chart_visitors_' . str_replace(['.', '-', 'ä', 'ö', 'ü', 'ß'], '_', $domain) . ', ';
    }
    $js_vars .= ']';
    echo $js_vars . PHP_EOL;
    ?>


    chart_visits = Plotly.newPlot('chart_visits', data, layout, config);


    chart_browser = Plotly.newPlot('chart_browser', [{
        type: 'pie',
        labels: <?php echo $browser_data['labels'] ?>,
        values: <?php echo $browser_data['values'] ?>,
        textposition: "inside",
    }], layout, config);

    chart_browsertype = Plotly.newPlot('chart_browsertype', [{
        type: 'pie',
        labels: <?php echo $browsertype_data['labels'] ?>,
        values: <?php echo $browsertype_data['values'] ?>,
        textposition: "inside",
    }], layout, config);

    chart_os = Plotly.newPlot('chart_os', [{
        type: 'pie',
        labels: <?php echo $os_data['labels'] ?>,
        values: <?php echo $os_data['values'] ?>,
        textposition: "inside",
    }], layout, config);

    chart_brand = Plotly.newPlot('chart_brand', [{
        type: 'pie',
        labels: <?php echo $brand_data['labels'] ?>,
        values: <?php echo $brand_data['values'] ?>,
        textposition: "inside",
    }], layout, config);

    chart_model = Plotly.newPlot('chart_model', [{
        type: 'pie',
        labels: <?php echo $model_data['labels'] ?>,
        values: <?php echo $model_data['values'] ?>,
        textposition: "inside",
    }], layout, config);

    chart_weekday = Plotly.newPlot('chart_weekday', [{
        type: 'bar',
        x: <?php echo $weekday_data['labels'] ?>,
        y: <?php echo $weekday_data['values'] ?>,
    }], layout, config);

    chart_hour = Plotly.newPlot('chart_hour', [{
        type: 'bar',
        x: <?php echo $hour_data['labels'] ?>,
        y: <?php echo $hour_data['values'] ?>,
    }], layout, config);


    $(document).on('rex:ready', function() {
        $('.dt_order_second').DataTable({
            "paging": true,
            "pageLength": 8,
            "lengthChange": true,
            "lengthMenu": [5, 10, 50, 100],
            "order": [
                [1, "desc"]
            ],
            "search": {
                "caseInsensitive": true
            },

            <?php

            if (trim(rex::getUser()->getLanguage()) == '' || trim(rex::getUser()->getLanguage()) == 'de_de') {
                if (rex::getProperty('lang') == 'de_de') {
                    echo '
                    language: {
                        "search": "_INPUT_",
                        "searchPlaceholder": "Suchen",
                        "decimal": ",",
                        "info": "Einträge _START_-_END_ von _TOTAL_",
                        "emptyTable": "Keine Daten",
                        "infoEmpty": "0 von 0 Einträgen",
                        "infoFiltered": "(von _MAX_ insgesamt)",
                        "lengthMenu": "_MENU_ anzeigen",
                        "loadingRecords": "Lade...",
                        "zeroRecords": "Keine passenden Datensätze gefunden",
                        "thousands": ".",
                        "paginate": {
                            "first": "<<",
                            "last": ">>",
                            "next": ">",
                            "previous": "<"
                        },
                    },
                    ';
                }
            }

            ?>
        });

        $('.dt_order_first').DataTable({
            "paging": true,
            "pageLength": 8,
            "lengthChange": true,
            "lengthMenu": [5, 10, 50, 100],
            "order": [
                [0, "desc"]
            ],
            "search": {
                "caseInsensitive": true
            },

            <?php

            if (trim(rex::getUser()->getLanguage()) == '' || trim(rex::getUser()->getLanguage()) == 'de_de') {
                if (rex::getProperty('lang') == 'de_de') {
                    echo '
                    language: {
                        "search": "_INPUT_",
                        "searchPlaceholder": "Suchen",
                        "decimal": ",",
                        "info": "Einträge _START_-_END_ von _TOTAL_",
                        "emptyTable": "Keine Daten",
                        "infoEmpty": "0 von 0 Einträgen",
                        "infoFiltered": "(von _MAX_ insgesamt)",
                        "lengthMenu": "_MENU_ anzeigen",
                        "loadingRecords": "Lade...",
                        "zeroRecords": "Keine passenden Datensätze gefunden",
                        "thousands": ".",
                        "paginate": {
                            "first": "<<",
                            "last": ">>",
                            "next": ">",
                            "previous": "<"
                        },
                    },
                    ';
                }
            }

            ?>
        });

        $('.dt_order_default').DataTable({
            "paging": true,
            "pageLength": 8,
            "lengthChange": true,
            "lengthMenu": [5, 10, 50, 100],
            "search": {
                "caseInsensitive": true
            },

            <?php

            if (trim(rex::getUser()->getLanguage()) == '' || trim(rex::getUser()->getLanguage()) == 'de_de') {
                if (rex::getProperty('lang') == 'de_de') {
                    echo '
                    language: {
                        "search": "_INPUT_",
                        "searchPlaceholder": "Suchen",
                        "decimal": ",",
                        "info": "Einträge _START_-_END_ von _TOTAL_",
                        "emptyTable": "Keine Daten",
                        "infoEmpty": "0 von 0 Einträgen",
                        "infoFiltered": "(von _MAX_ insgesamt)",
                        "lengthMenu": "_MENU_ anzeigen",
                        "loadingRecords": "Lade...",
                        "zeroRecords": "Keine passenden Datensätze gefunden",
                        "thousands": ".",
                        "paginate": {
                            "first": "<<",
                            "last": ">>",
                            "next": ">",
                            "previous": "<"
                        },
                    },
                    ';
                }
            }

            ?>
        });
    });
</script>