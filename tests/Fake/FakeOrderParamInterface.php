<?php

declare(strict_types=1);

namespace Koriym\NullObject;

use Koriym\NullObject\Annotation\DbPager;

interface FakeOrderParamInterface
{
    #[DbPager('id1', 'type1')]
    public function __invoke(string $id, string $name): void;
}
