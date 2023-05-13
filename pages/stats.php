<?php

$addon = rex_addon::get('statistics');

// BASIC INITIALISATION 

$current_backend_page = rex_get('page', 'string', '');
$request_date_start = htmlspecialchars_decode(rex_request('date_start', 'string', ''));
$request_date_end = htmlspecialchars_decode(rex_request('date_end', 'string', ''));

$sql = rex_sql::factory();
// $sql->setDebug(true);

$filter_date_helper = new filterDateHelper($request_date_start, $request_date_end, 'pagestats_visits_per_day');



// data for charts
$chart_data = new chartData($filter_date_helper);


// main chart data for visits and visitors
$main_chart_data = $chart_data->get_main_chart_data();

// heatmap data for visits per day in this year
$data_heatmap = $chart_data->get_heatmap_visits();

// chart data monthly
$chart_data_monthly = $chart_data->get_chart_data_monthly();

// chart data yearly
$chart_data_yearly = $chart_data->get_chart_data_yearly();



// device specific data
$browser = new stats_browser();
$browser_data = $browser->get_data();

$browsertype = new stats_browsertype();
$browsertype_data = $browsertype->get_data();

$os = new stats_os();
$os_data = $os->get_data();

$brand = new stats_brand();
$brand_data = $brand->get_data();

$model = new stats_model();
$model_data = $model->get_data();

$weekday = new stats_weekday();
$weekday_data = $weekday->get_data();

$hour = new stats_hour();
$hour_data = $hour->get_data();



// overview of visits and visitors of today, total and filered by date
$overview = new StatsOverview($filter_date_helper);
$overview_data = $overview->get_overview_data();

$fragment_overview = new rex_fragment();
$fragment_overview->setVar('date_start', $filter_date_helper->date_start);
$fragment_overview->setVar('date_end', $filter_date_helper->date_end);
$fragment_overview->setVar('filtered_visits', $overview_data['visits_datefilter']);
$fragment_overview->setVar('filtered_visitors', $overview_data['visitors_datefilter']);
$fragment_overview->setVar('today_visits', $overview_data['visits_today']);
$fragment_overview->setVar('today_visitors', $overview_data['visitors_today']);
$fragment_overview->setVar('total_visits', $overview_data['visits_total']);
$fragment_overview->setVar('total_visitors', $overview_data['visitors_total']);



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

$lists_data = new ListData($filter_date_helper);

$lists_daily = $lists_data->get_lists_daily();
$lists_monthly = $lists_data->get_lists_monthly();
$lists_yearly = $lists_data->get_lists_yearly();




$fragment_main_chart = new rex_fragment();
$fragment_main_chart->setVar('daily', '<div id="chart_visits_daily" style="width: 100%;height:500px;"></div><hr><div id="chart_visits_heatmap" style="width: 100%;height:200px;"></div>' . $lists_daily->parse('collapse.php'), false);
$fragment_main_chart->setVar('monthly', '<div id="chart_visits_monthly" style="width: 100%;height:500px;"></div>' . $lists_monthly->parse('collapse.php'), false);
$fragment_main_chart->setVar('yearly', '<div id="chart_visits_yearly" style="width: 100%;height:500px;"></div>' . $lists_yearly->parse('collapse.php'), false);
echo $fragment_main_chart->parse('main_chart.php');




$fragment = new rex_fragment();
$fragment->setVar('title', $addon->i18n('statistics_browser'));
$fragment->setVar('chart', '<div id="chart_browser" style="width: 100%;height:500px"></div>', false);
$fragment->setVar('table', $browser->get_list(), false);
echo $fragment->parse('data_vertical.php');

$fragment = new rex_fragment();
$fragment->setVar('title', $addon->i18n('statistics_devicetype'));
$fragment->setVar('chart', '<div id="chart_browsertype" style="width: 100%;height:500px"></div>', false);
$fragment->setVar('table', $browsertype->get_list(), false);
echo $fragment->parse('data_vertical.php');

$fragment = new rex_fragment();
$fragment->setVar('title', $addon->i18n('statistics_os'));
$fragment->setVar('chart', '<div id="chart_os" style="width: 100%;height:500px"></div>', false);
$fragment->setVar('table', $os->get_list(), false);
echo $fragment->parse('data_vertical.php');

