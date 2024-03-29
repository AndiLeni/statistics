<?php

use AndiLeni\Statistics\DateFilter;
use AndiLeni\Statistics\Pages;
use AndiLeni\Statistics\PageDetails;

$addon = rex_addon::get('statistics');

$current_backend_page = rex_get('page', 'string', '');
$request_url = rex_request('url', 'string', '');
$request_url = htmlspecialchars_decode($request_url);
$ignore_page = rex_request('ignore_page', 'boolean', false);
$search_string = htmlspecialchars_decode(rex_request('search_string', 'string', ''));
$request_date_start = htmlspecialchars_decode(rex_request('date_start', 'string', ''));
$request_date_end = htmlspecialchars_decode(rex_request('date_end', 'string', ''));
$httpstatus = rex_request('httpstatus', 'string', 'any');


$filter_date_helper = new DateFilter($request_date_start, $request_date_end, 'pagestats_visits_per_url');
$pages_helper = new Pages($filter_date_helper);




// FRAGMENT FOR DATE FILTER
$filter_fragment = new rex_fragment();
$filter_fragment->setVar('current_backend_page', $current_backend_page);
$filter_fragment->setVar('date_start', $filter_date_helper->date_start);
$filter_fragment->setVar('date_end', $filter_date_helper->date_end);
$filter_fragment->setVar('wts', $filter_date_helper->whole_time_start->format("Y-m-d"));


?>

<div class="row">
    <div class="col-sm-12">
        <?php echo $filter_fragment->parse('filter.php'); ?>
    </div>
</div>

<?php

// sum per page, bar chart
$sum_per_page = $pages_helper->sumPerPage($httpstatus);


// check if request is for ignoring a url
// if yes, add url to addon settings and delete all database entries of this url 
if ($request_url != '' && $ignore_page === true) {
    $rows = $pages_helper->ignorePage($request_url);
    echo rex_view::success('Es wurden ' . $rows . ' Einträge gelöscht. Die Url <code>' . $request_url . '</code> wird zukünftig ignoriert.');
}


// details for one url requested
if ($request_url != '' && !$ignore_page) {
    // details section for single page

    $pagedetails = new PageDetails($request_url, $filter_date_helper);
    $sum_data = $pagedetails->getSumPerDay();

    $content = '<h4>' . $addon->i18n('statistics_views_total') . ' <b>' . $pagedetails->getPageTotal() . '</b></h4><a href="http://' . $request_url . '" target="_blank">' . $request_url . '</a>';
    $content .= '<div id="chart_details" style="height:500px; width:auto"></div>';
    $content .= $pagedetails->getList();

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'info', false);
    $fragment->setVar('title', 'Details für:');
    $fragment->setVar('heading', $request_url);
    $fragment->setVar('body', $content, false);
    echo $fragment->parse('core/page/section.php');
}


// list of all pages
$sql = rex_sql::factory();
$domains = $sql->getArray('SELECT distinct domain FROM ' . rex::getTable('pagestats_visits_per_day'));
$domain_select = '
<select id="stats_domain_select" class="form-control">
<option value="">Alle Domains</option>
';
foreach ($domains as $domain) {
    $domain_select .= '<option value="' . $domain['domain'] . '">' . $domain['domain'] . '</option>';
}
$domain_select .= '</select>';


// buttons to filter by http status
$oa = rex_context::fromGet()->getUrl(["httpstatus" => "any"]);
$o2 = rex_context::fromGet()->getUrl(["httpstatus" => "200"]);
$on2 = rex_context::fromGet()->getUrl(["httpstatus" => "not200"]);

$http_filter_buttons = '<a class="btn btn-primary" href="' . $oa . '">Alle</a>
<a class="btn btn-primary" href="' . $o2 . '">Nur 200er</a>
<a class="btn btn-primary" href="' . $on2 . '">Nur nicht 200er</a>';


$fragment = new rex_fragment();
$fragment->setVar('title', $addon->i18n('statistics_sum_per_page'));
$fragment->setVar('body', $http_filter_buttons . '<div id="chart_visits_per_page" style="height:500px; width:auto"></div>' . $domain_select . $pages_helper->getList(), false);
echo $fragment->parse('core/page/section.php');

