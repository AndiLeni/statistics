<?php

$addon = rex_addon::get('statistics');

$request_url = rex_request('url', 'string', '');
$request_url = htmlspecialchars_decode($request_url);
$ignore_page = rex_request('ignore_page', 'boolean', false);
$search_string = htmlspecialchars_decode(rex_request('search_string', 'string', ''));

// sum per page, bar chart
$sql = rex_sql::factory();

if ($search_string == '') {
    $sum_per_page = $sql->setQuery('SELECT url, COUNT(url) AS "count" from ' . rex::getTable('pagestats_dump') . ' GROUP BY url ORDER BY count DESC');
} else {
    $sum_per_page = $sql->setQuery('SELECT url, COUNT(url) as "count" from ' . rex::getTable('pagestats_dump') . ' WHERE url LIKE :url GROUP BY url ORDER BY count DESC', ['url' => '%' . $search_string . '%']);
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
$form = '
<form class="form-inline" action="' . rex_url::backendPage('statistics/pages') . '" method="GET">
    <input type="hidden" value="statistics/pages" name="page">
    <div class="form-group">
        <label for="exampleInputName2">Suchen nach:</label>
        <input style="line-height: normal;" type="text" value="' . $search_string . '" class="form-control" name="search_string">
    </div>
    <button type="submit" class="btn btn-default">Suchen</button>
</form>
';

$fragment = new rex_fragment();
$fragment->setVar('title', 'Aufrufe pro Tag:');
$fragment->setVar('body', $form, false);
echo $fragment->parse('core/page/section.php');



// check if request is for ignoring a url
// if yes, add url to addon settings and delete all database entries of this url 
if ($request_url != '' && $ignore_page === true) {
    $ignored_paths = $addon->getConfig('pagestats_ignored_paths');
    $addon->setConfig('pagestats_ignored_paths', $ignored_paths . PHP_EOL . $request_url);

    $sql = rex_sql::factory();
    $sql->setQuery('delete from ' . rex::getTable('pagestats_dump') . ' where url = :url', ['url' => $request_url]);
    echo '<div class="alert alert-success">Es wurden ' . $sql->getRows() . ' Einträge gelöscht. Die Url <code>' . $request_url . '</code> wird zukünftig ignoriert.</div>';
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
    $fragment->setVar('body', '<h4>Aufrufe insgesamt: <b>' . $pagedetails->get_page_total() . '</b></h4>', false);
    $fragment->setVar('content', $content, false);
    echo $fragment->parse('core/page/section.php');
}


if ($search_string == '') {
    $list = rex_list::factory('SELECT url, COUNT(url) AS "count" from ' . rex::getTable('pagestats_dump') . ' GROUP BY url ORDER BY count DESC');
} else {
    $list = rex_list::factory('SELECT url, COUNT(url) as "count" from ' . rex::getTable('pagestats_dump') . ' WHERE url LIKE "%' . $search_string . '%" GROUP BY url ORDER BY count DESC');
}


$list->setColumnLabel('url', 'Url');
$list->setColumnLabel('count', 'Anzahl');
$list->setColumnParams('url', ['url' => '###url###']);

$list->addColumn('edit', 'Ignorieren & löschen');
$list->setColumnLabel('edit', 'Diese URL in Ignorier-Liste verschieben');
$list->addLinkAttribute('edit', 'data-confirm', 'Dieser Eintrag wird aus der Datenbank gelöscht und zukünftig ignoriert. Sind Sie sicher?');
$list->setColumnParams('edit', ['url' => '###url###', 'ignore_page' => true]);


$fragment = new rex_fragment();
$fragment->setVar('title', 'Summe pro Seite:');
$fragment->setVar('content', '<div id="chart_visits_per_page"></div>' . $list->get(), false);
echo $fragment->parse('core/page/section.php');

?>

<script src="<?php echo rex_addon::get('statistics')->getAssetsUrl('plotly-2.0.0.min.js') ?>"></script>

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
</script>