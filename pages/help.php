<?php

$file = rex_file::get(rex_path::addon('statistics', 'README.md'));

$body = rex_markdown::factory()->parse($file);

$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('statistics_help'));
$fragment->setVar('body', $body, false);
echo $fragment->parse('core/page/section.php');