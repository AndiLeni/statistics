<?php


$form = rex_config_form::factory("stats");

$field = $form->addTextAreaField('pagestats_ignored_paths');
$field->setLabel('Zu ignorierende Pfade:');
$field->setNotice('Ein Pfad pro Zeile.');

$field2 = $form->addTextField('pagestats_visit_duration');
$field2->setLabel('Sitzungsdauer:');
$field2->setNotice('Wie lange eine Sitzung des Besuchers dauern soll. Besuche innerhalb dieser Zeit werden nur einmal gezählt.');
$field2->getValidator()->add( 'type', 'Bitte für die Sitzungsdauer einen ganzzahligen Wert eingeben', 'int');

$field3 = $form->addTextAreaField('pagestats_ignored_ips');
$field3->setLabel('Zu ignorierende IPs:');
$field3->setNotice('Besuche dieser IPs nicht aufzeichnen. Eine IP pro Zeile.');

$addon = rex_addon::get('stats');
$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', 'Einstellungen', false);
$fragment->setVar('body', $form->get(), false);
echo $fragment->parse('core/page/section.php');