<?php


$form = rex_config_form::factory("stats");

$field = $form->addTextAreaField('pagestats_ignored_paths');
$field->setLabel('Zu ignorierende Pfade:');
$field->setNotice('Ein Pfad pro Zeile.');

$addon = rex_addon::get('beispiel_addon');
$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', 'Einstellungen', false);
$fragment->setVar('body', $form->get(), false);
echo $fragment->parse('core/page/section.php');