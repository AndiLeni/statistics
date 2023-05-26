<?php

class rex_statistics_hashremove_cronjob extends rex_cronjob
{

    public function execute()
    {
        $sql = rex_sql::factory();
        $sql->setQuery("DELETE FROM " . rex::getTable("pagestats_hash") . " WHERE datetime < CURDATE();");
    }
}
