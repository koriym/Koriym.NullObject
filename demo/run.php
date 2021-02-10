<?php

namespace FooName;

require dirname(__DIR__) . '/vendor/autoload.php';

$nullLoader = require dirname(__DIR__) . '/autoload.php';
spl_autoload_register($nullLoader);

interface FooInterface
{
}

$null = new FooInterfaceNull;

echo $null instanceof FooInterface ? 'It works!' : 'It does not work.';
