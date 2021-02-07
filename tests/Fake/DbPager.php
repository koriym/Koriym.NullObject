<?php

namespace Koriym\NullObject;

use Attribute;

#[Attribute]
class DbPager
{
    public function __construct(string $id, string $type)
    {
        unset($id, $type);
    }
}
