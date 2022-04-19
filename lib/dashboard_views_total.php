<?php

/**
 * Provides data for the dashboard addon
 *
 */
class rex_dashboard_views_total extends rex_dashboard_item
{

    /**
     *
     *
     * @return string
     * @throws InvalidArgumentException
     * @throws rex_sql_exception
     */
    public function getData(): string
    {
        $sql = rex_sql::factory();
        $sql->setTable(rex::getTable('pagestats_visits_per_day'));
        $sql->select('sum(count) as "count"');
        $total = $sql->getValue('count');

        $sql = rex_sql::factory();
        $sql->setTable(rex::getTable('pagestats_visits_per_day'));
        $sql->setWhere(['date' => date('Y-m-d')]);
        $sql->select('sum(count) as "count"');
        $today = $sql->getValue('count');

        $content = '
        <table class="table">
            <tr>
                <td class="h2">Heute:</td>
                <td class="text-right h2"><b>' . $today . '</b></td>
            </tr>
            <tr>
                <td class="h2">Insgesamt:</td>
                <td class="text-right h2"><b>' . $total . '</b></td>
            </tr>
        </table>
        ';


        return $content;
    }
}
