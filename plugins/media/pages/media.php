<?php

$search_string = htmlspecialchars_decode(rex_request('search_string', 'string', ''));
$request_url = rex_request('url', 'string', '');
$request_url = htmlspecialchars_decode($request_url);
$delete_entry = rex_request('delete_entry', 'boolean', false);


if ($request_url != '' && $delete_entry === true) {
    $sql = rex_sql::factory();
    $sql->setQuery('delete from ' . rex::getTable('pagestats_api') . ' where name = :name', ['name' => $request_url]);
    echo rex_view::success('Es wurden ' . $sql->getRows() . ' Einträge der Kampagne <code>' . $request_url . '</code> gelöscht.');
}

// details section
if ($request_url != '' && !$delete_entry) {
    // details section for single campaign

    $pagedetails = new stats_media_details($request_url);
    $sum_data = $pagedetails->get_sum_per_day();


    $content = '<div id="chart_details"></div>';

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'info', false);
    $fragment->setVar('title', 'Details für:');
    $fragment->setVar('heading', $request_url);
    $fragment->setVar('body', $content, false);
    echo $fragment->parse('core/page/section.php');
}

if ($search_string == '') {
    $list = rex_list::factory('SELECT url, sum(count) as "count" from ' . rex::getTable('pagestats_media') . ' GROUP BY url ORDER BY count DESC');
} else {
    $list = rex_list::factory('SELECT url, sum(count) as "count" from ' . rex::getTable('pagestats_media') . ' WHERE url LIKE "%' . $search_string . '%" GROUP BY url ORDER BY count DESC');
}



// $form = '
// <form class="form-inline" action="' . rex_url::backendPage('statistics/media') . '" method="GET">
//     <input type="hidden" value="statistics/media" name="page">
//     <div class="form-group">
//         <label for="exampleInputName2">' . $this->i18n('statistics_media_search_for') . '</label>
//         <input style="line-height: normal;" type="text" value="' . $search_string . '" class="form-control" name="search_string">
//     </div>
//     <button type="submit" class="btn btn-default">' . $this->i18n('statistics_media_search') . '</button>
// </form>
// ';


// $fragment = new rex_fragment();
// $fragment->setVar('title', $this->i18n('statistics_media_filter'));
// $fragment->setVar('body', $form, false);
// echo $fragment->parse('core/page/section.php');


$list->setColumnLabel('url', $this->i18n('statistics_media_url'));
$list->setColumnLabel('count', $this->i18n('statistics_media_count'));
// $list->setColumnSortable('url', $direction = 'asc');
// $list->setColumnSortable('count', $direction = 'asc');
$list->setColumnParams('url', ['url' => '###url###']);

$fragment2 = new rex_fragment();
$fragment2->setVar('title', $this->i18n('statistics_media_views'));
$fragment2->setVar('body', $list->get(), false);
echo $fragment2->parse('core/page/section.php');

?>

<script src="<?php echo rex_addon::get('statistics')->getAssetsUrl('plotly.min.js') ?>"></script>
<script src="<?php echo rex_addon::get('statistics')->getAssetsUrl('datatables.min.js') ?>"></script>
<link rel="stylesheet" href="<?php echo rex_addon::get('statistics')->getAssetsUrl('datatables.min.css') ?>">

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
    }
    var layout = {
        margin: {
            r: 25,
            l: 25,
            t: 25,
            b: 25,
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
                "caseInsensitive": false
            },
            <?php

            if (trim(rex::getUser()->getLanguage()) == '' || trim(rex::getUser()->getLanguage()) == 'de_de') {
                if (rex::getProperty('lang') == 'de_de') {
                    echo '
                    language: {
                        "search": "Suchen:",
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