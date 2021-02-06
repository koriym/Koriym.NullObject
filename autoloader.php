<?php

declare(strict_types=1);

use Koriym\NullObject\NullObject;

spl_autoload_register(static function (string $className): void {
    $hasNullPostfix = substr($className, -4) === 'Null';
    if (! $hasNullPostfix) {
        return;
    }

    $interfaceName = substr($className, 0, strlen($className) - 4);
    if (! interface_exists($interfaceName)) {
        return;
    }

    $tmpDir = $_ENV['NULL_OBJECT_TMP'] ?? sys_get_temp_dir();
    assert(is_string($tmpDir));
    $nullClass = new NullObject($tmpDir);
    $file = $nullClass->getNullFilePath($interfaceName);
    if (file_exists($file)) {
        require $file;

        return;
    }

    ($nullClass)($interfaceName);
});
