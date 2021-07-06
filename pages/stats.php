<?php



$request_date_start = htmlspecialchars_decode(rex_request('date_start', 'string', ''));
$request_date_end = htmlspecialchars_decode(rex_request('date_end', 'string', ''));

$sql = rex_sql::factory();


if ($request_date_end == '' || $request_date_start == '') {
    $max_date = $sql->setQuery('SELECT MAX(date) AS "date" from ' . rex::getTable('pagestats_dump'));
    $max_date = $max_date->getValue('date');
    $max_date = new DateTime($max_date);
    $max_date->modify('+1 day');

    $min_date = $sql->setQuery('SELECT MIN(date) AS "date" from ' . rex::getTable('pagestats_dump'));
    $min_date = $min_date->getValue('date');
    $min_date = new DateTime($min_date);

    $sum_per_day = $sql->setQuery('SELECT date, COUNT(date) AS "count" from ' . rex::getTable('pagestats_dump') . ' GROUP BY date ORDER BY date ASC');
} else {

    $max_date = new DateTime($request_date_end);
    $min_date = new DateTime($request_date_start);

    if ($min_date > $max_date) {
        echo '<div class="alert alert-danger">' . $this->i18n('statistics_dates') . '</div>';
        $min_date = new DateTime();
        $max_date = new DateTime();
        $max_date->modify('+1 day');
    }

    $sum_per_day = $sql->setQuery('SELECT date, COUNT(date) AS "count" from ' . rex::getTable('pagestats_dump') . ' where date between :start and :end GROUP BY date ORDER BY date ASC', ['start' => $request_date_start, ':end' => $request_date_end]);
}


$period = new DatePeriod(
    new DateTime($min_date->format('d.m.Y')),
    new DateInterval('P1D'),
    new DateTime($max_date->format('d.m.Y'))
);

foreach ($period as $value) {
    $array[$value->format("d.m.Y")] = "0";
}



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






$browser = new stats_browser();
$browser_data = $browser->get_data();

$browsertype = new stats_browsertype();
$browsertype_data = $browsertype->get_data();

$os = new stats_os();
$os_data = $os->get_data();

$brand = new stats_brand();
$brand_data = $brand->get_data();

$model = new stats_model();
$model_data = $model->get_data();

$weekday = new stats_weekday();
$weekday_data = $weekday->get_data();

$hour = new stats_hour();
$hour_data = $hour->get_data();


?>


<script src="<?php echo rex_addon::get('statistics')->getAssetsUrl('plotly.min.js') ?>"></script>


<div class="panel panel-default">
    <div class="panel-heading"><?php echo $this->i18n('statistics_filter_date') ?></div>
    <div class="panel-body">
        <form class="form-inline" action="<?php echo rex_url::backendPage('statistics/settings') ?>" method="GET">
            <input type="hidden" value="statistics/stats" name="page">
            <div class="form-group">
                <label for="exampleInputName2"><?php echo $this->i18n('statistics_startdate') ?></label>
                <input style="line-height: normal;" type="date" value="<?php echo $request_date_start ? $request_date_start : $min_date->format('Y-m-d') ?>" class="form-control" name="date_start">
            </div>
            <div class="form-group">
                <label for="exampleInputEmail2"><?php echo $this->i18n('statistics_enddate') ?></label>
                <input style="line-height: normal;" value="<?php echo $request_date_end ? $request_date_end : $max_date->format('Y-m-d') ?>" type="date" class="form-control" name="date_end">
            </div>
            <button type="submit" class="btn btn-default"><?php echo $this->i18n('statistics_filter') ?></button>
        </form>
    </div>
</div>



<?php
if (!isset($sum_per_day_labels)) {
    echo '<div class="alert alert-danger">';
    echo '<p>' . $this->i18n('statistics_no_data') . '</p>';
    echo '</div>';
}


$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('statistics_views_per_day'));
$fragment->setVar('content', '<div id="chart_visits"></div>', false);
echo $fragment->parse('core/page/section.php');
?>


