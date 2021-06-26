<?php


$list = rex_list::factory('SELECT url, sum(count) as "count" from ' . rex::getTable('pagestats_media') . ' GROUP BY url ORDER BY count DESC');
$list->setColumnLabel('url', 'Url');
$list->setColumnLabel('count', 'Anzahl');
$list->setColumnSortable('url', $direction = 'asc');
$list->setColumnSortable('count', $direction = 'asc');
// $list->setColumnParams('url', ['url' => '###url###']);

$fragment = new rex_fragment();
$fragment->setVar('title', 'Medien Aufrufe:');
$fragment->setVar('content', $list->get(), false);
echo $fragment->parse('core/page/section.php');

?>
