<?php


function get_labels($column)
{
    $sql = rex_sql::factory();
    $result = $sql->setQuery('SELECT ' . $column . ' FROM ' . rex::getTable('pagestats_dump') . ' GROUP BY ' . $column . ' ORDER BY ' . $column . ' ASC');

    foreach ($result as $row) {
        $data[] = $row->getValue($column);
    }

    return json_encode($data);
}

function get_values($column)
{
    $sql = rex_sql::factory();
    $result = $sql->setQuery('SELECT COUNT(' . $column . ') as "count" FROM ' . rex::getTable('pagestats_dump') . ' GROUP BY ' . $column . ' ORDER BY ' . $column . ' ASC');

    foreach ($result as $row) {
        $data[] = $row->getValue('count');
    }

    return json_encode($data);
}

function get_values_hour()
{
    $sql = rex_sql::factory();
    $result = $sql->setQuery('SELECT hour, COUNT(hour) as "count" FROM ' . rex::getTable('pagestats_dump') . ' GROUP BY hour ORDER BY hour ASC');

    $data = [0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0, 11 => 0, 12 => 0, 13 => 0, 14 => 0, 15 => 0, 16 => 0, 17 => 0, 18 => 0, 19 => 0, 20 => 0, 21 => 0, 22 => 0, 23 => 0];

    foreach ($result as $row) {
        $data[$row->getValue('hour')] = $row->getValue('count');
    }

    return json_encode(array_values($data));
}

function get_values_weekday($column)
{
    $sql = rex_sql::factory();
    $result = $sql->setQuery('SELECT weekday, COUNT(' . $column . ') as "count" FROM ' . rex::getTable('pagestats_dump') . ' GROUP BY ' . $column . ' ORDER BY ' . $column . ' ASC');

    $data = ["1" => 0, "2" => 0, "3" => 0, "4" => 0, "5" => 0, "6" => 0, "7" => 0];

    foreach ($result as $row) {
        $data[$row->getValue('weekday')] = $row->getValue('count');
    }

    return json_encode(array_values($data));
}

function get_weekday_string($weekday)
{
    switch ($weekday["value"]) {
        case 1:
            return "Montag";
            break;
        case 2:
            return "Dienstag";
            break;
        case 3:
            return "Mittwoch";
            break;
        case 4:
            return "Donnerstag";
            break;
        case 5:
            return "Freitag";
            break;
        case 6:
            return "Samstag";
            break;
        case 7:
            return "Sonntag";
            break;
    }
}




$request_date_start = rex_escape(rex_request('date_start', 'string', ''));
$request_date_end = rex_escape(rex_request('date_end', 'string', ''));

$sql = rex_sql::factory();

if ($request_date_end == '' || $request_date_start == '') {
    $max_date = $sql->setQuery('SELECT MAX(date) AS "date" from ' . rex::getTable('pagestats_dump'));
    $max_date = $max_date->getValue('date');
    $max_date = new DateTime($max_date);
    $max_date->modify('+1 day');
    $max_date = $max_date->format('d.m.Y');


    $min_date = $sql->setQuery('SELECT MIN(date) AS "date" from ' . rex::getTable('pagestats_dump'));
    $min_date = $min_date->getValue('date');
    $min_date = new DateTime($min_date);
    $min_date = $min_date->format('d.m.Y');
} else {
    $max_date = new DateTime($request_date_end);
    $max_date = $max_date->format('d.m.Y');
    $min_date = new DateTime($request_date_start);
    $min_date = $min_date->format('d.m.Y');
}



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


<div class="panel panel-default">
    <div class="panel-heading">Zeitraum filtern</div>
    <div class="panel-body">
        <form class="form-inline" action="http://redaxo.test/redaxo/index.php?page=stats/stats" method="GET">
            <input type="hidden" value="stats/stats" name="page">
            <div class="form-group">
                <label for="exampleInputName2">Startdatum:</label>
                <input style="line-height: normal;" type="date" class="form-control" name="date_start">
            </div>
            <div class="form-group">
                <label for="exampleInputEmail2">Enddatum:</label>
                <input style="line-height: normal;" value="<?php echo date('Y-m-d') ?>" type="date" class="form-control" name="date_end">
            </div>
            <button type="submit" class="btn btn-default">Filtern</button>
        </form>
    </div>
</div>



<?php
if (!isset($sum_per_day_labels)) {
    echo '<div class="alert alert-danger">';
    echo '<p>Es sind noch keine Daten vorhanden.</p>';
    echo '</div>';
}

$fragment = new rex_fragment();
$fragment->setVar('title', 'Aufrufe pro Tag:');
$fragment->setVar('content', '<div id="chart_visits"></div>', false);
echo $fragment->parse('core/page/section.php');
?>


