<?php

/**
 * Handles the "weekday" data for statistics
 *
 * @author Andreas Lenhardt
 */
class stats_weekday
{

    private $addon;

    /**
     * 
     * 
     * @return void 
     * @author Andreas Lenhardt
     */
    public function __construct()
    {
        $this->addon = rex_addon::get('statistics');
    }


    /**
     *
     *
     * @param mixed $weekday
     * @return string|void
     * @author Andreas Lenhardt
     */
    public function get_weekday_string($weekday)
    {
        switch ($weekday['value']) {
            case 1:
                return $this->addon->i18n('statistics_monday');
                break;
            case 2:
                return $this->addon->i18n('statistics_tuesday');
                break;
            case 3:
                return $this->addon->i18n('statistics_wednesday');
                break;
            case 4:
                return $this->addon->i18n('statistics_thursday');
                break;
            case 5:
                return $this->addon->i18n('statistics_friday');
                break;
            case 6:
                return $this->addon->i18n('statistics_saturday');
                break;
            case 7:
                return $this->addon->i18n('statistics_sunday');
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

        $addon = rex_addon::get('statistics');

        $data = [
            $addon->i18n('statistics_monday') => 0,
            $addon->i18n('statistics_tuesday') => 0,
            $addon->i18n('statistics_wednesday') => 0,
            $addon->i18n('statistics_thursday') => 0,
            $addon->i18n('statistics_friday') => 0,
            $addon->i18n('statistics_saturday') => 0,
            $addon->i18n('statistics_sunday') => 0
        ];

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
            'labels' => json_encode([
                $this->addon->i18n('statistics_monday'),
                $this->addon->i18n('statistics_tuesday'),
                $this->addon->i18n('statistics_wednesday'),
                $this->addon->i18n('statistics_thursday'),
                $this->addon->i18n('statistics_friday'),
                $this->addon->i18n('statistics_saturday'),
                $this->addon->i18n('statistics_sunday')
            ]),
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
        $list = rex_list::factory('SELECT weekday, COUNT(weekday) as "count" FROM ' . rex::getTable('pagestats_dump') . ' GROUP BY weekday ORDER BY count DESC', 7);
        $list->setColumnLabel('weekday', $this->addon->i18n('statistics_name'));
        $list->setColumnLabel('count', $this->addon->i18n('statistics_count'));
        $list->setColumnSortable('weekday', $direction = 'asc');
        $list->setColumnSortable('count', $direction = 'asc');
        $list->setColumnFormat('weekday', 'custom',  function ($params) {
            switch ($params['value']) {
                case 1:
                    return $this->addon->i18n('statistics_monday');
                    break;
                case 2:
                    return $this->addon->i18n('statistics_tuesday');
                    break;
                case 3:
                    return $this->addon->i18n('statistics_wednesday');
                    break;
                case 4:
                    return $this->addon->i18n('statistics_thursday');
                    break;
                case 5:
                    return $this->addon->i18n('statistics_friday');
                    break;
                case 6:
                    return $this->addon->i18n('statistics_saturday');
                    break;
                case 7:
                    return $this->addon->i18n('statistics_sunday');
                    break;
            }
        });

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
