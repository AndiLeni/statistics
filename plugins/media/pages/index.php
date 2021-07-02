<?php

$search_string = rex_escape(rex_request('search_string', 'string', ''));

if ($search_string == '') {
    $list = rex_list::factory('SELECT url, sum(count) as "count" from ' . rex::getTable('pagestats_media') . ' GROUP BY url ORDER BY count DESC');
} else {
    $list = rex_list::factory('SELECT url, sum(count) as "count" from ' . rex::getTable('pagestats_media') . ' WHERE url LIKE "%'. $search_string .'%" GROUP BY url ORDER BY count DESC');
}



$form = '
<form class="form-inline" action="' . rex_url::backendPage('statistics/media') . '" method="GET">
    <input type="hidden" value="statistics/media" name="page">
    <div class="form-group">
        <label for="exampleInputName2">Suchen nach:</label>
        <input style="line-height: normal;" type="text" value="'. $search_string .'" class="form-control" name="search_string">
    </div>
    <button type="submit" class="btn btn-default">Suchen</button>
</form>
';


$fragment = new rex_fragment();
$fragment->setVar('title', 'Aufrufe pro Tag:');
$fragment->setVar('body', $form, false);
echo $fragment->parse('core/page/section.php');


$list->setColumnLabel('url', 'Url');
$list->setColumnLabel('count', 'Anzahl');
$list->setColumnSortable('url', $direction = 'asc');
$list->setColumnSortable('count', $direction = 'asc');

$fragment2 = new rex_fragment();
$fragment2->setVar('title', 'Medien Aufrufe:');
$fragment2->setVar('content', $list->get(), false);
echo $fragment2->parse('core/page/section.php');