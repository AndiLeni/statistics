<?php

/**
 * Used on the page "campaigns.php" to handle and retreive data for a single api-request
 *
 * @author Andreas Lenhardt
 */
class stats_campaign_details
{
    private $name;
    private $date_start;
    private $date_end;

    /**
     *
     *
     * @param string $url
     * @return void
     * @author Andreas Lenhardt
     */
    public function __construct(string $name, $date_start, $date_end)
    {
        $this->name = $name;
        $this->date_start = $date_start;
        $this->date_end = $date_end;
    }


    /**
     *
     *
     * @return (string|false)[]
     * @throws InvalidArgumentException
     * @throws rex_sql_exception
     * @author Andreas Lenhardt
     */
    public function get_sum_per_day()
    {
        $sql = rex_sql::factory();

        // modify to include end date in period because SQL BETWEEN includes start and end date, but DatePeriod excludes end date
        // without modification an additional day would be fetched from database
        $end = clone $this->date_end;
        $end->modify('+1 day');

        $period = new DatePeriod(
            $this->date_start,
            new DateInterval('P1D'),
            $end
        );

        foreach ($period as $value) {
            $array[$value->format("d.m.Y")] = "0";
        }

        $sum_per_day = $sql->setQuery('SELECT date, count from ' . rex::getTable('pagestats_api') . ' WHERE name = :name and date between :start and :end GROUP BY date ORDER BY date ASC', ['name' => $this->name, 'start' => $this->date_start->format('Y-m-d'), 'end' => $this->date_end->format('Y-m-d')]);

        $data = [];

        if ($sum_per_day->getRows() != 0) {
            foreach ($sum_per_day as $row) {
                $date = DateTime::createFromFormat('Y-m-d', $row->getValue('date'))->format('d.m.Y');
                $arr2[$date] = $row->getValue('count');
            }

            $data = array_merge($array, $arr2);
        }

        return [
            'labels' => array_keys($data),
            'values' => array_values($data),
        ];
    }
}
