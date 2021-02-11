<?php

declare(strict_types=1);

namespace Koriym\NullObject;

use Koriym\NullObject\Annotation\DbPager;

interface FakeUserAddInterface
{
    /**
     * @DbPager(id="id1", type="type1")
     */
    #[DbPager('id1', 'type1')]
    public function __invoke(string $id, string $name): void;
}
