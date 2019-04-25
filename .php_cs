<?php
use Narrowspark\CS\Config\Config;

$config = new Config();
$config->getFinder()
    ->files()
    ->in(__DIR__)
    ->exclude('build/stub')
    ->exclude('vendor')
    ->notPath('tests/Fixture/ActualMimeTypeDbList.php')
    ->notPath('src/MimeTypesList.php')
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

$cacheDir = getenv('TRAVIS') ? getenv('HOME') . '/.php-cs-fixer' : __DIR__;

$config->setCacheFile($cacheDir . '/.php_cs.cache');

return $config;
