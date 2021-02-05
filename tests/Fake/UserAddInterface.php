<?php

declare(strict_types=1);

namespace Koriym\NullObject;

interface UserAddInterface
{
    public function __invoke(string $id, string $name): void;
}
