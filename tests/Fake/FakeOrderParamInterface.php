<?php

declare(strict_types=1);

namespace Koriym\NullObject;

use Koriym\NullObject\Annotation\DbPager;

interface FakeOrderParamInterface
{
    /**
     * @DbPager(id="id1", type="type1")
     */
    #[DbPager('type1', 'id1' )]
    public function __invoke(string $id, string $name): void;
}
