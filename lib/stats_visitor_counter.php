<?php

/**
 * Can be used to retreive the total amount of visitors
 * F.e. to be used in an old-fashioned Visitor-Counter
 *
 */
class stats_visitor_counter
{


    /**
     * 
     * 
     * @return rex_sql 
     * @throws InvalidArgumentException 
     * @throws rex_sql_exception 
     */
    private function get_sql(): rex_sql
    {
        $sql = rex_sql::factory();
        $result = $sql->setQuery('select sum(count) as "count" from ' . rex::getTable('pagestats_visits_per_day'));

        return $result;
    }

    /**
     *
     *
     * @return string
     * @throws rex_sql_exception
     */
    public function get_text(): string
    {
        $sql = $this->get_sql();
        $count = $sql->getValue('count');

        return strval($count);
    }
}
