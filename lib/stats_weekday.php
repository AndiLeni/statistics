<?php


/**
 * 
 * 
 * @author Andreas Lenhardt
 */
class stats_weekday
{
    /**
     * 
     * 
     * @param mixed $weekday 
     * @return string|void 
     * @author Andreas Lenhardt
     */
    public static function get_weekday_string($weekday)
    {
        switch ($weekday['value']) {
            case 1:
                return "Montag";
                break;
            case 2:
                return "Dienstag";
                break;
            case 3:
                return "Mittwoch";
                break;
            case 4:
                return "Donnerstag";
                break;
            case 5:
                return "Freitag";
                break;
            case 6:
                return "Samstag";
                break;
            case 7:
                return "Sonntag";
                break;
        }
    }


    /**
     * 
     * 
     * @return array 
     * @throws InvalidArgumentException 
     * @throws rex_sql_exception 
     * @author Andreas Lenhardt
     */
    private function get_sql()
    {
        $sql = rex_sql::factory();
        $result = $sql->setQuery('SELECT weekday, COUNT(weekday) as "count" FROM ' . rex::getTable('pagestats_dump') . ' GROUP BY weekday ORDER BY weekday ASC');

        $data = ["Montag" => 0, "Dienstag" => 0, "Mittwoch" => 0, "Donnerstag" => 0, "Freitag" => 0, "Samstag" => 0, "Sonntag" => 0];

        foreach ($result as $row) {
            $data[$this->get_weekday_string(['value' => $row->getValue('weekday')])] = $row->getValue('count');
        }

        return $data;
    }


    /**
     * 
     * 
     * @return (string|false)[] 
     * @throws InvalidArgumentException 
     * @throws rex_sql_exception 
     * @author Andreas Lenhardt
     */
    public function get_data()
    {
        $data = $this->get_sql();

        return [
            'labels' => json_encode(["Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag", "Sonntag"]),
            'values' => json_encode(array_values($data)),
        ];
    }


    /**
     * 
     * 
     * @return string 
     * @throws InvalidArgumentException 
     * @throws rex_exception 
     * @author Andreas Lenhardt
     */
    public function get_list()
    {
        $list = rex_list::factory('SELECT weekday, COUNT(weekday) as "count" FROM ' . rex::getTable('pagestats_dump') . ' GROUP BY weekday ORDER BY count DESC');
        $list->setColumnLabel('weekday', 'Name');
        $list->setColumnLabel('count', 'Anzahl');
        $list->setColumnSortable('weekday', $direction = 'asc');
        $list->setColumnSortable('count', $direction = 'asc');
        $list->setColumnFormat('weekday', 'custom', __CLASS__ . '::get_weekday_string');

        return $list->get();
    }


    /**
     * 
     * 
     * @return array 
     * @throws InvalidArgumentException 
     * @throws rex_sql_exception 
     * @author Andreas Lenhardt
     */
    public function get_data_dashboard()
    {
        $data = $this->get_sql();

        return $data;
    }
}
