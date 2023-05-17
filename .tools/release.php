<?php

$new_version = $argv[1];


// set version and date in changelog
$today = new DateTimeImmutable();

$changelog_file = file_get_contents(__DIR__ . '/../Changelog.md');
$changed = preg_replace('/xx.xx.xxxx/', $today->format('d.m.Y'), $changelog_file);
$changed = preg_replace('/x.x.x/', $new_version, $changed);
$changed = preg_replace('/# Changelog/', '# Changelog' . PHP_EOL . PHP_EOL . PHP_EOL . '## [x.x.x] - xx.xx.xxxx', $changed);
file_put_contents(__DIR__ . '/../Changelog.md', $changed);


// update version in package.yml files
foreach (['package.yml'] as $file) {
    $package_yml_file = file_get_contents(__DIR__ . '/../' . $file);
    $changed = preg_replace('/version: \d\.\d\.\d/', 'version: ' . $new_version, $package_yml_file);
    file_put_contents(__DIR__ . '/../' . $file, $changed);
}
