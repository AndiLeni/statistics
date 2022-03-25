<?php

$addon = rex_addon::get('statistics');

$current_backend_page = rex_get('page', 'string', '');
$request_date_start = htmlspecialchars_decode(rex_request('date_start', 'string', ''));
$request_date_end = htmlspecialchars_decode(rex_request('date_end', 'string', ''));
$request_ref = htmlspecialchars_decode(rex_request('referer', 'string', ''));

$filter_date_helper = new filter_date_helper($request_date_start, $request_date_end, 'pagestats_referer');



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

    $list = rex_list::factory('SELECT date, count FROM ' . rex::getTable('pagestats_referer') . ' WHERE referer = "' . $request_ref . '" and date between "' . $filter_date_helper->date_start->format('Y-m-d') . '" and "' . $filter_date_helper->date_end->format('Y-m-d') . '" GROUP BY date ORDER BY count DESC', 10000);
    $list->addTableAttribute('class', 'table-bordered dt_order_first statistics_table');
    $list->setColumnLabel('date', 'Datum');
    $list->setColumnLabel('count', 'Anzahl');
    $list->setColumnFormat('date', 'date', 'd.m.Y');
    $list->setColumnLayout('date', ['<th>###VALUE###</th>', '<td data-sort="###date###">###VALUE###</td>']);


    $fragment = new rex_fragment();
    $fragment->setVar('class', 'info', false);
    $fragment->setVar('title', 'Details für:');
    $fragment->setVar('heading', $request_ref);
    $fragment->setVar('body', '<a target="_blank" href="' . $request_ref . '">' . $request_ref . '</a><div id="chart_details" style="height:500px; width:auto"></div>' . $list->get(), false);
    echo $fragment->parse('core/page/section.php');
}




$list = rex_list::factory('SELECT referer, SUM(count) as "count" from ' . rex::getTable('pagestats_referer') . ' where date between "' . $filter_date_helper->date_start->format('Y-m-d') . '" and "' . $filter_date_helper->date_end->format('Y-m-d') . '" GROUP BY referer ORDER BY count DESC, referer ASC', 10000);

$list->setColumnLabel('referer', 'Referer');
$list->setColumnLabel('count', $this->i18n('statistics_count'));
$list->setColumnParams('referer', ['referer' => '###referer###', 'date_start' => $filter_date_helper->date_start->format('Y-m-d'), 'date_end' => $filter_date_helper->date_end->format('Y-m-d')]);
$list->addTableAttribute('class', 'table-bordered dt_order_first statistics_table');

if ($list->getRows() == 0) {
    $table = rex_view::info($this->i18n('statistics_no_data'));
} else {
    $table = $list->get();
}

$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('statistics_all_referer'));
$fragment->setVar('body', $table, false);
echo $fragment->parse('core/page/section.php');


?>


<script>
    <?php

    if ($request_ref != '') {

        $sql = rex_sql::factory();

        // modify to include end date in period because SQL BETWEEN includes start and end date, but DatePeriod excludes end date
        // without modification an additional day would be fetched from database
        $end = $filter_date_helper->date_end;
        $end = $end->modify('+1 day');

        $period = new DatePeriod(
            $filter_date_helper->date_start,
            new DateInterval('P1D'),
            $end
        );

        foreach ($period as $value) {
            $array[$value->format("d.m.Y")] = "0";
        }

        $sum_per_day = $sql->setQuery('SELECT date, count from ' . rex::getTable('pagestats_referer') . ' WHERE referer = :referer and date between :start and :end GROUP BY date ORDER BY date ASC', ['referer' => $request_ref, 'start' => $filter_date_helper->date_start->format('Y-m-d'), 'end' => $filter_date_helper->date_end->format('Y-m-d')]);

        $data = [];

        if ($sum_per_day->getRows() != 0) {
            foreach ($sum_per_day as $row) {
                $date = DateTime::createFromFormat('Y-m-d', $row->getValue('date'))->format('d.m.Y');
                $arr2[$date] = $row->getValue('count');
            }

            $data = array_merge($array, $arr2);
        }

        $sum_data = [
            'labels' => array_keys($data),
            'values' => array_values($data),
        ];

        echo "var chart_details = echarts.init(document.getElementById('chart_details'));
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
                show: true,
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
        $('.dt_order_second').DataTable({
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