?>


<script>
    if (rex.theme == "dark" || window.matchMedia('(prefers-color-scheme: dark)').matches && rex.theme == "auto") {
        var theme = "dark";
    } else {
        var theme = "shine";
    }

    var chart_visits_per_page = echarts.init(document.getElementById('chart_visits_per_page'), theme);
    var chart_visits_per_page_option = {
        title: {},
        tooltip: {
            trigger: 'axis',
            formatter: function(params) {
                return `${params[0].data.url}<br />
                            Status: <b>${params[0].data.status}</b><br />
                            Anzahl: <b>${params[0].data.count}</b>`;
            },
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
            show: true,
        },
        xAxis: [{
            type: 'category',
        }],
        yAxis: {
            type: 'value'
        },
        series: [{
                datasetId: "ds0",
                stack: "stack1",
                type: "bar",
                encode: {
                    x: "url",
                    y: "zero"
                }
            },
            {
                name: "200",
                datasetId: "ds1",
                type: "bar",
                encode: {
                    x: "url",
                    y: "count"
                },
                stack: "stack1",
                color: "#198754",
            },
            {
                name: "nicht-200",
                datasetId: "ds2",
                type: "bar",
                encode: {
                    x: "url",
                    y: "count"
                },
                stack: "stack1",
                color: "#c12e34",
            }
        ],
        dataset: [{
                // Provide a set of data.
                id: "dataset_raw",
                dimensions: ["url", "count", "status", "zero"],
                source: <?= json_encode($sum_per_page) ?>
            },
            {
                id: "ds0",
                fromDatasetId: "dataset_raw",
                transform: [{
                    type: "sort",
                    config: {
                        dimension: "count",
                        order: "desc"
                    }
                }]
            },
            {
                id: "ds1",
                fromDatasetId: "ds0",
                transform: [{
                    type: "filter",
                    config: {
                        dimension: "status",
                        "=": "200 OK"
                    }
                }]
            },
            {
                id: "ds2",
                fromDatasetId: "ds0",
                transform: [{
                    type: "filter",
                    config: {
                        dimension: "status",
                        "!=": "200 OK"
                    }
                }]
            }
        ],

    };
    chart_visits_per_page.setOption(chart_visits_per_page_option);



    <?php

    if ($request_url != '' && !$ignore_page) {
        $show_toolbox = rex_config::get('statistics', 'statistics_show_chart_toolbox') ? 'true' : 'false';
        echo "var chart_details = echarts.init(document.getElementById('chart_details'), theme);
        var chart_details_option = {
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
                show: " . $show_toolbox . ",
                feature: {
                    dataZoom: {
                        yAxisIndex: 'none'
                    },
                    dataView: {
                        readOnly: false
                    },
                    magicType: {
                        type: ['line', 'bar', 'stack']
                    },
                    restore: {},
                    saveAsImage: {}
                }
            },
            legend: {},
            xAxis: {
                data:" . json_encode($sum_data['labels']) . ",
                type: 'category',
            },
            yAxis: {},
            series: [{
                data:" . json_encode($sum_data['values']) . ",
                type: 'line',
            }]
        };
        chart_details.setOption(chart_details_option);";
    }


    ?>

    $(document).ready(function() {
        stats_table_all_pages = $('.dt_order_second').DataTable({
            "paging": true,
            "pageLength": 20,
            "lengthChange": true,
            "lengthMenu": [
                [10, 20, 50, 100, 200, -1],
                [10, 20, 50, 100, 200, 'All']
            ],
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
            "pageLength": 20,
            "lengthChange": true,
            "lengthMenu": [
                [10, 20, 50, 100, 200, -1],
                [10, 20, 50, 100, 200, 'All']
            ],
            "order": [
                [0, "desc"]
            ],
            "search": {
                "caseInsensitive": false
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

        var stats_domain_select = document.getElementById('stats_domain_select');
        stats_domain_select.addEventListener('change', function() {
            var domain = this.value;
            stats_table_all_pages.search(domain).draw();
        });

    });
</script>