<div class="row">
    <div class="col-12 col-md-6">
        <?php

        $list = rex_list::factory('SELECT browser, COUNT(browser) as "count" FROM ' . rex::getTable('pagestats_dump') . ' GROUP BY browser ORDER BY count DESC');
        $list->setColumnLabel('browser', 'Name');
        $list->setColumnLabel('count', 'Anzahl');
        $list->setColumnSortable('browser', $direction = 'asc');
        $list->setColumnSortable('count', $direction = 'asc');

        $fragment = new rex_fragment();
        $fragment->setVar('title', 'Browser:');
        $fragment->setVar('content', '<div id="chart_browser"></div>' . $list->get(), false);
        echo $fragment->parse('core/page/section.php');

        ?>

    </div>
    <div class="col-12 col-md-6">
        <?php

        $list = rex_list::factory('SELECT browsertype, COUNT(browsertype) as "count" FROM ' . rex::getTable('pagestats_dump') . ' GROUP BY browsertype ORDER BY count DESC');
        $list->setColumnLabel('browsertype', 'Name');
        $list->setColumnLabel('count', 'Anzahl');
        $list->setColumnSortable('browsertype', $direction = 'asc');
        $list->setColumnSortable('count', $direction = 'desc');

        $fragment = new rex_fragment();
        $fragment->setVar('title', 'Gerätetyp:');
        $fragment->setVar('content', '<div id="chart_browsertype"></div>' . $list->get(), false);
        echo $fragment->parse('core/page/section.php');
        ?>

    </div>
</div>

<div class="row">
    <div class="col-12 col-md-6">
        <?php

        $list = rex_list::factory('SELECT os, COUNT(os) as "count" FROM ' . rex::getTable('pagestats_dump') . ' GROUP BY os ORDER BY count DESC');
        $list->setColumnLabel('os', 'Name');
        $list->setColumnLabel('count', 'Anzahl');
        $list->setColumnSortable('os', $direction = 'asc');
        $list->setColumnSortable('count', $direction = 'asc');

        $fragment = new rex_fragment();
        $fragment->setVar('title', 'Betriebssystem:');
        $fragment->setVar('content', '<div id="chart_os"></div>' . $list->get(), false);
        echo $fragment->parse('core/page/section.php');
        ?>

    </div>
    <div class="col-12 col-md-6">

    </div>
</div>


<div class="row">
    <div class="col-12 col-md-6">
        <?php

        $list = rex_list::factory('SELECT brand, COUNT(brand) as "count" FROM ' . rex::getTable('pagestats_dump') . ' GROUP BY brand ORDER BY count DESC');
        $list->setColumnLabel('brand', 'Name');
        $list->setColumnLabel('count', 'Anzahl');
        $list->setColumnSortable('brand', $direction = 'asc');
        $list->setColumnSortable('count', $direction = 'asc');

        $fragment = new rex_fragment();
        $fragment->setVar('title', 'Marke:');
        $fragment->setVar('content', '<div id="chart_brand"></div>' . $list->get(), false);
        echo $fragment->parse('core/page/section.php');
        ?>

    </div>
    <div class="col-12 col-md-6">
        <?php

        $list = rex_list::factory('SELECT model, COUNT(model) as "count" FROM ' . rex::getTable('pagestats_dump') . ' GROUP BY model ORDER BY count DESC');
        $list->setColumnLabel('model', 'Name');
        $list->setColumnLabel('count', 'Anzahl');
        $list->setColumnSortable('model', $direction = 'asc');
        $list->setColumnSortable('count', $direction = 'asc');

        $fragment = new rex_fragment();
        $fragment->setVar('title', 'Modell:');
        $fragment->setVar('content', '<div id="chart_model"></div>' . $list->get(), false);
        echo $fragment->parse('core/page/section.php');
        ?>

    </div>
    <div class="col-12 col-md-6">
        <?php

        $list = rex_list::factory('SELECT weekday, COUNT(weekday) as "count" FROM ' . rex::getTable('pagestats_dump') . ' GROUP BY weekday ORDER BY count DESC');
        $list->setColumnLabel('weekday', 'Name');
        $list->setColumnLabel('count', 'Anzahl');
        $list->setColumnSortable('weekday', $direction = 'asc');
        $list->setColumnSortable('count', $direction = 'asc');
        $list->setColumnFormat('weekday', 'custom', 'get_weekday_string');

        $fragment = new rex_fragment();
        $fragment->setVar('title', 'Wochentage:');
        $fragment->setVar('content', '<div id="chart_weekday"></div>' . $list->get(), false);
        echo $fragment->parse('core/page/section.php');
        ?>

    </div>
    <div class="col-12 col-md-6">
        <?php

        $list = rex_list::factory('SELECT hour, COUNT(hour) as "count" FROM ' . rex::getTable('pagestats_dump') . ' GROUP BY hour ORDER BY count DESC');
        $list->setColumnLabel('hour', 'Name');
        $list->setColumnLabel('count', 'Anzahl');
        $list->setColumnSortable('hour', $direction = 'asc');
        $list->setColumnSortable('count', $direction = 'asc');
        $list->setColumnFormat('hour', 'sprintf', '###hour### Uhr');

        $fragment = new rex_fragment();
        $fragment->setVar('title', 'Uhrzeiten:');
        $fragment->setVar('content', '<div id="chart_hour"></div>' . $list->get(), false);
        echo $fragment->parse('core/page/section.php');

        ?>

    </div>
</div>


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

$fragment = new rex_fragment();
$fragment->setVar('title', 'Bots:');
$fragment->setVar('content', '<div id="chart_hour"></div>' . $list->get(), false);
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
    }
    var layout = {
        margin: {
            r: 40,
            l: 40,
            t: 40,
            b: 90,
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

    chart_weekday = Plotly.newPlot('chart_weekday', [{
        type: 'bar',
        x: ["Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag", "Sonntag"],
        y: <?php echo get_values_weekday('weekday') ?>,
    }], layout, config);

    chart_hour = Plotly.newPlot('chart_hour', [{
        type: 'bar',
        x: ["00", "01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21", "22", "23"],
        y: <?php echo get_values_hour('hour') ?>,
    }], layout, config);
</script>