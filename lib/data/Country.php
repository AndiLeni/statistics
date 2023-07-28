<?php

namespace AndiLeni\Statistics;

use rex;
use rex_addon;
use rex_list;
use rex_sql;
use rex_view;

class Country
{


    public function getChartData()
    {
        $sql = rex_sql::factory();
        $res = $sql->getArray('SELECT name, count FROM ' . rex::getTable('pagestats_data') . ' where type = "country" ORDER BY count DESC');

        $labels = array_column($res, "name");
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

        $list = rex_list::factory('SELECT name, count FROM ' . rex::getTable('pagestats_data') . ' where type = "country" ORDER BY count DESC', 10000);

        $list->setColumnLabel('name', "Anzahl");
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
