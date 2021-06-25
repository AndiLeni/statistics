<?php


// sum per page, bar chart
$sql = rex_sql::factory();
$sum_per_page = $sql->setQuery('SELECT url, COUNT(url) AS "count" from ' . rex::getTable('pagestats_dump') . ' GROUP BY url ORDER BY count DESC');

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



$request_url = rex_request('url', 'string', '');


// details section for single page
if ($request_url != '') {


    $request_url = rex_escape($request_url);

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
    $fragment->setVar('body', '<h4>Aufrufe insgesamt: <b>' . $pagedetails->get_page_total() . '</b></h4>', false);
    $fragment->setVar('content', $content, false);
    echo $fragment->parse('core/page/section.php');

}


$list = rex_list::factory('SELECT url, COUNT(url) AS "count" from ' . rex::getTable('pagestats_dump') . ' GROUP BY url ORDER BY count DESC');
$list->setColumnLabel('url', 'Url');
$list->setColumnLabel('count', 'Anzahl');
// $list->setColumnSortable('url', $direction = 'asc'); needs fix, "url" url-param not set when reorderung
// $list->setColumnSortable('count', $direction = 'asc'); needs fix, "url" url-param not set when reorderung
$list->setColumnParams('url', ['url' => '###url###']);

$fragment = new rex_fragment();
$fragment->setVar('title', 'Summe pro Seite:');
$fragment->setVar('content', '<div id="chart_visits_per_page"></div>' . $list->get(), false);
echo $fragment->parse('core/page/section.php');

?>

<script src="<?php echo rex_addon::get('stats')->getAssetsUrl('plotly-2.0.0.min.js') ?>"></script>

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
            b: 90,
        },
    }


    chart_visits_per_page = Plotly.newPlot('chart_visits_per_page', [{
        type: 'bar',
        x: <?php echo $sum_per_page_labels ?>,
        y: <?php echo $sum_per_page_values ?>,
    }], layout, config);

    <?php

    if ($request_url != '') {
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
</script>