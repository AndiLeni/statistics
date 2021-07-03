<?php

/**
 * Provides data for the dashboard addon
 *
 * @author Andreas Lenhardt
 */
class rex_dashboard_views_total extends rex_dashboard_item
{

    /**
     *
     *
     * @return string
     * @throws InvalidArgumentException
     * @throws rex_sql_exception
     * @author Andreas Lenhardt
     */
    public function getData()
    {
        $sql = rex_sql::factory();
        $sql->setTable(rex::getTable('pagestats_dump'));
        $sql->select('count(url) as "count"');
        $total = $sql->getValue('count');

        $sql = rex_sql::factory();
        $sql->setTable(rex::getTable('pagestats_dump'));
        $sql->setWhere(['date' => date('Y-m-d')]);
        $sql->select('count(url) as "count"');
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
