<?php
class stats_visitor_counter
{
    private $addon;

    public function __construct()
    {
        $this->addon = rex_addon::get('stats');
    }

    private function get_sql()
    {
        $sql = rex_sql::factory();
        $result = $sql->setQuery('select count(url) as "count" from rex_pagestats_dump');

        return $result->getValue('count');
    }

    public function get_text()
    {
        $count = $this->get_sql();

        return strval($count);
    }
}
