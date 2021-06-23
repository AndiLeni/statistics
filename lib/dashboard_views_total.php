<?php

class rex_dashboard_views_total extends rex_dashboard_item
{
    public function getData()
    {
        $sql = rex_sql::factory();
        $sql->setTable(rex::getTable('pagestats_dump'));
        $sql->select('count(url) as "count"');


        $content = '<h2><b>'. $sql->getValue('count') .'</b></h2>';
        return $content;
    }
}