$fragment = new rex_fragment();
$fragment->setVar('title', $addon->i18n('statistics_brand'));
$fragment->setVar('chart', '<div id="chart_brand" style="width: 100%;height:500px"></div>', false);
$fragment->setVar('table', $brand->get_list(), false);
echo $fragment->parse('data_vertical.php');

$fragment = new rex_fragment();
$fragment->setVar('title', $addon->i18n('statistics_model'));
$fragment->setVar('chart', '<div id="chart_model" style="width: 100%;height:500px"></div>', false);
$fragment->setVar('table', $model->get_list(), false);
echo $fragment->parse('data_vertical.php');

$fragment = new rex_fragment();
$fragment->setVar('title', $addon->i18n('statistics_days'));
$fragment->setVar('chart', '<div id="chart_weekday" style="width: 100%;height:500px"></div>', false);
$fragment->setVar('table', $weekday->get_list(), false);
echo $fragment->parse('data_vertical.php');

$fragment = new rex_fragment();
$fragment->setVar('title', $addon->i18n('statistics_hours'));
$fragment->setVar('chart', '<div id="chart_hour" style="width: 100%;height:500px"></div>', false);
$fragment->setVar('table', $hour->get_list(), false);
echo $fragment->parse('data_vertical.php');




$list = rex_list::factory('SELECT * FROM ' . rex::getTable('pagestats_bot') . ' ORDER BY count DESC', 1000);
$list->setColumnLabel('name', $addon->i18n('statistics_name'));
$list->setColumnLabel('count', $addon->i18n('statistics_count'));
$list->setColumnLabel('category', $addon->i18n('statistics_category'));
$list->setColumnLabel('producer', $addon->i18n('statistics_producer'));
$list->addTableAttribute('class', 'dt_bots statistics_table');

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
    var chart_visits_daily = echarts.init(document.getElementById('chart_visits_daily'));
    var chart_visits_daily_option = {
        title: {},
        tooltip: {
            trigger: 'axis',
        },
        dataZoom: [{
            id: 'dataZoomX',
            type: 'slider',
            xAxisIndex: [0],
            filterMode: 'filter'
        }],
        grid: {
            left: '5%',
            right: '5%',
            // bottom: '10%',
            // top: '12%',
        },
        toolbox: {
            show: <?= rex_config::get('statistics', 'statistics_show_chart_toolbox') ? 'true' : 'false' ?>,
            orient: 'vertical',
            top: '10%',
            feature: {
                dataZoom: {
                    yAxisIndex: "none"
                },
                dataView: {
                    readOnly: false
                },
                magicType: {
                    type: ["line", "bar", 'stack']
                },
                restore: {},
                saveAsImage: {}
            }
        },
        legend: {
            data: <?php echo json_encode($main_chart_data['legend']) ?>,
            // orient: 'vertical',
            type: 'scroll',
            // top: 20,
            // bottom: 20,
            right: '5%',
            align: 'left',
        },
        xAxis: {
            data: <?php echo json_encode($main_chart_data['xaxis']) ?>,
            type: 'category',
        },
        yAxis: {},
        series: <?php echo json_encode($main_chart_data['series']) ?>
    };

    // Display the chart using the configuration items and data just specified.
    chart_visits_daily.setOption(chart_visits_daily_option);



    var chart_visits_monthly = echarts.init(document.getElementById('chart_visits_monthly'));
    var chart_visits_monthly_option = {
        title: {},
        tooltip: {
            trigger: 'axis',
        },
        dataZoom: [{
            id: 'dataZoomX',
            type: 'slider',
            xAxisIndex: [0],
            filterMode: 'filter'
        }],
        grid: {
            left: '5%',
            right: '5%',
        },
        toolbox: {
            show: <?= rex_config::get('statistics', 'statistics_show_chart_toolbox') ? 'true' : 'false' ?>,
            orient: 'vertical',
            top: '10%',
            feature: {
                dataZoom: {
                    yAxisIndex: "none"
                },
                dataView: {
                    readOnly: false
                },
                magicType: {
                    type: ["line", "bar", 'stack']
                },
                restore: {},
                saveAsImage: {}
            }
        },
        legend: {
            data: <?php echo json_encode($chart_data_monthly['legend']) ?>,
            right: '5%',
            type: 'scroll',
        },
        xAxis: {
            data: <?php echo json_encode($chart_data_monthly['xaxis']) ?>,
            type: 'category',
        },
        yAxis: {},
        series: <?php echo json_encode($chart_data_monthly['series']) ?>
    };
    chart_visits_monthly.setOption(chart_visits_monthly_option);



    var chart_visits_yearly = echarts.init(document.getElementById('chart_visits_yearly'));
    var chart_visits_yearly_option = {
        title: {},
        tooltip: {
            trigger: 'axis',
        },
        dataZoom: [{
            id: 'dataZoomX',
            type: 'slider',
            xAxisIndex: [0],
            filterMode: 'filter'
        }],
        grid: {
            left: '5%',
            right: '5%',
        },
        toolbox: {
            show: <?= rex_config::get('statistics', 'statistics_show_chart_toolbox') ? 'true' : 'false' ?>,
            orient: 'vertical',
            top: '10%',
            feature: {
                dataZoom: {
                    yAxisIndex: "none"
                },
                dataView: {
                    readOnly: false
                },
                magicType: {
                    type: ["line", "bar", 'stack']
                },
                restore: {},
                saveAsImage: {}
            }
        },
        legend: {
            data: <?php echo json_encode($chart_data_yearly['legend']) ?>,
            right: '5%',
            type: 'scroll',
        },
        xAxis: {
            data: <?php echo json_encode($chart_data_yearly['xaxis']) ?>,
            type: 'category',
        },
        yAxis: {},
        series: <?php echo json_encode($chart_data_yearly['series']) ?>
    };
    chart_visits_yearly.setOption(chart_visits_yearly_option);



    var visits_heatmap = echarts.init(document.getElementById('chart_visits_heatmap'));
    var option_heatmap = {
        title: {},
        tooltip: {
            show: true,
            formatter: function(p) {
                var format = echarts.format.formatTime('dd.MM.yyyy', p.data[0]);
                return format + '<br><b>' + p.data[1] + ' Aufrufe</b>';
            }
        },
        toolbox: {
            show: <?= rex_config::get('statistics', 'statistics_show_chart_toolbox') ? 'true' : 'false' ?>,
            orient: 'vertical',
            top: '10%',
            feature: {
                dataView: {
                    readOnly: false
                },
                restore: {},
                saveAsImage: {}
            }
        },
        calendar: {
            // top: 120,
            top: '90',
            left: '5%',
            right: '5%',
            cellSize: ['auto', 15],
            range: <?php echo date('Y') ?>,
            itemStyle: {
                borderWidth: 0.5
            },
            yearLabel: {
                show: false
            },
            monthLabel: {
                nameMap: [
                    'Jan', 'Feb', 'Mar', 'Apr', 'Mai', 'Jun',
                    'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'
                ],
            },
            dayLabel: {
                nameMap: [
                    'So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'
                ]
            }
        },
        series: {
            data: <?php echo json_encode($data_heatmap['data']) ?>,
            type: 'heatmap',
            coordinateSystem: 'calendar',
        },
        visualMap: {
            type: 'continuous',
            itemWidth: 20,
            itemHeight: 250,
            min: 0,
            max: <?php echo $data_heatmap['max'] ?>,
            calculable: true,
            orient: 'horizontal',
            left: 'center',
            top: 'top'
        },
    };
    visits_heatmap.setOption(option_heatmap);



    var chart_browser = echarts.init(document.getElementById('chart_browser'));
    var chart_browser_option = {
        title: {},
        tooltip: {
            trigger: 'item',
            formatter: "{b}: <b>{c}</b> ({d}%)"
        },
        legend: {
            show: false,
            orient: 'vertical',
            left: 'left',
        },
        toolbox: {
            show: <?= rex_config::get('statistics', 'statistics_show_chart_toolbox') ? 'true' : 'false' ?>,
            orient: 'vertical',
            top: '10%',
            feature: {
                dataView: {
                    readOnly: false
                },
                saveAsImage: {}
            }
        },
        series: [{
            type: 'pie',
            radius: '85%',
            data: <?php echo json_encode($browser_data) ?>,
            labelLine: {
                show: false
            },
            label: {
                show: true,
                position: 'inside',
                formatter: '{b}: {c} \n ({d}%)',
            },
            emphasis: {
                itemStyle: {
                    shadowBlur: 10,
                    shadowOffsetX: 0,
                    shadowColor: 'rgba(0, 0, 0, 0.5)'
                }
            }
        }]
    };
    chart_browser.setOption(chart_browser_option);



    var chart_browsertype = echarts.init(document.getElementById('chart_browsertype'));
    var chart_browsertype_option = {
        title: {},
        tooltip: {
            trigger: 'item',
            formatter: "{b}: <b>{c}</b> ({d}%)"
        },
        legend: {
            show: false,
            orient: 'vertical',
            left: 'left',
        },
        toolbox: {
            show: <?= rex_config::get('statistics', 'statistics_show_chart_toolbox') ? 'true' : 'false' ?>,
            orient: 'vertical',
            top: '10%',
            feature: {
                dataView: {
                    readOnly: false
                },
                saveAsImage: {}
            }
        },
        series: [{
            type: 'pie',
            radius: '85%',
            data: <?php echo json_encode($browsertype_data) ?>,
            labelLine: {
                show: false
            },
            label: {
                show: true,
                position: 'inside',
                formatter: '{b}: {c} \n ({d}%)',
            },
            emphasis: {
                itemStyle: {
                    shadowBlur: 10,
                    shadowOffsetX: 0,
                    shadowColor: 'rgba(0, 0, 0, 0.5)'
                }
            }
        }]
    };
    chart_browsertype.setOption(chart_browsertype_option);



    var chart_os = echarts.init(document.getElementById('chart_os'));
    var chart_os_option = {
        title: {},
        tooltip: {
            trigger: 'item',
            formatter: "{b}: <b>{c}</b> ({d}%)"
        },
        legend: {
            show: false,
            orient: 'vertical',
            left: 'left',
        },
        toolbox: {
            show: <?= rex_config::get('statistics', 'statistics_show_chart_toolbox') ? 'true' : 'false' ?>,
            orient: 'vertical',
            top: '10%',
            feature: {
                dataView: {
                    readOnly: false
                },
                saveAsImage: {}
            }
        },
        series: [{
            type: 'pie',
            radius: '85%',
            data: <?php echo json_encode($os_data) ?>,
            labelLine: {
                show: false
            },
            label: {
                show: true,
                position: 'inside',
                formatter: '{b}: {c} \n ({d}%)',
            },
            emphasis: {
                itemStyle: {
                    shadowBlur: 10,
                    shadowOffsetX: 0,
                    shadowColor: 'rgba(0, 0, 0, 0.5)'
                }
            }
        }]
    };
    chart_os.setOption(chart_os_option);



    var chart_brand = echarts.init(document.getElementById('chart_brand'));
    var chart_brand_option = {
        title: {},
        tooltip: {
            trigger: 'item',
            formatter: "{b}: <b>{c}</b> ({d}%)"
        },
        legend: {
            show: false,
            orient: 'vertical',
            left: 'left',
        },
        toolbox: {
            show: <?= rex_config::get('statistics', 'statistics_show_chart_toolbox') ? 'true' : 'false' ?>,
            orient: 'vertical',
            top: '10%',
            feature: {
                dataView: {
                    readOnly: false
                },
                saveAsImage: {}
            }
        },
        series: [{
            type: 'pie',
            radius: '85%',
            data: <?php echo json_encode($brand_data) ?>,
            labelLine: {
                show: false
            },
            label: {
                show: true,
                position: 'inside',
                formatter: '{b}: {c} \n ({d}%)',
            },
            emphasis: {
                itemStyle: {
                    shadowBlur: 10,
                    shadowOffsetX: 0,
                    shadowColor: 'rgba(0, 0, 0, 0.5)'
                }
            }
        }]
    };
    chart_brand.setOption(chart_brand_option);



    var chart_model = echarts.init(document.getElementById('chart_model'));
    var chart_model_option = {
        title: {},
        tooltip: {
            trigger: 'item',
            formatter: "{b}: <b>{c}</b> ({d}%)"
        },
        legend: {
            show: false,
            orient: 'vertical',
            left: 'left',
        },
        toolbox: {
            show: <?= rex_config::get('statistics', 'statistics_show_chart_toolbox') ? 'true' : 'false' ?>,
            orient: 'vertical',
            top: '10%',
            feature: {
                dataView: {
                    readOnly: false
                },
                saveAsImage: {}
            }
        },
        series: [{
            type: 'pie',
            radius: '85%',
            data: <?php echo json_encode($model_data) ?>,
            labelLine: {
                show: false
            },
            label: {
                show: true,
                position: 'inside',
                formatter: '{b}: {c} \n ({d}%)',
            },
            emphasis: {
                itemStyle: {
                    shadowBlur: 10,
                    shadowOffsetX: 0,
                    shadowColor: 'rgba(0, 0, 0, 0.5)'
                }
            }
        }]
    };
    chart_model.setOption(chart_model_option);



    var chart_weekday = echarts.init(document.getElementById('chart_weekday'));
    var chart_weekday_option = {
        title: {},
        tooltip: {
            trigger: 'axis',
            formatter: "{b}: <b>{c}</b>",
            axisPointer: {
                type: 'shadow'
            }
        },
        grid: {
            containLabel: true,
            left: '3%',
            right: '3%',
            bottom: '3%',
        },
        xAxis: [{
            type: 'category',
            data: ['Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So'],
            axisTick: {
                alignWithLabel: true
            }
        }],
        yAxis: [{
            type: 'value'
        }],
        toolbox: {
            show: <?= rex_config::get('statistics', 'statistics_show_chart_toolbox') ? 'true' : 'false' ?>,
            orient: 'vertical',
            top: '10%',
            feature: {
                dataZoom: {
                    yAxisIndex: "none"
                },
                dataView: {
                    readOnly: false
                },
                magicType: {
                    type: ["line", "bar"]
                },
                restore: {},
                saveAsImage: {}
            }
        },
        series: [{
            type: 'bar',
            data: <?php echo json_encode($weekday_data) ?>,
            label: {
                show: false,
            },
            emphasis: {
                itemStyle: {
                    shadowBlur: 10,
                    shadowOffsetX: 0,
                    shadowColor: 'rgba(0, 0, 0, 0.5)'
                }
            }
        }]
    };
    chart_weekday.setOption(chart_weekday_option);



    var chart_hour = echarts.init(document.getElementById('chart_hour'));
    var chart_hour_option = {
        title: {},
        tooltip: {
            trigger: 'axis',
            formatter: "{b} Uhr: <b>{c}</b>",
            axisPointer: {
                type: 'shadow'
            }
        },
        grid: {
            containLabel: true,
            left: '3%',
            right: '3%',
            bottom: '3%',
        },
        xAxis: [{
            type: 'category',
            data: ['00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23'],
            axisTick: {
                alignWithLabel: true
            }
        }],
        yAxis: [{
            type: 'value'
        }],
        toolbox: {
            show: <?= rex_config::get('statistics', 'statistics_show_chart_toolbox') ? 'true' : 'false' ?>,
            orient: 'vertical',
            top: '10%',
            feature: {
                dataZoom: {
                    yAxisIndex: "none"
                },
                dataView: {
                    readOnly: false
                },
                magicType: {
                    type: ["line", "bar"]
                },
                restore: {},
                saveAsImage: {}
            }
        },
        series: [{
            type: 'bar',
            data: <?php echo json_encode($hour_data) ?>,
            label: {
                show: false,
            },
            emphasis: {
                itemStyle: {
                    shadowBlur: 10,
                    shadowOffsetX: 0,
                    shadowColor: 'rgba(0, 0, 0, 0.5)'
                }
            }
        }]
    };
    chart_hour.setOption(chart_hour_option);


    // resize visits chart when tabs change
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
        chart_visits_daily.resize();
        chart_visits_monthly.resize();
        chart_visits_yearly.resize();
    })



    $(document).on('rex:ready', function() {
        $('.dt_order_second').DataTable({
            "paging": true,
            "pageLength": 10,
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
            "pageLength": 10,
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
            "pageLength": 10,
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

        $('.dt_bots').DataTable({
            "paging": true,
            "pageLength": 10,
            "lengthChange": true,
            "lengthMenu": [5, 10, 50, 100],
            "search": {
                "caseInsensitive": true
            },
            "order": [
                [3, "desc"]
            ],

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