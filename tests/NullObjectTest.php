<?php

declare(strict_types=1);

namespace Koriym\NullObject;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class NullObjectTest extends TestCase
{
    public function testGenerateNullObject(): UserAddInterface
    {
        $nullClass = UserAddInterface::class . 'Null';
        $nullObject = new $nullClass();
        $this->assertInstanceOf(UserAddInterface::class, $nullObject);

        return $nullObject;
    }

    /**
     * @depends testGenerateNullObject
     */
    public function testNullObjectAttribute(UserAddInterface $userAdd): void
    {
        $attrs = (new ReflectionMethod($userAdd, '__invoke'))->getAttributes();
        $attr = $attrs[0]->newInstance();
        $this->assertInstanceOf(DbPager::class, $attr);
    }
}
