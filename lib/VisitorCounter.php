<?php

namespace AndiLeni\Statistics;

use rex;
use rex_sql;
use InvalidArgumentException;
use rex_sql_exception;

/**
 * Can be used to retreive the total amount of visitors
 * F.e. to be used in an old-fashioned Visitor-Counter
 *
 */
class VisitorCounter
{


    /**
     * 
     * 
     * @return string 
     * @throws InvalidArgumentException 
     * @throws rex_sql_exception 
     */
    public static function getText(): string
    {
        $sql = rex_sql::factory();
        $result = $sql->setQuery('select sum(count) as "count" from ' . rex::getTable('pagestats_visits_per_day'));
        $count = $result->getValue('count');

        return strval($count);
    }
}
