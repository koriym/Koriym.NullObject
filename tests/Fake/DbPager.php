<?php

namespace Koriym\NullObject;

use Attribute;
use Doctrine\Common\Annotations\NamedArgumentConstructorAnnotation;

/**
 * @Annotation
 */
#[Attribute]
class DbPager implements NamedArgumentConstructorAnnotation
{
    public $id;
    public $type;

    public function __construct(string $id, string $type)
    {
        unset($id, $type);
    }
}
