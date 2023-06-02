<?php

namespace AndiLeni\Statistics;

use rex;
use rex_addon;
use rex_list;
use rex_sql;
use rex_view;

class VisitDuration
{


    public function getChartData()
    {
        $sql = rex_sql::factory();
        $res = $sql->getArray("select concat(floor(visitduration / 30) * 30, '-', (floor(visitduration / 30) + 1) * 30, ' Sekunden (~', floor(visitduration / 60) + 1, 'min)') as timespan, count(*) as count, floor(visitduration / 30) as dur from " . rex::getTable("pagestats_sessionstats") . " group by timespan, dur order by dur asc");

        $labels = array_column($res, "count");
        $values = array_column($res, "timespan");

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

        $list = rex_list::factory("select concat(floor(visitduration / 30) * 30, '-', (floor(visitduration / 30) + 1) * 30, ' (~', floor(visitduration / 60) + 1, 'min)') as timespan, count(*) as count, floor(visitduration / 30) as dur from " . rex::getTable("pagestats_sessionstats") . " group by timespan, dur order by dur asc", 10000);

        $list->setColumnLabel('count', "Anzahl");
        $list->setColumnLabel('timespan', "Dauer in Sekunden");
        $list->removeColumn("dur");

        $list->addTableAttribute('class', 'dt_order_second statistics_table');

        if ($list->getRows() == 0) {
            $table = rex_view::info($addon->i18n('statistics_no_data'));
        } else {
            $table = $list->get();
        }

        return $table;
    }
}
