<?php

namespace AndiLeni\Statistics;

use rex;
use rex_addon;
use rex_sql;
use rex_view;

class Pagecount
{
    /** @var null|array<int, array{pagecount: int, count: int}> */
    private ?array $rows = null;


    public function getChartData()
    {
        $res = $this->getRows();

        $labels = array_column($res, "count");
        $values = array_column($res, "pagecount");

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
            $table .= '<thead><tr><th>Seitenaufrufe</th><th>Anzahl</th></tr></thead><tbody>';

            foreach ($rows as $row) {
                $pagecount = (string) $row['pagecount'];
                $count = (string) $row['count'];
                $table .= '<tr>';
                $table .= '<td data-sort="' . htmlspecialchars($pagecount, ENT_QUOTES) . '">' . htmlspecialchars($pagecount, ENT_QUOTES) . '</td>';
                $table .= '<td data-sort="' . htmlspecialchars($count, ENT_QUOTES) . '">' . htmlspecialchars($count, ENT_QUOTES) . '</td>';
                $table .= '</tr>';
            }

            $table .= '</tbody></table>';
        }

        return $table;
    }

    /**
     * @return array<int, array{pagecount: int, count: int}>
     */
    private function getRows(): array
    {
        if (null !== $this->rows) {
            return $this->rows;
        }

        $sql = rex_sql::factory();
        $this->rows = array_map(
            static fn(array $row): array => [
                'pagecount' => (int) $row['pagecount'],
                'count' => (int) $row['count'],
            ],
            $sql->getArray("select pagecount , count(*) as 'count' from " . rex::getTable("pagestats_sessionstats") . " group by pagecount order by pagecount asc;")
        );

        return $this->rows;
    }
}
