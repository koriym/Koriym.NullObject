<?php

namespace Koriym\NullObject\Annotation;

use Attribute;
use Doctrine\Common\Annotations\NamedArgumentConstructorAnnotation;

/**
 * @Annotation
 */
#[Attribute]
class DbPager implements NamedArgumentConstructorAnnotation
{
    /** @var string */
    public $id;
    /** @var string */
    public $type;

    public function __construct(string $id, string $type)
    {
        unset($id, $type);
    }
}
