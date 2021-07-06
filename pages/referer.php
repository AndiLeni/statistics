<?php

$list = rex_list::factory('SELECT referer, count from ' . rex::getTable('pagestats_referer') . ' ORDER BY count DESC, referer ASC');

$list->setColumnLabel('referer', $this->i18n('statistics_url'));
$list->setColumnLabel('count', $this->i18n('statistics_count'));


$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('statistics_all_referer'));
$fragment->setVar('content', $list->get(), false);
echo $fragment->parse('core/page/section.php');
