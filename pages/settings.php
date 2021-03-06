<?php


// post request which handles deletion of stats data
if (rex_request_method() == 'post') {
    $function = rex_post('func', 'string', '');

    if ($function == 'delete_hash') {
        $sql = rex_sql::factory();
        $sql->setQuery('delete from ' . rex::getTable('pagestats_hash'));
        echo rex_view::success($sql->getRows() . ' ' . $this->i18n('statistics_deleted_hashes'));
    } elseif ($function == 'delete_dump') {
        $sql = rex_sql::factory();
        $count = 0;

        $sql->setQuery('delete from ' . rex::getTable('pagestats_data'));
        $count += $sql->getRows();

        $sql->setQuery('delete from ' . rex::getTable('pagestats_visits_per_day'));
        $count += $sql->getRows();

        $sql->setQuery('delete from ' . rex::getTable('pagestats_visits_per_url'));
        $count += $sql->getRows();

        echo rex_view::success($count . ' ' . $this->i18n('statistics_deleted_dump'));
    } elseif ($function == 'delete_media') {
        $sql = rex_sql::factory();
        $sql->setQuery('delete from ' . rex::getTable('pagestats_media'));
        echo rex_view::success($sql->getRows() . ' ' . $this->i18n('statistics_deleted_bots'));
    } elseif ($function == 'delete_bot') {
        $sql = rex_sql::factory();
        $sql->setQuery('delete from ' . rex::getTable('pagestats_bot'));
        echo rex_view::success($sql->getRows() . ' ' . $this->i18n('statistics_deleted_referer'));
    } elseif ($function == 'delete_referer') {
        $sql = rex_sql::factory();
        $sql->setQuery('delete from ' . rex::getTable('pagestats_referer'));
        echo rex_view::success($sql->getRows() . ' ' . $this->i18n('statistics_deleted_media'));
    }
}


$form = rex_config_form::factory("statistics");


$field2 = $form->addTextField('statistics_visit_duration');
$field2->setLabel($this->i18n('statistics_visit_duration'));
$field2->setNotice($this->i18n('statistics_duration_note'));
$field2->getValidator()->add('type', $this->i18n('statistics_duration_validate'), 'int');


$field = $form->addTextAreaField('statistics_ignored_paths');
$field->setLabel($this->i18n('statistics_ignore_paths'));
$field->setNotice($this->i18n('statistics_paths_note'));


$field3 = $form->addTextAreaField('statistics_ignored_ips');
$field3->setLabel($this->i18n('statistics_ignore_ips'));
$field3->setNotice($this->i18n('statistics_ips_note'));


$field3 = $form->addTextAreaField('pagestats_ignored_regex');
$field3->setLabel($this->i18n('pagestats_ignored_regex'));
$field3->setNotice($this->i18n('pagestats_ignored_regex_note'));


$field4 = $form->addRadioField('statistics_log_all');
$field4->setLabel($this->i18n('statistics_log_404'));
$field4->addOption($this->i18n('statistics_yes'), 1);
$field4->addOption($this->i18n('statistics_no'), 0);
$field4->setNotice($this->i18n('statistics_log_404_note'));


$field4 = $form->addRadioField('statistics_scroll_pagination');
$field4->setLabel($this->i18n('statistics_scroll_pagination'));
$field4->addOption($this->i18n('statistics_scroll_table'), 'table');
$field4->addOption($this->i18n('statistics_scroll_panel'), 'panel');
$field4->addOption($this->i18n('statistics_scroll_none'), 'none');


$field5 = $form->addRadioField('statistics_ignore_url_params');
$field5->setLabel($this->i18n('statistics_statistics_ignore_url_params'));
$field5->addOption($this->i18n('statistics_yes'), 1);
$field5->addOption($this->i18n('statistics_no'), 0);
$field5->setNotice($this->i18n('statistics_statistics_ignore_url_params_note'));


$field6 = $form->addRadioField('statistics_default_datefilter_range');
$field6->setLabel($this->i18n('statistics_default_datefilter_range'));
$field6->addOption($this->i18n('statistics_default_datefilter_last7days'), 'last7days');
$field6->addOption($this->i18n('statistics_default_datefilter_last30days'), 'last30days');
$field6->addOption($this->i18n('statistics_default_datefilter_thisYear'), 'thisYear');
$field6->addOption($this->i18n('statistics_default_datefilter_wholeTime'), 'wholeTime');
$field6->setNotice($this->i18n('statistics_default_datefilter_range_note'));


$field7 = $form->addRadioField('statistics_combine_all_domains');
$field7->setLabel('Fasse alle Domains zusammen');
$field7->addOption($this->i18n('statistics_yes'), 1);
$field7->addOption($this->i18n('statistics_no'), 0);
$field7->setNotice('Alle Domains werden zu einer "Gesamt" Anzahl zusammengefasst. Deaktivieren um Statistiken f??r alle Domains einzeln anzuzeigen.');


$addon = rex_addon::get('statistics');
$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', $this->i18n('statistics_settings'), false);
$fragment->setVar('body', $form->get(), false);
echo $fragment->parse('core/page/section.php');


// forms which should make a post request to this page to trigger deletion of stats data
$content = '
<div style="display: flex; flex-wrap: wrap">

<form style="margin:5px" action="' . rex_url::currentBackendPage() . '" method="post">
<input type="hidden" name="func" value="delete_hash">
<button class="btn btn-danger" type="submit" data-confirm="' . $this->i18n('statistics_confirm_delete_hashes') . '">' . $this->i18n('statistics_delete_hashes') . '</button>
</form>

<form style="margin:5px" action="' . rex_url::currentBackendPage() . '" method="post">
<input type="hidden" name="func" value="delete_dump">
<button class="btn btn-danger" type="submit" data-confirm="' . $this->i18n('statistics_confirm_delete_dump') . '">' . $this->i18n('statistics_delete_visits') . '</button>
</form>

<form style="margin:5px" action="' . rex_url::currentBackendPage() . '" method="post">
<input type="hidden" name="func" value="delete_bot">
<button class="btn btn-danger" type="submit" data-confirm="' . $this->i18n('statistics_confirm_delete_bots') . '">' . $this->i18n('statistics_delete_bots') . '</button>
</form>

<form style="margin:5px" action="' . rex_url::currentBackendPage() . '" method="post">
<input type="hidden" name="func" value="delete_referer">
<button class="btn btn-danger" type="submit" data-confirm="' . $this->i18n('statistics_confirm_delete_referer') . '">' . $this->i18n('statistics_delete_referer') . '</button>
</form>
';


if (rex::isBackend() && rex_plugin::get('statistics', 'media')->isAvailable()) {
    $content .= '
    <form style="margin:5px" action="' . rex_url::currentBackendPage() . '" method="post">
    <input type="hidden" name="func" value="delete_media">
    <button class="btn btn-danger" type="submit" data-confirm="' . $this->i18n('statistics_confirm_delete_media') . '">' . $this->i18n('statistics_delete_media') . '</button>
    </form>
    ';
}

$content .= '</div>';


$fragment = new rex_fragment();
$fragment->setVar('class', 'danger', false);
$fragment->setVar('title', $this->i18n('statistics_delete_statistics'), false);
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');
