<?php

declare(strict_types=1);

namespace Koriym\NullObject;

use PHPUnit\Framework\TestCase;

class NullObjectTest extends TestCase
{
    /** @var NullObject */
    protected $nullObject;

    protected function setUp(): void
    {
        $this->nullObject = new NullObject();
    }

    public function testIsInstanceOfNullObject(): void
    {
        $actual = $this->nullObject;
        $this->assertInstanceOf(NullObject::class, $actual);
    }
}
