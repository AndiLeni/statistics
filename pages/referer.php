<?php

$list = rex_list::factory('SELECT referer, count from ' . rex::getTable('pagestats_referer') . ' ORDER BY count DESC');

$list->setColumnLabel('referer', 'Referer');
$list->setColumnLabel('count', 'Anzahl');


$fragment = new rex_fragment();
$fragment->setVar('title', 'Alle Referer:');
$fragment->setVar('content', $list->get(), false);
echo $fragment->parse('core/page/section.php');