<?php

declare(strict_types=1);

namespace Koriym\NullObject;

use DateTime;
use Doctrine\Common\Annotations\AnnotationReader;
use Koriym\NullObject\Exception\LogicException;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class NullObjectTest extends TestCase
{
    public function testInvoke(): UserAddInterface
    {
        $nullClass = (new NullObject(__DIR__ . '/tmp'))(UserAddInterface::class);
        $nullObject = new $nullClass();
        $this->assertInstanceOf(UserAddInterface::class, $nullObject);
        (new NullObject(__DIR__ . '/tmp'))(UserAddInterface::class);

        return $nullObject;
    }

    /**
     * @depends testInvoke
     */
    public function testNullObjectAttribute(UserAddInterface $userAdd): void
    {
        $method = (new ReflectionMethod($userAdd, '__invoke'));
        $anotation = (new AnnotationReader())->getMethodAnnotation($method, DbPager::class);
        $this->assertInstanceOf(DbPager::class, $anotation);
    }

    public function testAutoloader(): void
    {
        $nullClass = BarInterface::class . 'Null';
        $nullObject = new $nullClass();
        $this->assertInstanceOf(BarInterface::class, $nullObject);
    }

    public function testInvaliClass(): void
    {
        $this->expectException(LogicException::class);
        (new NullObject(__DIR__ . '/tmp'))->getCode(DateTime::class);
    }
}
