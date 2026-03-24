<?php

namespace AndiLeni\Statistics;

use rex;
use rex_addon;
use rex_sql;
use rex_view;

class Lastpage
{
    /** @var null|array<int, array{lastpage: string, count: int}> */
    private ?array $rows = null;


    public function getChartData()
    {
        $res = $this->getRows();

        $labels = array_column($res, "lastpage");
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

        $rows = $this->getRows();

        if ([] === $rows) {
            $table = rex_view::info($addon->i18n('statistics_no_data'));
        } else {
            $table = '<table class="dt_order_second statistics_table table table-striped table-hover">';
            $table .= '<thead><tr><th>Seite</th><th>Anzahl</th></tr></thead><tbody>';

            foreach ($rows as $row) {
                $lastpage = (string) $row['lastpage'];
                $count = (string) $row['count'];
                $table .= '<tr>';
                $table .= '<td>' . htmlspecialchars($lastpage, ENT_QUOTES) . '</td>';
                $table .= '<td data-sort="' . htmlspecialchars($count, ENT_QUOTES) . '">' . htmlspecialchars($count, ENT_QUOTES) . '</td>';
                $table .= '</tr>';
            }

            $table .= '</tbody></table>';
        }

        return $table;
    }

    /**
     * @return array<int, array{lastpage: string, count: int}>
     */
    private function getRows(): array
    {
        if (null !== $this->rows) {
            return $this->rows;
        }

        $sql = rex_sql::factory();
        $this->rows = array_map(
            static fn(array $row): array => [
                'lastpage' => (string) $row['lastpage'],
                'count' => (int) $row['count'],
            ],
            $sql->getArray("select lastpage , count(*) as 'count' from " . rex::getTable("pagestats_sessionstats") . " group by lastpage order by count desc;")
        );

        return $this->rows;
    }
}
