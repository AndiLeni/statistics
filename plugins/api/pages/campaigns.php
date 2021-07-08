<?php

$search_string = htmlspecialchars_decode(rex_request('search_string', 'string', ''));
$request_name = rex_request('name', 'string', '');
$request_name = htmlspecialchars_decode($request_name);
$delete_entry = rex_request('delete_entry', 'boolean', false);


if ($request_name != '' && $delete_entry === true) {
    $sql = rex_sql::factory();
    $sql->setQuery('delete from ' . rex::getTable('pagestats_api') . ' where name = :name', ['name' => $request_name]);
    echo rex_view::success('Es wurden ' . $sql->getRows() . ' Einträge der Kampagne <code>' . $request_name . '</code> gelöscht.');
}

// details section
if ($request_name != '' && !$delete_entry) {
    // details section for single campaign

    $pagedetails = new stats_campaign_details($request_name);
    $sum_data = $pagedetails->get_sum_per_day();


    $content = '<div id="chart_details"></div>';

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'info', false);
    $fragment->setVar('title', 'Details für:');
    $fragment->setVar('heading', $request_name);
    $fragment->setVar('body', '<h4>' . $this->i18n('statistics_views_total') . ' <b>' . $pagedetails->get_page_total() . '</b></h4>', false);
    $fragment->setVar('content', $content, false);
    echo $fragment->parse('core/page/section.php');
}


if ($search_string == '') {
    $list = rex_list::factory('SELECT name, sum(count) as "count" from ' . rex::getTable('pagestats_api') . ' GROUP BY name ORDER BY count DESC');
} else {
    $list = rex_list::factory('SELECT name, sum(count) as "count" from ' . rex::getTable('pagestats_api') . ' WHERE name LIKE "%' . $search_string . '%" GROUP BY name ORDER BY count DESC');
}


$form = '
<form class="form-inline" action="' . rex_url::currentBackendPage() . '" method="GET">
    <input type="hidden" value="statistics/api/campaigns" name="page">
    <div class="form-group">
        <label for="exampleInputName2">' . $this->i18n('statistics_api_search_for') . '</label>
        <input style="line-height: normal;" type="text" value="' . $search_string . '" class="form-control" name="search_string">
    </div>
    <button type="submit" class="btn btn-default">' . $this->i18n('statistics_api_search') . '</button>
</form>
';


$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('statistics_api_filter'));
$fragment->setVar('body', $form, false);
echo $fragment->parse('core/page/section.php');


$list->setColumnLabel('name', $this->i18n('statistics_api_name'));
$list->setColumnLabel('count', $this->i18n('statistics_api_count'));
$list->setColumnSortable('name', $direction = 'asc');
$list->setColumnSortable('count', $direction = 'asc');
$list->setColumnParams('name', ['name' => '###name###']);

$list->addColumn('edit', $this->i18n('statistics_api_delete'));
$list->setColumnLabel('edit', $this->i18n('statistics_api_delete'));
$list->addLinkAttribute('edit', 'data-confirm', $this->i18n('statistics_api_delete_confirm'));
$list->setColumnParams('edit', ['name' => '###name###', 'delete_entry' => true]);

$fragment2 = new rex_fragment();
$fragment2->setVar('title', $this->i18n('statistics_api_campaign_views'));
$fragment2->setVar('content', $list->get(), false);
echo $fragment2->parse('core/page/section.php');

?>

<script src="<?php echo rex_addon::get('statistics')->getAssetsUrl('plotly.min.js') ?>"></script>

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


    <?php

    if ($request_name != '' && !$delete_entry) {
        echo 'chart_details = Plotly.newPlot("chart_details", [{
            type: "line",
            x:' . $sum_data['labels'] . ',
            y:' . $sum_data['values'] . ',
        }], layout, config);';
    }


    ?>
</script>