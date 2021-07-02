<?php


// post request which handles deletion of stats data
if (rex_request_method() == 'post') {
    $function = rex_post('func','string','');

    if ($function == 'delete_media') {
        $sql = rex_sql::factory();
        $sql->setQuery('delete from ' . rex::getTable('pagestats_media'));
        echo '<div class="alert alert-success">Es wurden '. $sql->getRows() .' Einträge aus der Tabelle media gelöscht.</div>';
    }

}


$form = rex_config_form::factory("statistics/media");


$field = $form->addRadioField ('pagestats_media_log_all');
$field->setLabel('Alle Medien loggen:');
$field->addOption ('Ja', true);
$field->addOption ('Nein', false);
$field->setNotice('Loggt alle Aufrufe von Medien Dateien. Dadurch wird die Website verlangsamt. VORSICHTIG EINSETZEN!');


$field = $form->addRadioField ('pagestats_media_log_mm');
$field->setLabel('Medien loggen mit zugehörigem Media-Manager-Effekt.');
$field->addOption ('Ja', true);
$field->addOption ('Nein', false);
$field->setNotice('Loggt nur Medien die den Media-Manager-Effekt haben.');



$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', 'Einstellungen', false);
$fragment->setVar('body', $form->get(), false);
echo $fragment->parse('core/page/section.php');


// forms which should make a post request to this page to trigger deletion of stats data
$content = '
<div style="display: flex; flex-wrap: wrap">

<form style="margin:5px" action="' . rex_url::backendPage('statistics/media/settings') . '" method="post">
<input type="hidden" name="func" value="delete_media">
<button class="btn btn-danger" type="submit" data-confirm="Wirklich die gesamte Media-Statistik löschen?">Alle Media-Statistik löschen</button>
</form>

</div>
';



$fragment = new rex_fragment();
$fragment->setVar('class', 'danger', false);
$fragment->setVar('title', 'Statistiken löschen', false);
$fragment->setVar('body', $content , false);
echo $fragment->parse('core/page/section.php');
