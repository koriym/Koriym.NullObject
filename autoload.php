<?php

declare(strict_types=1);

use Koriym\NullObject\NullObject;

return static function (string $className): void {
    $hasNullPostfix = substr($className, -4) === 'Null';
    if (! $hasNullPostfix) {
        return;
    }

    $interfaceName = substr($className, 0, strlen($className) - 4);
    if (! interface_exists($interfaceName)) {
        return;
    }
    $nullObject = new NullObject();
    $generated = $nullObject->generate($interfaceName);
    eval($generated->code);
};
