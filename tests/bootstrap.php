<?php

declare(strict_types=1);

$_ENV['NULL_OBJECT_TMP'] = __DIR__ . '/tmp';

array_map('unlink', (array) glob(__DIR__ . '/tmp/*.php')); // @phpstan-ignore-line
array_map('unlink', (array) glob(__DIR__ . '/tmp1/*.php')); // @phpstan-ignore-line

require dirname(__DIR__) . '/vendor/autoload.php';
