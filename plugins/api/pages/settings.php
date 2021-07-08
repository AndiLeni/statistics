<?php


// post request which handles deletion of stats data
if (rex_request_method() == 'post') {
    $function = rex_post('func','string','');

    if ($function == 'delete_campaigns') {
        $sql = rex_sql::factory();
        $sql->setQuery('delete from ' . rex::getTable('pagestats_api'));
        echo rex_view::success('Es wurden '. $sql->getRows() .' Einträge aus der Tabelle api gelöscht.');
    }

}


$form = rex_config_form::factory("statistics/api");


$field = $form->addRadioField ('statistics_api_enable');
$field->setLabel($this->i18n('statistics_api_enable_campaigns'));
$field->addOption ($this->i18n('statistics_api_yes'), true);
$field->addOption ($this->i18n('statistics_api_no'), false);
$field->setNotice($this->i18n('statistics_api_enable_campaigns_note'));




$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', $this->i18n('statistics_api_settings'), false);
$fragment->setVar('body', $form->get(), false);
echo $fragment->parse('core/page/section.php');


// forms which should make a post request to this page to trigger deletion of stats data
$content = '
<div style="display: flex; flex-wrap: wrap">

<form style="margin:5px" action="' . rex_url::currentBackendPage() . '" method="post">
<input type="hidden" name="func" value="delete_campaigns">
<button class="btn btn-danger" type="submit" data-confirm="'. $this->i18n('statistics_api_delete_api_confirm') .'">'. $this->i18n('statistics_api_delete_api') .'</button>
</form>

</div>
';



$fragment = new rex_fragment();
$fragment->setVar('class', 'danger', false);
$fragment->setVar('title', $this->i18n('statistics_api_delete'), false);
$fragment->setVar('body', $content , false);
echo $fragment->parse('core/page/section.php');
