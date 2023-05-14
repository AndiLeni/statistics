<?php

use AndiLeni\Statistics\filterDateHelper;
use AndiLeni\Statistics\stats_media_details;

$addon = rex_addon::get('statistics');

$current_backend_page = rex_get('page', 'string', '');
$search_string = htmlspecialchars_decode(rex_request('search_string', 'string', ''));
$request_url = rex_request('url', 'string', '');
$request_url = htmlspecialchars_decode($request_url);
$delete_entry = rex_request('delete_entry', 'boolean', false);
$request_date_start = htmlspecialchars_decode(rex_request('date_start', 'string', ''));
$request_date_end = htmlspecialchars_decode(rex_request('date_end', 'string', ''));

$filter_date_helper = new filterDateHelper($request_date_start, $request_date_end, 'pagestats_media');



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

if ($request_url != '' && $delete_entry === true) {
    $sql = rex_sql::factory();
    $sql->setQuery('delete from ' . rex::getTable('pagestats_media') . ' where url = :url', ['url' => $request_url]);
    echo rex_view::success('Es wurden ' . $sql->getRows() . ' Einträge der Kampagne <code>' . $request_url . '</code> gelöscht.');
}

// details section
if ($request_url != '' && !$delete_entry) {
    // details section for single campaign

    $pagedetails = new stats_media_details($request_url, $filter_date_helper);
    $sum_data = $pagedetails->get_sum_per_day();

    $content = '<div id="chart_details" style="height:500px; width:auto"></div>';

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'info', false);
    $fragment->setVar('title', 'Details für:');
    $fragment->setVar('heading', $request_url);
    $fragment->setVar('body', $content, false);
    echo $fragment->parse('core/page/section.php');
}



$list = rex_list::factory('SELECT url, sum(count) as "count" from ' . rex::getTable('pagestats_media') . ' where date between "' . $filter_date_helper->date_start->format('Y-m-d') . '" and "' . $filter_date_helper->date_end->format('Y-m-d') . '" GROUP BY url ORDER BY count DESC', 10000);


$list->setColumnLabel('url', $addon->i18n('statistics_media_url'));
$list->setColumnLabel('count', $addon->i18n('statistics_media_count'));
// $list->setColumnSortable('url', $direction = 'asc');
// $list->setColumnSortable('count', $direction = 'asc');
$list->setColumnParams('url', ['url' => '###url###', 'date_start' => $filter_date_helper->date_start->format('Y-m-d'), 'date_end' => $filter_date_helper->date_end->format('Y-m-d')]);
$list->addTableAttribute('class', 'table-bordered statistics_table');

if ($list->getRows() == 0) {
    $table = rex_view::info($addon->i18n('statistics_no_data'));
} else {
    $table = $list->get();
}

$fragment2 = new rex_fragment();
$fragment2->setVar('title', $addon->i18n('statistics_media_views'));
$fragment2->setVar('body', $table, false);
echo $fragment2->parse('core/page/section.php');

?>


<script>
    if (rex.theme == "dark" || window.matchMedia('(prefers-color-scheme: dark)').matches && rex.theme == "auto") {
        var theme = "dark";
    } else {
        var theme = "shine";
    }

    var config = {
        responsive: true,
        toImageButtonOptions: {
            format: 'jpeg',
            filename: 'plot',
            height: 750,
            width: 1000,
            scale: 1,
        },
        displaylogo: false,
        displayModeBar: true,
    }
    var layout = {
        margin: {
            r: 25,
            l: 25,
            t: 25,
            b: 100,
        },
    }


    <?php

    if ($request_url != '' && !$delete_entry) {
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
        $('.table').DataTable({
            "paging": true,
            "pageLength": 20,
            "lengthChange": true,
            "lengthMenu": [
                [10, 20, 50, 100, 200, -1],
                [10, 20, 50, 100, 200, 'All']
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
    });
</script>