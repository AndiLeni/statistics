<?php

use AndiLeni\Statistics\DateFilter;
use AndiLeni\Statistics\EventDetails;

$addon = rex_addon::get('statistics');

$current_backend_page = rex_get('page', 'string', '');
$search_string = htmlspecialchars_decode(rex_request('search_string', 'string', ''));
$request_name = rex_request('name', 'string', '');
$request_name = htmlspecialchars_decode($request_name);
$delete_entry = rex_request('delete_entry', 'boolean', false);
$request_date_start = htmlspecialchars_decode(rex_request('date_start', 'string', ''));
$request_date_end = htmlspecialchars_decode(rex_request('date_end', 'string', ''));

$filter_date_helper = new DateFilter($request_date_start, $request_date_end, 'pagestats_api');



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


if ($request_name != '' && $delete_entry === true) {
    $sql = rex_sql::factory();
    $sql->setQuery('delete from ' . rex::getTable('pagestats_api') . ' where name = :name', ['name' => $request_name]);
    echo rex_view::success('Es wurden ' . $sql->getRows() . ' Einträge der Kampagne <code>' . $request_name . '</code> gelöscht.');
}

// details section
if ($request_name != '' && !$delete_entry) {
    // details section for single campaign

    $pagedetails = new EventDetails($request_name, $filter_date_helper);
    $sum_data = $pagedetails->getSumPerDay();


    $content = '<div id="chart_details" style="height:500px; width:auto"></div>';

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'info', false);
    $fragment->setVar('title', 'Details für:');
    $fragment->setVar('heading', $request_name);
    $fragment->setVar('body', $content, false);
    echo $fragment->parse('core/page/section.php');
}
$sql = rex_sql::factory();
$eventRows = $sql->getArray(
    'SELECT name, SUM(count) AS count FROM ' . rex::getTable('pagestats_api')
    . ' WHERE date BETWEEN :start AND :end GROUP BY name ORDER BY count DESC',
    [
        'start' => $filter_date_helper->date_start->format('Y-m-d'),
        'end' => $filter_date_helper->date_end->format('Y-m-d'),
    ]
);

if ([] === $eventRows) {
    $table = rex_view::info($addon->i18n('statistics_no_data'));
} else {
    $table = '<table class="table-bordered statistics_table table-striped table-hover table">';
    $table .= '<thead><tr>';
    $table .= '<th>' . htmlspecialchars($addon->i18n('statistics_api_name'), ENT_QUOTES) . '</th>';
    $table .= '<th>' . htmlspecialchars($addon->i18n('statistics_api_count'), ENT_QUOTES) . '</th>';
    $table .= '<th>' . htmlspecialchars($addon->i18n('statistics_api_delete'), ENT_QUOTES) . '</th>';
    $table .= '</tr></thead><tbody>';

    foreach ($eventRows as $row) {
        $name = (string) $row['name'];
        $count = (string) $row['count'];
        $detailUrl = rex_context::fromGet()->getUrl([
            'name' => $name,
            'date_start' => $filter_date_helper->date_start->format('Y-m-d'),
            'date_end' => $filter_date_helper->date_end->format('Y-m-d'),
        ]);
        $deleteUrl = rex_context::fromGet()->getUrl([
            'name' => $name,
            'delete_entry' => true,
        ]);
        $confirm = htmlspecialchars($name . PHP_EOL . $addon->i18n('statistics_api_delete_confirm'), ENT_QUOTES);

        $table .= '<tr>';
        $table .= '<td><a href="' . htmlspecialchars($detailUrl, ENT_QUOTES) . '">' . htmlspecialchars($name, ENT_QUOTES) . '</a></td>';
        $table .= '<td data-sort="' . htmlspecialchars($count, ENT_QUOTES) . '">' . htmlspecialchars($count, ENT_QUOTES) . '</td>';
        $table .= '<td><a href="' . htmlspecialchars($deleteUrl, ENT_QUOTES) . '" data-confirm="' . $confirm . '">' . htmlspecialchars($addon->i18n('statistics_api_delete'), ENT_QUOTES) . '</a></td>';
        $table .= '</tr>';
    }

    $table .= '</tbody></table>';
}

$fragment2 = new rex_fragment();
$fragment2->setVar('title', $addon->i18n('statistics_api_campaign_views'));
$fragment2->setVar('body', $table, false);
echo $fragment2->parse('core/page/section.php');

?>


<script>
    if (rex.theme == "dark" || window.matchMedia('(prefers-color-scheme: dark)').matches && rex.theme == "auto") {
        var theme = "dark";
    } else {
        var theme = "shine";
    }

    <?php

    if ($request_name != '' && !$delete_entry) {
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
