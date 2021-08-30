<?php

$addon = rex_addon::get('statistics');

$current_backend_page = rex_get('page', 'string', '');
$request_url = rex_request('url', 'string', '');
$request_url = htmlspecialchars_decode($request_url);
$ignore_page = rex_request('ignore_page', 'boolean', false);
$search_string = htmlspecialchars_decode(rex_request('search_string', 'string', ''));
$request_date_start = htmlspecialchars_decode(rex_request('date_start', 'string', ''));
$request_date_end = htmlspecialchars_decode(rex_request('date_end', 'string', ''));

$filter_date_helper = new filter_date_helper($request_date_start, $request_date_end, 'pagestats_dump');
$pages_helper = new pages_helper($filter_date_helper->date_start, $filter_date_helper->date_end);




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

// sum per page, bar chart
$sum_per_page = $pages_helper->sum_per_page();


// check if request is for ignoring a url
// if yes, add url to addon settings and delete all database entries of this url 
if ($request_url != '' && $ignore_page === true) {
    $rows = $pages_helper->ignore_page($request_url);
    echo rex_view::success('Es wurden ' . $rows . ' Einträge gelöscht. Die Url <code>' . $request_url . '</code> wird zukünftig ignoriert.');
}


// details for one url requested
if ($request_url != '' && !$ignore_page) {
    // details section for single page

    $pagedetails = new stats_pagedetails($request_url, $filter_date_helper->date_start, $filter_date_helper->date_end);
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

    dump($pagedetails->get_page_total());
}


// list of all pages
$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('statistics_sum_per_page'));
$fragment->setVar('body', '<div id="chart_visits_per_page"></div>' . $pages_helper->get_list(), false);
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
            l: 50,
            t: 25,
            b: <?php echo $addon->getConfig('pagestats_chart_padding_bottom') ?>,
        },
    }


    chart_visits_per_page = Plotly.newPlot('chart_visits_per_page', [{
        type: 'bar',
        x: <?php echo $sum_per_page['labels'] ?>,
        y: <?php echo $sum_per_page['values'] ?>,
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