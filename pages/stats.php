<?php

function get_data($column, $table, $order)
{
    $sql = rex_sql::factory();
    // $sql->setDebug(true);
    $result = $sql->setQuery('SELECT ' . $column . ' FROM ' . rex::getTable($table) . ' ORDER BY ' . $order . ' ASC');

    $data = '[';

    while ($result->hasNext()) {

        $data .= '"' . $result->getValue($column) . '"';

        $result->next();

        if ($result->hasNext()) {
            $data .= ',';
        }
    }

    $data .= ']';

    return $data;
}

function get_data_array($column, $table, $order)
{
    $sql = rex_sql::factory();
    // $sql->setDebug(true);
    $result = $sql->setQuery('SELECT * FROM ' . rex::getTable($table) . ' ORDER BY ' . $order . ' DESC');

    return $result;
}


$sql = rex_sql::factory();
$max_date = $sql->setQuery('SELECT MAX(date) AS "date" from ' . rex::getTable('pagestats_views'));
$max_date = $max_date->getValue('date');
$max_date = new DateTime($max_date);
$max_date->modify('+1 day');
$max_date = $max_date->format('d.m.Y');


$min_date = $sql->setQuery('SELECT MIN(date) AS "date" from ' . rex::getTable('pagestats_views'));
$min_date = $min_date->getValue('date');

$period = new DatePeriod(
    new DateTime($min_date),
    new DateInterval('P1D'),
    new DateTime($max_date)
);

foreach ($period as $value) {
    $array[$value->format("d.m.Y")] = "0";
}


$sum_per_day = $sql->setQuery('SELECT date, SUM(count) AS "count" from ' . rex::getTable('pagestats_views') . ' GROUP BY date ORDER BY date ASC');

foreach ($sum_per_day as $row) {
    $arr2[$row->getValue('date')] = $row->getValue('count');
}

$data = array_merge($array, $arr2);

$sum_per_day_labels = json_encode(array_keys($data));
$sum_per_day_values = json_encode(array_values($data));

?>

<!-- html begin -->

<script src="https://cdn.plot.ly/plotly-2.0.0-rc.3.min.js"></script>




<div id="chart_visits"></div>


<div class="row">
    <div class="col-12 col-md-6">
        <h3>Browser:</h3>
        <div id="chart_browser"></div>

        <table class="table">
            <tr>
                <th>Name</th>
                <th>Anzahl</th>
            </tr>

            <?php

            foreach (get_data_array('name', 'pagestats_browser', 'count') as $row) {
                echo '<tr>';
                echo '<td>' . $row->getValue('name') . '</td>';
                echo '<td>' . $row->getValue('count') . '</td>';
                echo '</tr>';
            }

            ?>

        </table>



    </div>
    <div class="col-12 col-md-6">
        <h3>Gerätetyp:</h3>
        <div id="chart_browsertype"></div>

        <table class="table">
            <tr>
                <th>Name</th>
                <th>Anzahl</th>
            </tr>

            <?php

            foreach (get_data_array('name', 'pagestats_browsertype', 'count') as $row) {
                echo '<tr>';
                echo '<td>' . $row->getValue('name') . '</td>';
                echo '<td>' . $row->getValue('count') . '</td>';
                echo '</tr>';
            }

            ?>

        </table>

    </div>
</div>

<div class="row">
    <div class="col-12 col-md-6">
        <h3>Betriebssystem:</h3>
        <div id="chart_os"></div>

        <table class="table">
            <tr>
                <th>Name</th>
                <th>Anzahl</th>
            </tr>

            <?php

            foreach (get_data_array('name', 'pagestats_os', 'count') as $row) {
                echo '<tr>';
                echo '<td>' . $row->getValue('name') . '</td>';
                echo '<td>' . $row->getValue('count') . '</td>';
                echo '</tr>';
            }

            ?>

        </table>

    </div>
    <div class="col-12 col-md-6">

    </div>
</div>


<div class="row">
    <div class="col-12 col-md-6">
        <h3>Marke:</h3>
        <div id="chart_brand"></div>

        <table class="table">
            <tr>
                <th>Name</th>
                <th>Anzahl</th>
            </tr>

            <?php

            foreach (get_data_array('name', 'pagestats_brand', 'count') as $row) {
                echo '<tr>';
                echo '<td>' . $row->getValue('name') . '</td>';
                echo '<td>' . $row->getValue('count') . '</td>';
                echo '</tr>';
            }

            ?>

        </table>

    </div>
    <div class="col-12 col-md-6">
        <h3>Modell:</h3>
        <div id="chart_model"></div>

        <table class="table">
            <tr>
                <th>Name</th>
                <th>Anzahl</th>
            </tr>

            <?php

            foreach (get_data_array('name', 'pagestats_model', 'count') as $row) {
                echo '<tr>';
                echo '<td>' . $row->getValue('name') . '</td>';
                echo '<td>' . $row->getValue('count') . '</td>';
                echo '</tr>';
            }

            ?>

        </table>

    </div>
</div>


<h3>Bots:</h3>
<table class="table">
    <tr>
        <th>Name</th>
        <th>Ketegorie</th>
        <th>Hersteller</th>
        <th>Anzahl</th>
    </tr>

    <?php

    foreach (get_data_array('name', 'pagestats_bot', 'count') as $row) {
        echo '<tr>';
        echo '<td>' . $row->getValue('name') . '</td>';
        echo '<td>' . $row->getValue('category') . '</td>';
        echo '<td>' . $row->getValue('producer') . '</td>';
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
            r: 40,
            l: 40,
            t: 40,
            b: 70,
        },
    }


    chart_visits = Plotly.newPlot('chart_visits', [{
        type: 'line',
        x: <?php echo $sum_per_day_labels ?>,
        y: <?php echo $sum_per_day_values ?>,
    }], layout, config);

    chart_browser = Plotly.newPlot('chart_browser', [{
        type: 'pie',
        labels: <?php echo get_data('name', 'pagestats_browser', 'count') ?>,
        values: <?php echo get_data('count', 'pagestats_browser', 'count') ?>,
    }], layout, config);

    chart_browsertype = Plotly.newPlot('chart_browsertype', [{
        type: 'pie',
        labels: <?php echo get_data('name', 'pagestats_browsertype', 'count') ?>,
        values: <?php echo get_data('count', 'pagestats_browsertype', 'count') ?>,
    }], layout, config);

    chart_os = Plotly.newPlot('chart_os', [{
        type: 'pie',
        labels: <?php echo get_data('name', 'pagestats_os', 'count') ?>,
        values: <?php echo get_data('count', 'pagestats_os', 'count') ?>,
    }], layout, config);

    chart_brand = Plotly.newPlot('chart_brand', [{
        type: 'pie',
        labels: <?php echo get_data('name', 'pagestats_brand', 'count') ?>,
        values: <?php echo get_data('count', 'pagestats_brand', 'count') ?>,
    }], layout, config);

    chart_model = Plotly.newPlot('chart_model', [{
        type: 'pie',
        labels: <?php echo get_data('name', 'pagestats_model', 'count') ?>,
        values: <?php echo get_data('count', 'pagestats_model', 'count') ?>,
    }], layout, config);
</script>