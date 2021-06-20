<?php

function get_labels($column, $request_url) {
    $sql = rex_sql::factory();
    $result = $sql->setQuery('SELECT '. $column .' FROM ' . rex::getTable('pagestats_dump') . ' WHERE url = :url GROUP BY '. $column .' ORDER BY '. $column .' ASC', ['url' => $request_url]);

    foreach ($result as $row) {
        $data[] = $row->getValue($column);
    }

    return json_encode($data);
}

function get_values($column, $request_url) {
    $sql = rex_sql::factory();
    $result = $sql->setQuery('SELECT COUNT('. $column .') as "count" FROM ' . rex::getTable('pagestats_dump') . ' WHERE url = :url GROUP BY '. $column .' ORDER BY '. $column .' ASC', ['url' => $request_url]);

    foreach ($result as $row) {
        $data[] = $row->getValue('count');
    }

    return json_encode($data);
}


// sum per page
$sql = rex_sql::factory();
$sum_per_page = $sql->setQuery('SELECT url, COUNT(url) AS "count" from ' . rex::getTable('pagestats_dump') . ' GROUP BY url ORDER BY url ASC');

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


// details section for single page
if ($request_url != []) {

    $request_url = rex_escape($request_url[0]);


    $sql = rex_sql::factory();
    $max_date = $sql->setQuery('SELECT MAX(date) AS "date" from ' . rex::getTable('pagestats_dump') . ' WHERE url = :url', ['url' => $request_url]);
    $max_date = $max_date->getValue('date');
    $max_date = new DateTime($max_date);
    $max_date->modify('+1 day');
    $max_date = $max_date->format('d.m.Y');

    $min_date = $sql->setQuery('SELECT MIN(date) AS "date" from ' . rex::getTable('pagestats_dump') . ' WHERE url = :url', ['url' => $request_url]);
    $min_date = $min_date->getValue('date');

    $period = new DatePeriod(
        new DateTime($min_date),
        new DateInterval('P1D'),
        new DateTime($max_date)
    );

    foreach ($period as $value) {
        $array[$value->format("d.m.Y")] = "0";
    }

    $sum_per_day = $sql->setQuery('SELECT date, COUNT(date) AS "count" from ' . rex::getTable('pagestats_dump') . ' WHERE url = :url GROUP BY date ORDER BY date ASC', ['url' => $request_url]);

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


    $list = rex_list::factory('SELECT date, COUNT(date) as "count" FROM ' . rex::getTable('pagestats_dump') . ' WHERE url = "' . $request_url . '" GROUP BY date ORDER BY url DESC');
    $list->setColumnLabel('date', 'Datum');
    $list->setColumnLabel('count', 'Anzahl');
    $list->setColumnSortable('date', $direction = 'desc');
    $list->setColumnSortable('count', $direction = 'desc');
    $list->setColumnParams('url', ['url' => '###url###']);


    $details_page_total = rex_sql::factory();
    $details_page_total->setQuery('SELECT COUNT(url) as "count" FROM ' . rex::getTable('pagestats_dump') . ' WHERE url = :url', ['url' => $request_url]);
    $details_page_total = $details_page_total->getValue('count');


    echo '<div class="panel panel-edit">';
    echo '<header class="panel-heading">';
    echo '<div class="panel-title">Details für:</div>' . $request_url;
    echo '</header>';

    echo '<div class="panel-body">';
    echo '<h5>Aufrufe insgesamt: <b>' . $details_page_total . '</b></h5>';
    echo '<div class="row">';
    echo '<div class="col-md-4">';
    echo '<div class="panel panel-default">
            <div class="panel-body">
                <div id="chart_details_devicetype"></div>
            </div>
        </div>';
    echo '</div>';
    echo '<div class="col-md-4">';
    echo '<div class="panel panel-default">
            <div class="panel-body">
                <div id="chart_details_browser"></div>
            </div>
        </div>';
    echo '</div>';
    echo '<div class="col-md-4">';
    echo '<div class="panel panel-default">
            <div class="panel-body">
                <div id="chart_details_os"></div>
            </div>
        </div>';
    echo '</div>';
    echo '</div>';
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

$list = rex_list::factory('SELECT url, COUNT(url) AS "count" from ' . rex::getTable('pagestats_dump') . ' GROUP BY url ORDER BY url ASC');
$list->setColumnLabel('url', 'Url');
$list->setColumnLabel('count', 'Anzahl');
// $list->setColumnSortable('url', $direction = 'asc'); needs fix, "url" url-param not set when reorderung
// $list->setColumnSortable('count', $direction = 'asc'); needs fix, "url" url-param not set when reorderung
$list->setColumnParams('url', ['url' => '###url###']);
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
            x:' . $sum_per_day_labels . ',
            y:' . $sum_per_day_values . ',
        }], layout, config);';

        echo 'chart_details_devicetype = Plotly.newPlot("chart_details_devicetype", [{
            type: "pie",
            labels:' . get_labels('browsertype', $request_url) . ',
            values:' . get_values('browsertype', $request_url) . ',
        }], layout, config);';

        echo 'chart_details_browser = Plotly.newPlot("chart_details_browser", [{
            type: "pie",
            labels:' . get_labels('browser', $request_url) . ',
            values:' . get_values('browser', $request_url) . ',
        }], layout, config);';

        echo 'chart_details_os = Plotly.newPlot("chart_details_os", [{
            type: "pie",
            labels:' . get_labels('os', $request_url) . ',
            values:' . get_values('os', $request_url) . ',
        }], layout, config);';

        
    }


    ?>
</script>