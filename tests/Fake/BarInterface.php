<?php

declare(strict_types=1);

namespace Koriym\NullObject;

interface BarInterface
{
    /**
     * @return mixed
     */
    public function __invoke();
}
