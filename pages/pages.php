<?php


// echo rex_view::title('Seiten');

function get_data($column, $table, $order)
{
    $sql = rex_sql::factory();
    // $sql->setDebug(true);
    $result = $sql->setQuery('SELECT ' . $column . ' FROM ' . rex::getTable($table) . ' ORDER BY ' . $order . ' ASC');

    $array = [];

    while ($result->hasNext()) {

        $array[] = $result->getValue($column);

        $result->next();

    }

    $array = json_encode($array);

    return $array;
}

function get_data_array($column, $table, $order)
{
    $sql = rex_sql::factory();
    // $sql->setDebug(true);
    $result = $sql->setQuery('SELECT * FROM ' . rex::getTable($table) . ' ORDER BY ' . $order . ' DESC');

    return $result;
}


$sql = rex_sql::factory();
$sql->setTable(rex::getTable('pagestats_views'));
$result = $sql->select();



// sum per day
// $sql = rex_sql::factory();
// $sum_per_day = $sql->setQuery('SELECT date, SUM(count) AS "count" from ' . rex::getTable('pagestats_views') . ' GROUP BY date ORDER BY date desc');

// $sum_per_day_labels = [];
// foreach ($sum_per_day as $row) {
//     $sum_per_day_labels[] = $row->getValue('date');
// }
// $sum_per_day_labels = json_encode($sum_per_day_labels);

// $sum_per_day_values = [];
// foreach ($sum_per_day as $row) {
//     $sum_per_day_values[] = $row->getValue('count');
// }
// $sum_per_day_values = json_encode($sum_per_day_values);


// sum per page
$sql = rex_sql::factory();
$sum_per_page = $sql->setQuery('SELECT url, SUM(count) AS "count" from ' . rex::getTable('pagestats_views') . ' GROUP BY url ORDER BY url asc');

$sum_per_page_labels = [];
foreach ($sum_per_page as $row) {
    $sum_per_page_labels[] = $row->getValue('url');
}
$sum_per_page_labels = json_encode($sum_per_page_labels);

$sum_per_page_values = [];
foreach ($sum_per_page as $row) {
    $sum_per_page_values[] = $row->getValue('count');
}
$sum_per_page_values = json_encode($sum_per_page_values);





$sql = rex_sql::factory();
$total_per_date = $sql->setQuery('SELECT date, SUM(count) AS "count" from ' . rex::getTable('pagestats_views') . ' GROUP BY date ORDER BY date DESC');

?>

<script src="https://cdn.plot.ly/plotly-2.0.0-rc.3.min.js"></script>


<!-- <h3>Summe pro Tag:</h3>

<div id="chart_visits"></div>

<table class="table">
    <tr>
        <th>Datum</th>
        <th>Aufrufe</th>
    </tr>

    <?php

    foreach ($sum_per_day as $row) {
        echo '<tr>';
        echo '<td>' . $row->getValue('date') . '</td>';
        echo '<td>' . $row->getValue('count') . '</td>';
        echo '</tr>';
    }

    ?>
</table> -->


<h3>Summe pro Seite:</h3>

<div id="chart_visits_per_page"></div>

<table class="table">
    <tr>
        <th>URL</th>
        <th>Aufrufe</th>
    </tr>

    <?php

    foreach ($sum_per_page as $row) {
        echo '<tr>';
        echo '<td>' . $row->getValue('url') . '</td>';
        echo '<td>' . $row->getValue('count') . '</td>';
        echo '</tr>';
    }

    ?>
</table>

<h3>Aufrufe pro Tag pro Seite:</h3>

<table class="table">
    <tr>
        <th>URL</th>
        <th>Datum</th>
        <th>Aufrufe</th>
    </tr>

    <?php

    foreach ($result as $row) {
        echo '<tr>';
        echo '<td>' . $row->getValue('url') . '</td>';
        echo '<td>' . $row->getValue('date') . '</td>';
        echo '<td>' . $row->getValue('count') . '</td>';
        echo '</tr>';
    }

    ?>
</table>

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


    chart_visits = Plotly.newPlot('chart_visits', [{
        type: 'line',
        x: <?php echo $sum_per_day_labels ?>,
        y: <?php echo $sum_per_day_values ?>,
    }], layout, config);

    chart_visits_per_page = Plotly.newPlot('chart_visits_per_page', [{
        type: 'bar',
        x: <?php echo $sum_per_page_labels ?>,
        y: <?php echo $sum_per_page_values ?>,
    }], layout, config);


</script>