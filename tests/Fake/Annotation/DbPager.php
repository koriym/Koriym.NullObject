<?php

namespace Koriym\NullObject\Annotation;

use Attribute;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\NamedArgumentConstructorAnnotation;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 */
#[Attribute]
class DbPager
{
    /** @var string */
    public $id;
    /** @var string */
    public $type;

    public function __construct(string $id, string $type)
    {
        $this->id = $id;
        $this->type = $type;
    }
}
