<?php

$addon = rex_addon::get('statistics');

$request_url = rex_request('url', 'string', '');
$request_url = htmlspecialchars_decode($request_url);
$ignore_page = rex_request('ignore_page', 'boolean', false);
$search_string = htmlspecialchars_decode(rex_request('search_string', 'string', ''));

// sum per page, bar chart
$sql = rex_sql::factory();

if ($search_string == '') {
    $sum_per_page = $sql->setQuery('SELECT url, COUNT(url) AS "count" from ' . rex::getTable('pagestats_dump') . ' GROUP BY url ORDER BY count DESC, url ASC');
} else {
    $sum_per_page = $sql->setQuery('SELECT url, COUNT(url) as "count" from ' . rex::getTable('pagestats_dump') . ' WHERE url LIKE :url GROUP BY url ORDER BY count DESC, url ASC', ['url' => '%' . $search_string . '%']);
}

$sum_per_page_labels = [];
$sum_per_page_values = [];

foreach ($sum_per_page as $row) {
    $sum_per_page_labels[] = $row->getValue('url');
}
$sum_per_page_labels = json_encode($sum_per_page_labels);


foreach ($sum_per_page as $row) {
    $sum_per_page_values[] = $row->getValue('count');
}
$sum_per_page_values = json_encode($sum_per_page_values);




// search form
// $form = '
// <form class="form-inline" action="' . rex_url::backendPage('statistics/pages') . '" method="GET">
//     <input type="hidden" value="statistics/pages" name="page">
//     <div class="form-group">
//         <label for="exampleInputName2">' . $this->i18n('statistics_search_for') . '</label>
//         <input style="line-height: normal;" type="text" value="' . $search_string . '" class="form-control" name="search_string">
//     </div>
//     <button type="submit" class="btn btn-default">' . $this->i18n('statistics_search') . '</button>
// </form>
// ';

// $fragment = new rex_fragment();
// $fragment->setVar('title', $this->i18n('statistics_views_per_day'));
// $fragment->setVar('body', $form, false);
// echo $fragment->parse('core/page/section.php');



// check if request is for ignoring a url
// if yes, add url to addon settings and delete all database entries of this url 
if ($request_url != '' && $ignore_page === true) {
    $ignored_paths = $addon->getConfig('pagestats_ignored_paths');
    $addon->setConfig('pagestats_ignored_paths', $ignored_paths . PHP_EOL . $request_url);

    $sql = rex_sql::factory();
    $sql->setQuery('delete from ' . rex::getTable('pagestats_dump') . ' where url = :url', ['url' => $request_url]);
    echo rex_view::success('Es wurden ' . $sql->getRows() . ' Einträge gelöscht. Die Url <code>' . $request_url . '</code> wird zukünftig ignoriert.');
}

if ($request_url != '' && !$ignore_page) {
    // details section for single page

    $pagedetails = new stats_pagedetails($request_url);
    $browsertype_data = $pagedetails->get_browsertype();
    $browser_data = $pagedetails->get_browser();
    $os_data = $pagedetails->get_os();
    $sum_data = $pagedetails->get_sum_per_day();


    $content = '<div class="row">
    <div class="col-md-4">
        <div class="panel panel-default">
            <div class="panel-body">
                <div id="chart_details_devicetype"></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="panel panel-default">
            <div class=" panel-body">
                <div id="chart_details_browser"></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="panel panel-default">
            <div class=" panel-body">
                <div id="chart_details_os"></div>
            </div>
        </div>
    </div>
    </div>
    <div id="chart_details"></div>
    ' . $pagedetails->get_list();

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'info', false);
    $fragment->setVar('title', 'Details für:');
    $fragment->setVar('heading', $request_url);
    $fragment->setVar('body', '<h4>' . $this->i18n('statistics_views_total') . ' <b>' . $pagedetails->get_page_total() . '</b></h4>', false);
    $fragment->setVar('body', $content, false);
    echo $fragment->parse('core/page/section.php');
}


if ($search_string == '') {
    $list = rex_list::factory('SELECT url, COUNT(url) AS "count" from ' . rex::getTable('pagestats_dump') . ' GROUP BY url ORDER BY count DESC, url ASC', 500);
} else {
    $list = rex_list::factory('SELECT url, COUNT(url) as "count" from ' . rex::getTable('pagestats_dump') . ' WHERE url LIKE "%' . $search_string . '%" GROUP BY url ORDER BY count DESC, url ASC', 500);
}


$list->setColumnLabel('url', $this->i18n('statistics_url'));
$list->setColumnLabel('count', $this->i18n('statistics_count'));
$list->setColumnParams('url', ['url' => '###url###']);

$list->addColumn('edit', $this->i18n('statistics_ignore_and_delete'));
$list->setColumnLabel('edit', $this->i18n('statistics_ignore'));
$list->addLinkAttribute('edit', 'data-confirm', '###url###:' . PHP_EOL . $this->i18n('statistics_confirm_ignore_delete'));
$list->setColumnParams('edit', ['url' => '###url###', 'ignore_page' => true]);
$list->addFormAttribute('style', 'margin-top: 3rem');
$list->addTableAttribute('class', 'table-bordered');
$list->addTableAttribute('class', 'dt_order_second');


$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('statistics_sum_per_page'));
$fragment->setVar('body', '<div id="chart_visits_per_page"></div>' . $list->get(), false);
echo $fragment->parse('core/page/section.php');

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
            b: <?php echo $addon->getConfig('pagestats_chart_padding_bottom') ?>,
        },
    }


    chart_visits_per_page = Plotly.newPlot('chart_visits_per_page', [{
        type: 'bar',
        x: <?php echo $sum_per_page_labels ?>,
        y: <?php echo $sum_per_page_values ?>,
    }], layout, config);

    <?php

    if ($request_url != '' && !$ignore_page) {
        echo 'chart_details = Plotly.newPlot("chart_details", [{
            type: "line",
            x:' . $sum_data['labels'] . ',
            y:' . $sum_data['values'] . ',
        }], layout, config);';

        echo 'chart_details_devicetype = Plotly.newPlot("chart_details_devicetype", [{
            type: "pie",
            labels:' . $browsertype_data['labels'] . ',
            values:' . $browsertype_data['values'] . ',
        }], layout, config);';

        echo 'chart_details_browser = Plotly.newPlot("chart_details_browser", [{
            type: "pie",
            labels:' . $browser_data['labels'] . ',
            values:' . $browser_data['values'] . ',
        }], layout, config);';

        echo 'chart_details_os = Plotly.newPlot("chart_details_os", [{
            type: "pie",
            labels:' . $os_data['labels'] . ',
            values:' . $os_data['values'] . ',
        }], layout, config);';
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