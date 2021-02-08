<?php

declare(strict_types=1);

namespace Koriym\NullObject;

use Doctrine\Common\Annotations\AnnotationReader;
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
        $method = (new ReflectionMethod($userAdd, '__invoke'));
        $anotation = (new AnnotationReader())->getMethodAnnotation($method, DbPager::class);
        $this->assertInstanceOf(DbPager::class, $anotation);
    }
}
