<?php

class rex_statistics_hashremove_cronjob extends rex_cronjob
{

    public function execute()
    {
        $sql = rex_sql::factory();

        try {
            $sql->setQuery("DELETE FROM " . rex::getTable("pagestats_hash") . " WHERE datetime < CURDATE();");
        } catch (rex_sql_exception $e) {
            rex_logger::logException($e);
            return false;
        }

        return true;
    }


    public function getTypeName()
    {
        return "Entferne Client-Hashes des Statistics Addons die älter sind als einen Tag";
    }
}
