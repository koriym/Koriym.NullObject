<?php

declare(strict_types=1);

namespace Koriym\NullObject;

use Koriym\NullObject\Annotation\DbPager;

interface FakeNamedParamInterface
{
    /**
     * @DbPager(id="id1", type="type1")
     */
    #[DbPager(type:'type1', id:'id1' )]
    public function __invoke(string $id, string $name): void;
}
