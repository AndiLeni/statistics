<?php

use AndiLeni\Statistics\DateFilter;
use AndiLeni\Statistics\RefererDetails;

$addon = rex_addon::get('statistics');

$current_backend_page = rex_get('page', 'string', '');
$request_date_start = htmlspecialchars_decode(rex_request('date_start', 'string', ''));
$request_date_end = htmlspecialchars_decode(rex_request('date_end', 'string', ''));
$request_ref = htmlspecialchars_decode(rex_request('referer', 'string', ''));

$filter_date_helper = new DateFilter($request_date_start, $request_date_end, 'pagestats_referer');



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

// details for one url requested
if ($request_ref != '') {
    // details section for single page

    $refererDetails = new RefererDetails($request_ref, $filter_date_helper);
    $sum_data = $refererDetails->getSumPerDay();

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'info', false);
    $fragment->setVar('title', 'Details für:');
    $fragment->setVar('heading', $request_ref);
    $fragment->setVar('body', '<a target="_blank" href="' . htmlspecialchars($request_ref, ENT_QUOTES) . '">' . htmlspecialchars($request_ref, ENT_QUOTES) . '</a><div id="chart_details" style="height:500px; width:auto"></div>' . $refererDetails->getList(), false);
    echo $fragment->parse('core/page/section.php');
}

$sql = rex_sql::factory();
$refererRows = $sql->getArray(
    'SELECT referer, SUM(count) AS count FROM ' . rex::getTable('pagestats_referer')
    . ' WHERE date BETWEEN :start AND :end GROUP BY referer ORDER BY count DESC, referer ASC',
    [
        'start' => $filter_date_helper->date_start->format('Y-m-d'),
        'end' => $filter_date_helper->date_end->format('Y-m-d'),
    ]
);

if ([] === $refererRows) {
    $table = rex_view::info($addon->i18n('statistics_no_data'));
} else {
    $table = '<table class="table-bordered dt_order_first statistics_table table-striped table-hover table">';
    $table .= '<thead><tr><th>Referer</th><th>' . htmlspecialchars($addon->i18n('statistics_count'), ENT_QUOTES) . '</th></tr></thead><tbody>';

    foreach ($refererRows as $row) {
        $referer = (string) $row['referer'];
        $count = (string) $row['count'];
        $detailUrl = rex_context::fromGet()->getUrl([
            'referer' => $referer,
            'date_start' => $filter_date_helper->date_start->format('Y-m-d'),
            'date_end' => $filter_date_helper->date_end->format('Y-m-d'),
        ]);

        $table .= '<tr>';
        $table .= '<td><a href="' . htmlspecialchars($detailUrl, ENT_QUOTES) . '">' . htmlspecialchars($referer, ENT_QUOTES) . '</a></td>';
        $table .= '<td data-sort="' . htmlspecialchars($count, ENT_QUOTES) . '">' . htmlspecialchars($count, ENT_QUOTES) . '</td>';
        $table .= '</tr>';
    }

    $table .= '</tbody></table>';
}

$fragment = new rex_fragment();
$fragment->setVar('title', $addon->i18n('statistics_all_referer'));
$fragment->setVar('body', $table, false);
echo $fragment->parse('core/page/section.php');


?>


<script>
    if (rex.theme == "dark" || window.matchMedia('(prefers-color-scheme: dark)').matches && rex.theme == "auto") {
        var theme = "dark";
    } else {
        var theme = "shine";
    }

    <?php

    if ($request_ref != '') {
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

        $('.dt_order_first').DataTable({
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
    });
</script>
