<?php


// post request which handles deletion of stats data
if (rex_request_method() == 'post') {
    $function = rex_post('func','string','');

    if ($function == 'delete_hash') {
        $sql = rex_sql::factory();
        $sql->setQuery('delete from ' . rex::getTable('pagestats_hash'));
        echo '<div class="alert alert-success">Es wurden '. $sql->getRows() .' Einträge aus der Tabelle hashes gelöscht.</div>';
    } elseif ($function == 'delete_dump') {
        $sql = rex_sql::factory();
        $sql->setQuery('delete from ' . rex::getTable('pagestats_dump'));
        echo '<div class="alert alert-success">Es wurden '. $sql->getRows() .' Einträge aus der Tabelle dump gelöscht.</div>';
    } elseif ($function == 'delete_media') {
        $sql = rex_sql::factory();
        $sql->setQuery('delete from ' . rex::getTable('pagestats_media'));
        echo '<div class="alert alert-success">Es wurden '. $sql->getRows() .' Einträge aus der Tabelle media gelöscht.</div>';
    } elseif ($function == 'delete_bot') {
        $sql = rex_sql::factory();
        $sql->setQuery('delete from ' . rex::getTable('pagestats_bot'));
        echo '<div class="alert alert-success">Es wurden '. $sql->getRows() .' Einträge aus der Tabelle bot gelöscht.</div>';
    }

}


$form = rex_config_form::factory("stats");

$field = $form->addTextAreaField('pagestats_ignored_paths');
$field->setLabel('Zu ignorierende Pfade:');
$field->setNotice('Ein Pfad pro Zeile.');

$field2 = $form->addTextField('pagestats_visit_duration');
$field2->setLabel('Sitzungsdauer:');
$field2->setNotice('Wie lange eine Sitzung des Besuchers dauern soll. Besuche innerhalb dieser Zeit werden nur einmal gezählt.');
$field2->getValidator()->add('type', 'Bitte für die Sitzungsdauer einen ganzzahligen Wert eingeben', 'int');

$field3 = $form->addTextAreaField('pagestats_ignored_ips');
$field3->setLabel('Zu ignorierende IPs:');
$field3->setNotice('Besuche dieser IPs nicht aufzeichnen. Eine IP pro Zeile.');

$addon = rex_addon::get('stats');
$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', 'Einstellungen', false);
$fragment->setVar('body', $form->get(), false);
echo $fragment->parse('core/page/section.php');


// forms which should make a post request to this page to trigger deletion of stats data
$content = '
<div style="display: flex; flex-wrap: wrap">

<form style="margin:5px" action="' . rex_url::backendPage('stats/settings') . '" method="post">
<input type="hidden" name="func" value="delete_hash">
<button class="btn btn-danger" type="submit" data-confirm="Wirklich alle Hashes löschen?">Alle Hashes löschen</button>
</form>

<form style="margin:5px" action="' . rex_url::backendPage('stats/settings') . '" method="post">
<input type="hidden" name="func" value="delete_dump">
<button class="btn btn-danger" type="submit" data-confirm="Wirklich alle Einträge der Statistik löschen?">Alle Besuche löschen</button>
</form>

<form style="margin:5px" action="' . rex_url::backendPage('stats/settings') . '" method="post">
<input type="hidden" name="func" value="delete_media">
<button class="btn btn-danger" type="submit" data-confirm="Wirklich die gesamte Media-Statistik löschen?">Alle Media-Statistik löschen</button>
</form>

<form style="margin:5px" action="' . rex_url::backendPage('stats/settings') . '" method="post">
<input type="hidden" name="func" value="delete_bot">
<button class="btn btn-danger" type="submit" data-confirm="Wirklich alle Besuche von Bots löschen?">Alle Bots löschen</button>
</form>

</div>
';

$fragment = new rex_fragment();
$fragment->setVar('class', 'danger', false);
$fragment->setVar('title', 'Statistiken löschen', false);
$fragment->setVar('body', $content , false);
echo $fragment->parse('core/page/section.php');


?>

