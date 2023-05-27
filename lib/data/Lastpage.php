<?php

namespace AndiLeni\Statistics;

use rex;
use rex_addon;
use rex_list;
use rex_sql;
use rex_view;

class Lastpage
{


    public function getChartData()
    {
        $sql = rex_sql::factory();
        $res = $sql->getArray("select url , count(*) as 'count' from " . rex::getTable("pagestats_lastpage") . " group by url order by count desc;");

        $labels = array_column($res, "url");
        $values = array_column($res, "count");

        return [
            "labels" => $labels,
            "values" => $values
        ];
    }


    /**
     * 
     * 
     * @return string 
     * @throws InvalidArgumentException 
     * @throws rex_exception 
     */
    public function getList(): string
    {
        $addon = rex_addon::get('statistics');

        $list = rex_list::factory("select url , count(*) as 'count' from " . rex::getTable("pagestats_lastpage") . " group by url order by count desc", 10000);

        $list->setColumnLabel('url', "Seite");
        $list->setColumnLabel('count', "Anzahl");

        $list->addTableAttribute('class', 'dt_order_second statistics_table');

        if ($list->getRows() == 0) {
            $table = rex_view::info($addon->i18n('statistics_no_data'));
        } else {
            $table = $list->get();
        }

        return $table;
    }
}
