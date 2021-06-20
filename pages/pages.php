<?php


// sum per page
$sql = rex_sql::factory();
$sum_per_page = $sql->setQuery('SELECT url, SUM(count) AS "count" from ' . rex::getTable('pagestats_views') . ' GROUP BY url ORDER BY url asc');

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


?>

<script src="<?php echo rex_addon::get('stats')->getAssetsUrl('plotly-2.0.0.min.js') ?>"></script>


<?php

$request_url = rex_request('url', 'array', []);


if ($request_url != []) {

    $request_url = rex_escape($request_url[0]);


    $sql = rex_sql::factory();
    $details = $sql->setQuery('SELECT date, count FROM ' . rex::getTable('pagestats_views') . '  WHERE url = :url ORDER BY url asc', ['url' => $request_url]);

    $details_labels = [];
    $details_values = [];

    foreach ($details as $row) {
        $details_labels[] = $row->getValue('date');
    }
    $details_labels = json_encode($details_labels);


    foreach ($details as $row) {
        $details_values[] = $row->getValue('count');
    }
    $details_values = json_encode($details_values);


    $list = rex_list::factory('SELECT date, count FROM ' . rex::getTable('pagestats_views') . ' WHERE url = "' . $request_url . '" ORDER BY url ASC');
    $list->setColumnLabel('date', 'Datum');
    $list->setColumnLabel('count', 'Anzahl');
    $list->setColumnSortable('date', $direction = 'asc');
    $list->setColumnSortable('count', $direction = 'asc');
    $list->setColumnParams('url', ['url' => '###url###']);


    echo '<div class="panel panel-edit">';
    echo '<header class="panel-heading">';
    echo '<div class="panel-title">Details für:</div>' . $request_url;
    echo '</header>';

    echo '<div class="panel-body">';
    echo '<div id="chart_details"></div>';
    $list->show();
    echo '</div>';
    echo '</div>';


    echo '<hr>';
}


?>


<h3>Summe pro Seite:</h3>

<div id="chart_visits_per_page"></div>
<?php

$list = rex_list::factory('SELECT url, SUM(count) AS "count" from ' . rex::getTable('pagestats_views') . ' GROUP BY url ORDER BY url ASC');
$list->setColumnLabel('url', 'Url');
$list->setColumnLabel('count', 'Anzahl');
$list->setColumnSortable('url', $direction = 'asc');
$list->setColumnSortable('count', $direction = 'asc');
$list->setColumnParams('url', ['url' => '###url###']);
$list->show();

?>

<h3>Aufrufe pro Tag pro Seite:</h3>
<?php

$list = rex_list::factory('SELECT * FROM ' . rex::getTable('pagestats_views'));
$list->setColumnLabel('url', 'Url');
$list->setColumnLabel('date', 'Datum');
$list->setColumnLabel('count', 'Anzahl');
$list->setColumnSortable('url', $direction = 'asc');
$list->setColumnSortable('date', $direction = 'asc');
$list->setColumnSortable('count', $direction = 'asc');
$list->show();

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
    }
    var layout = {
        margin: {
            r: 25,
            l: 25,
            t: 25,
            b: 25,
        },
    }


    chart_visits_per_page = Plotly.newPlot('chart_visits_per_page', [{
        type: 'bar',
        x: <?php echo $sum_per_page_labels ?>,
        y: <?php echo $sum_per_page_values ?>,
    }], layout, config);

    <?php 

    if ($request_url != []) {
        echo 'chart_details = Plotly.newPlot("chart_details", [{
            type: "line",
            x:' . $details_labels . ',
            y:' . $details_values . ',
        }], layout, config);';
    }


    ?>


</script>