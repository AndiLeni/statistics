<?php

// function get_data_as_string_array($column, $table, $order)
// {
//     $sql = rex_sql::factory();
//     $result = $sql->setQuery('SELECT ' . $column . ' FROM ' . rex::getTable($table) . ' ORDER BY ' . $order . ' ASC');

//     foreach ($result as $row) {
//         $data[] = $row->getValue($column);
//     }

//     return json_encode($data);
// }

// function get_data_as_sql($table, $order)
// {
//     $sql = rex_sql::factory();
//     $result = $sql->setQuery('SELECT * FROM ' . rex::getTable($table) . ' ORDER BY ' . $order . ' DESC');

//     return $result;
// }

function get_labels($column) {
    $sql = rex_sql::factory();
    $result = $sql->setQuery('SELECT '. $column .' FROM ' . rex::getTable('pagestats_dump') . ' GROUP BY '. $column .' ORDER BY '. $column .' ASC');

    foreach ($result as $row) {
        $data[] = $row->getValue($column);
    }

    return json_encode($data);
}

function get_values($column) {
    $sql = rex_sql::factory();
    $result = $sql->setQuery('SELECT COUNT('. $column .') as "count" FROM ' . rex::getTable('pagestats_dump') . ' GROUP BY '. $column .' ORDER BY '. $column .' ASC');

    foreach ($result as $row) {
        $data[] = $row->getValue('count');
    }

    return json_encode($data);
}

// $processed = [
//     'browser' => [],
//     'os' => [],
//     'browsertype' => [],
//     'brand' => [],
//     'model' => [],
// ];

// // process dump table
// $sql = rex_sql::factory();
// $sql->setDebug(true);
// $sql->setTable(rex::getTable('pagestats_dump'));
// $result = $sql->select();

// foreach ($result as $row) {
//     $b = $row->getValue('browser');
//     $processed['browser'][$b] += 1;

//     $b = $row->getValue('os');
//     $processed['os'][$b] += 1;

//     $b = $row->getValue('browsertype');
//     $processed['browsertype'][$b] += 1;

//     $b = $row->getValue('brand');
//     $processed['brand'][$b] += 1;

//     $b = $row->getValue('model');
//     $processed['model'][$b] += 1;
// }


// dump($processed);





$sql = rex_sql::factory();
// $sql->setDebug(true);
$max_date = $sql->setQuery('SELECT MAX(date) AS "date" from ' . rex::getTable('pagestats_dump'));
$max_date = $max_date->getValue('date');
$max_date = new DateTime($max_date);
$max_date->modify('+1 day');
$max_date = $max_date->format('d.m.Y');


$min_date = $sql->setQuery('SELECT MIN(date) AS "date" from ' . rex::getTable('pagestats_dump'));
$min_date = $min_date->getValue('date');
$min_date = new DateTime($min_date);
$min_date = $min_date->format('d.m.Y');

$period = new DatePeriod(
    new DateTime($min_date),
    new DateInterval('P1D'),
    new DateTime($max_date)
);

foreach ($period as $value) {
    $array[$value->format("d.m.Y")] = "0";
}

$sum_per_day = $sql->setQuery('SELECT date, COUNT(date) AS "count" from ' . rex::getTable('pagestats_dump') . ' GROUP BY date ORDER BY date ASC');

$data = [];

if ($sum_per_day->getRows() != 0) {
    foreach ($sum_per_day as $row) {
        $date = DateTime::createFromFormat('Y-m-d', $row->getValue('date'))->format('d.m.Y');
        $arr2[$date] = $row->getValue('count');
    }

    $data = array_merge($array, $arr2);
}

$sum_per_day_labels = json_encode(array_keys($data));
$sum_per_day_values = json_encode(array_values($data));


?>

<!-- html begin -->


<script src="<?php echo rex_addon::get('stats')->getAssetsUrl('plotly-2.0.0.min.js') ?>"></script>



<h3>Aufrufe pro Tag:</h3>
<?php
if (!isset($sum_per_day_labels)) {
    echo '<div class="alert alert-danger">';
    echo '<p>Es sind noch keine Daten vorhanden.</p>';
    echo '</div>';
}
?>
<div id="chart_visits"></div>


<div class="row">
    <div class="col-12 col-md-6">
        <h3>Browser:</h3>
        <div id="chart_browser"></div>

        <?php

        $list = rex_list::factory('SELECT browser, COUNT(browser) as "count" FROM ' . rex::getTable('pagestats_dump') . ' GROUP BY browser ORDER BY count DESC');
        $list->setColumnLabel('browser', 'Name');
        $list->setColumnLabel('count', 'Anzahl');
        $list->setColumnSortable('browser', $direction = 'asc');
        $list->setColumnSortable('count', $direction = 'asc');
        $list->show();

        // $list = rex_list::factory('SELECT * FROM ' . rex::getTable('pagestats_browser') . ' ORDER BY count ASC');
        // $list->setColumnLabel('name', 'Name');
        // $list->setColumnLabel('count', 'Anzahl');
        // $list->setColumnSortable('name', $direction = 'asc');
        // $list->setColumnSortable('count', $direction = 'asc');
        // $list->show();

        ?>

    </div>
    <div class="col-12 col-md-6">
        <h3>Gerätetyp:</h3>
        <div id="chart_browsertype"></div>

        <?php

        $list = rex_list::factory('SELECT browsertype, COUNT(browsertype) as "count" FROM ' . rex::getTable('pagestats_dump') . ' GROUP BY browsertype ORDER BY count DESC');
        $list->setColumnLabel('browsertype', 'Name');
        $list->setColumnLabel('count', 'Anzahl');
        $list->setColumnSortable('browsertype', $direction = 'asc');
        $list->setColumnSortable('count', $direction = 'desc');
        $list->show();

        // $list = rex_list::factory('SELECT * FROM ' . rex::getTable('pagestats_browsertype') . ' ORDER BY count ASC');
        // $list->setColumnLabel('name', 'Name');
        // $list->setColumnLabel('count', 'Anzahl');
        // $list->setColumnSortable('name', $direction = 'asc');
        // $list->setColumnSortable('count', $direction = 'asc');
        // $list->show();

        ?>

    </div>
