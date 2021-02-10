<?php

declare(strict_types=1);

array_map('unlink', (array) glob(__DIR__ . '/tmp/*.php'));

require dirname(__DIR__) . '/vendor/autoload.php';
