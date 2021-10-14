<?php

/**
 * Can be used to retreive the total amount of visitors
 * F.e. to be used in an old-fashioned Visitor-Counter
 *
 * @author Andreas Lenhardt
 */
class stats_visitor_counter
{
    private $addon;

    /**
     *
     *
     * @return void
     * @throws InvalidArgumentException
     * @author Andreas Lenhardt
     */
    public function __construct()
    {
        $this->addon = rex_addon::get('statistics');
    }

    /**
     *
     *
     * @return mixed
     * @throws rex_sql_exception
     * @author Andreas Lenhardt
     */
    private function get_sql()
    {
        $sql = rex_sql::factory();
        $result = $sql->setQuery('select sum(count) as "count" from ' . rex::getTable('pagestats_visits_per_day'));

        return $result->getValue('count');
    }

    /**
     *
     *
     * @return string
     * @throws rex_sql_exception
     * @author Andreas Lenhardt
     */
    public function get_text()
    {
        $count = $this->get_sql();

        return strval($count);
    }
}