</div>

<div class="row">
    <div class="col-12 col-md-6">
        <h3>Betriebssystem:</h3>
        <div id="chart_os"></div>

        <?php

        $list = rex_list::factory('SELECT os, COUNT(os) as "count" FROM ' . rex::getTable('pagestats_dump') . ' GROUP BY os ORDER BY count DESC');
        $list->setColumnLabel('os', 'Name');
        $list->setColumnLabel('count', 'Anzahl');
        $list->setColumnSortable('os', $direction = 'asc');
        $list->setColumnSortable('count', $direction = 'asc');
        $list->show();

        // $list = rex_list::factory('SELECT * FROM ' . rex::getTable('pagestats_os') . ' ORDER BY count ASC');
        // $list->setColumnLabel('name', 'Name');
        // $list->setColumnLabel('count', 'Anzahl');
        // $list->setColumnSortable('name', $direction = 'asc');
        // $list->setColumnSortable('count', $direction = 'asc');
        // $list->show();

        ?>

    </div>
    <div class="col-12 col-md-6">

    </div>
</div>


<div class="row">
    <div class="col-12 col-md-6">
        <h3>Marke:</h3>
        <div id="chart_brand"></div>

        <?php

        $list = rex_list::factory('SELECT brand, COUNT(brand) as "count" FROM ' . rex::getTable('pagestats_dump') . ' GROUP BY brand ORDER BY count DESC');
        $list->setColumnLabel('brand', 'Name');
        $list->setColumnLabel('count', 'Anzahl');
        $list->setColumnSortable('brand', $direction = 'asc');
        $list->setColumnSortable('count', $direction = 'asc');
        $list->show();

        // $list = rex_list::factory('SELECT * FROM ' . rex::getTable('pagestats_brand') . ' ORDER BY count ASC');
        // $list->setColumnLabel('name', 'Name');
        // $list->setColumnLabel('count', 'Anzahl');
        // $list->setColumnSortable('name', $direction = 'asc');
        // $list->setColumnSortable('count', $direction = 'asc');
        // $list->show();

        ?>

    </div>
    <div class="col-12 col-md-6">
        <h3>Modell:</h3>
        <div id="chart_model"></div>

        <?php

        $list = rex_list::factory('SELECT model, COUNT(model) as "count" FROM ' . rex::getTable('pagestats_dump') . ' GROUP BY model ORDER BY count DESC');
        $list->setColumnLabel('model', 'Name');
        $list->setColumnLabel('count', 'Anzahl');
        $list->setColumnSortable('model', $direction = 'asc');
        $list->setColumnSortable('count', $direction = 'asc');
        $list->show();

        // $list = rex_list::factory('SELECT * FROM ' . rex::getTable('pagestats_model') . ' ORDER BY count ASC');
        // $list->setColumnLabel('name', 'Name');
        // $list->setColumnLabel('count', 'Anzahl');
        // $list->setColumnSortable('name', $direction = 'asc');
        // $list->setColumnSortable('count', $direction = 'asc');
        // $list->show();

        ?>

    </div>
</div>


<h3>Bots:</h3>
<?php

$list = rex_list::factory('SELECT * FROM ' . rex::getTable('pagestats_bot') . ' ORDER BY count DESC');
$list->setColumnLabel('name', 'Name');
$list->setColumnLabel('count', 'Anzahl');
$list->setColumnLabel('category', 'Kategorie');
$list->setColumnLabel('producer', 'Hersteller');
$list->setColumnSortable('name', $direction = 'asc');
$list->setColumnSortable('count', $direction = 'asc');
$list->setColumnSortable('category', $direction = 'asc');
$list->setColumnSortable('producer', $direction = 'asc');
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
        labels: <?php echo get_labels('browser') ?>,
        values: <?php echo get_values('browser') ?>,
    }], layout, config);

    chart_browsertype = Plotly.newPlot('chart_browsertype', [{
        type: 'pie',
        labels: <?php echo get_labels('browsertype') ?>,
        values: <?php echo get_values('browsertype') ?>,
    }], layout, config);

    chart_os = Plotly.newPlot('chart_os', [{
        type: 'pie',
        labels: <?php echo get_labels('os') ?>,
        values: <?php echo get_values('os') ?>,
    }], layout, config);

    chart_brand = Plotly.newPlot('chart_brand', [{
        type: 'pie',
        labels: <?php echo get_labels('brand') ?>,
        values: <?php echo get_values('brand') ?>,
    }], layout, config);

    chart_model = Plotly.newPlot('chart_model', [{
        type: 'pie',
        labels: <?php echo get_labels('model') ?>,
        values: <?php echo get_values('model') ?>,
    }], layout, config);
</script>