<div class="row">
    <div class="col-12 col-md-6">
        <?php

        $fragment = new rex_fragment();
        $fragment->setVar('title', $this->i18n('statistics_browser'));
        $fragment->setVar('content', '<div id="chart_browser"></div>' . $browser->get_list(), false);
        echo $fragment->parse('core/page/section.php');

        ?>

    </div>
    <div class="col-12 col-md-6">
        <?php

        $fragment = new rex_fragment();
        $fragment->setVar('title', $this->i18n('statistics_devicetype'));
        $fragment->setVar('content', '<div id="chart_browsertype"></div>' . $browsertype->get_list(), false);
        echo $fragment->parse('core/page/section.php');

        ?>

    </div>
</div>

<div class="row">
    <div class="col-12 col-md-6">
        <?php

        $fragment = new rex_fragment();
        $fragment->setVar('title', $this->i18n('statistics_os'));
        $fragment->setVar('content', '<div id="chart_os"></div>' . $os->get_list(), false);
        echo $fragment->parse('core/page/section.php');

        ?>

    </div>
    <div class="col-12 col-md-6">

    </div>
</div>


<div class="row">
    <div class="col-12 col-md-6">
        <?php

        $fragment = new rex_fragment();
        $fragment->setVar('title', $this->i18n('statistics_brand'));
        $fragment->setVar('content', '<div id="chart_brand"></div>' . $brand->get_list(), false);
        echo $fragment->parse('core/page/section.php');

        ?>

    </div>
    <div class="col-12 col-md-6">
        <?php

        $fragment = new rex_fragment();
        $fragment->setVar('title', $this->i18n('statistics_model'));
        $fragment->setVar('content', '<div id="chart_model"></div>' . $model->get_list(), false);
        echo $fragment->parse('core/page/section.php');

        ?>

    </div>
    <div class="col-12 col-md-6">
        <?php

        $fragment = new rex_fragment();
        $fragment->setVar('title', $this->i18n('statistics_days'));
        $fragment->setVar('content', '<div id="chart_weekday"></div>' . $weekday->get_list(), false);
        echo $fragment->parse('core/page/section.php');

        ?>

    </div>
    <div class="col-12 col-md-6">
        <?php

        $fragment = new rex_fragment();
        $fragment->setVar('title', $this->i18n('statistics_hours'));
        $fragment->setVar('content', '<div id="chart_hour"></div>' . $hour->get_list(), false);
        echo $fragment->parse('core/page/section.php');

        ?>

    </div>
</div>


<?php

$list = rex_list::factory('SELECT * FROM ' . rex::getTable('pagestats_bot') . ' ORDER BY count DESC');
$list->setColumnLabel('name', $this->i18n('statistics_name'));
$list->setColumnLabel('count', $this->i18n('statistics_count'));
$list->setColumnLabel('category', $this->i18n('statistics_category'));
$list->setColumnLabel('producer', $this->i18n('statistics_producer'));
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
        labels: <?php echo $browser_data['labels'] ?>,
        values: <?php echo $browser_data['values'] ?>,
    }], layout, config);

    chart_browsertype = Plotly.newPlot('chart_browsertype', [{
        type: 'pie',
        labels: <?php echo $browsertype_data['labels'] ?>,
        values: <?php echo $browsertype_data['values'] ?>,
    }], layout, config);

    chart_os = Plotly.newPlot('chart_os', [{
        type: 'pie',
        labels: <?php echo $os_data['labels'] ?>,
        values: <?php echo $os_data['values'] ?>,
    }], layout, config);

    chart_brand = Plotly.newPlot('chart_brand', [{
        type: 'pie',
        labels: <?php echo $brand_data['labels'] ?>,
        values: <?php echo $brand_data['values'] ?>,
    }], layout, config);

    chart_model = Plotly.newPlot('chart_model', [{
        type: 'pie',
        labels: <?php echo $model_data['labels'] ?>,
        values: <?php echo $model_data['values'] ?>,
    }], layout, config);

    chart_weekday = Plotly.newPlot('chart_weekday', [{
        type: 'bar',
        x: <?php echo $weekday_data['labels'] ?>,
        y: <?php echo $weekday_data['values'] ?>,
    }], layout, config);

    chart_hour = Plotly.newPlot('chart_hour', [{
        type: 'bar',
        x: <?php echo $hour_data['labels'] ?>,
        y: <?php echo $hour_data['values'] ?>,
    }], layout, config);
</script>