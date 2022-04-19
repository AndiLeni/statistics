<?php


// post request which handles deletion of stats data
if (rex_request_method() == 'post') {
    $function = rex_post('func', 'string', '');

    if ($function == 'delete_media') {
        $sql = rex_sql::factory();
        $sql->setQuery('delete from ' . rex::getTable('pagestats_media'));
        echo rex_view::success('Es wurden ' . $sql->getRows() . ' Einträge aus der Tabelle media gelöscht.</div>');
    }
}


$form = rex_config_form::factory("statistics/media");


$field = $form->addRadioField('statistics_media_log_all');
$field->setLabel($this->i18n('statistics_media_log_all'));
$field->addOption($this->i18n('statistics_media_yes'), 1);
$field->addOption($this->i18n('statistics_media_no'), 0);
$field->setNotice($this->i18n('statistics_media_log_all_note'));


$field = $form->addRadioField('statistics_media_log_mm');
$field->setLabel($this->i18n('statistics_media_log_mm'));
$field->addOption($this->i18n('statistics_media_yes'), 1);
$field->addOption($this->i18n('statistics_media_no'), 0);
$field->setNotice($this->i18n('statistics_media_log_mm_note'));



$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', $this->i18n('statistics_media_settings'), false);
$fragment->setVar('body', $form->get(), false);
echo $fragment->parse('core/page/section.php');


// forms which should make a post request to this page to trigger deletion of stats data
$content = '
<div style="display: flex; flex-wrap: wrap">

<form style="margin:5px" action="' . rex_url::backendPage('statistics/media/settings') . '" method="post">
<input type="hidden" name="func" value="delete_media">
<button class="btn btn-danger" type="submit" data-confirm="' . $this->i18n('statistics_media_delete_media_confirm') . '">' . $this->i18n('statistics_media_delete_media') . '</button>
</form>

</div>
';



$fragment = new rex_fragment();
$fragment->setVar('class', 'danger', false);
$fragment->setVar('title', $this->i18n('statistics_media_delete_stats'), false);
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');
