<?php

namespace AndiLeni\Statistics;

use DateTimeImmutable;
use InvalidArgumentException;
use rex;
use rex_logger;
use rex_sql;
use rex_sql_exception;

class Event
{

    /**
     * 
     * @api
     * @param string $name 
     * @return bool 
     * @throws InvalidArgumentException 
     */
    public static function log($name)
    {
        $datetime_now = new DateTimeImmutable();
        $datetime_now = $datetime_now->format('Y-m-d');

        $sql = rex_sql::factory();

        try {
            $sql->setQuery('INSERT INTO ' . rex::getTable('pagestats_api') . ' (name,date,count) VALUES (:name,:date,1) ON DUPLICATE KEY UPDATE count = count + 1;', ['name' => $name, 'date' => $datetime_now]);

            return true;
        } catch (rex_sql_exception $e) {
            rex_logger::logException($e);
            return false;
        }
    }
}
