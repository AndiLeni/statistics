<?php

$current_backend_page = rex_get('page', 'string', '');
$search_string = htmlspecialchars_decode(rex_request('search_string', 'string', ''));
$request_url = rex_request('url', 'string', '');
$request_url = htmlspecialchars_decode($request_url);
$delete_entry = rex_request('delete_entry', 'boolean', false);
$request_date_start = htmlspecialchars_decode(rex_request('date_start', 'string', ''));
$request_date_end = htmlspecialchars_decode(rex_request('date_end', 'string', ''));

$filter_date_helper = new filter_date_helper($request_date_start, $request_date_end, 'pagestats_media');



// FRAGMENT FOR DATE FILTER
$filter_fragment = new rex_fragment();
$filter_fragment->setVar('current_backend_page', $current_backend_page);
$filter_fragment->setVar('date_start', $filter_date_helper->date_start);
$filter_fragment->setVar('date_end', $filter_date_helper->date_end);


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

    $pagedetails = new stats_media_details($request_url, $filter_date_helper->date_start, $filter_date_helper->date_end);
    $sum_data = $pagedetails->get_sum_per_day();

    $content = '<div id="chart_details"></div>';

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'info', false);
    $fragment->setVar('title', 'Details für:');
    $fragment->setVar('heading', $request_url);
    $fragment->setVar('body', $content, false);
    echo $fragment->parse('core/page/section.php');
}



$list = rex_list::factory('SELECT url, sum(count) as "count" from ' . rex::getTable('pagestats_media') . ' where date between "' . $filter_date_helper->date_start->format('Y-m-d') . '" and "' . $filter_date_helper->date_end->format('Y-m-d') . '" GROUP BY url ORDER BY count DESC', 10000);


$list->setColumnLabel('url', $this->i18n('statistics_media_url'));
$list->setColumnLabel('count', $this->i18n('statistics_media_count'));
// $list->setColumnSortable('url', $direction = 'asc');
// $list->setColumnSortable('count', $direction = 'asc');
$list->setColumnParams('url', ['url' => '###url###', 'date_start' => $filter_date_helper->date_start->format('Y-m-d'), 'date_end' => $filter_date_helper->date_end->format('Y-m-d')]);
$list->addTableAttribute('class', 'table-bordered');

$fragment2 = new rex_fragment();
$fragment2->setVar('title', $this->i18n('statistics_media_views'));
$fragment2->setVar('body', $list->get(), false);
echo $fragment2->parse('core/page/section.php');

?>


<script>
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
        echo 'chart_details = Plotly.newPlot("chart_details", [{
            type: "line",
            x:' . $sum_data['labels'] . ',
            y:' . $sum_data['values'] . ',
        }], layout, config);';
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