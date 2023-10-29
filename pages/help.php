<?php

$addon = rex_addon::get('statistics');

$file = rex_file::get(rex_path::addon('statistics', 'README.md'));

$body = rex_markdown::factory()->parse($file);

// Search for preview images and replace them with assets-URL
$pattern = '/src\s*=\s*"(\.\/assets\/preview\/)(.+?)"/';
$assetsUrl = $addon->getAssetsUrl('preview');
$replacement = 'src="'.$assetsUrl.'/\2"';
$body = preg_replace($pattern, $replacement, $body);

$fragment = new rex_fragment();
$fragment->setVar('class', 'rex-readme', false);
$fragment->setVar('title', $addon->i18n('statistics_help'));
$fragment->setVar('body', $body, false);
echo $fragment->parse('core/page/section.php');
