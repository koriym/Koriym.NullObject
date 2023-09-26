<?php

declare(strict_types=1);

namespace Koriym\NullObject;

use DateTime;
use Doctrine\Common\Annotations\AnnotationReader;
use Koriym\NullObject\Annotation\DbPager;
use Koriym\NullObject\Exception\LogicException;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

use function assert;
use function class_exists;
use function dirname;
use function interface_exists;
use function spl_autoload_register;

/**
 * @template T of object
 */
class NullObjectTest extends TestCase
{
    /** @var NullObject<T> */
    private $nullObject;

    /** @var string */
    private $scriptDir;

    protected function setUp(): void
    {
        $this->nullObject = new NullObject();
        $this->scriptDir = __DIR__ . '/tmp';
        parent::setUp();
    }

    /**
     * @return list<list<string>>
     */
    public function interfaceProvider(): array
    {
        return [
            [FakeFooInterface::class],
            [FakeNullInterface::class],
        ];
    }

    /**
     * @param class-string<T> $interface
     *
     * @dataProvider interfaceProvider
     */
    public function testNewInstance(string $interface): void
    {
        assert(interface_exists($interface));
        $nullObject = $this->nullObject->newInstance($interface);
        $this->assertInstanceOf($interface, $nullObject);
    }

    public function testSave(): FakeNamedParamInterface
    {
        $nullClass = $this->nullObject->save(FakeNamedParamInterface::class, $this->scriptDir);
        $nullObject = new $nullClass();
        $this->assertInstanceOf(FakeNamedParamInterface::class, $nullObject);
        assert($nullObject instanceof FakeNamedParamInterface);

        return $nullObject;
    }

    public function testSaveTwice(): void
    {
        $this->testSave();
    }

    /**
     * @depends testSave
     */
    public function testNullObjectAttribute(FakeNamedParamInterface $userAdd): void
    {
        $method = (new ReflectionMethod($userAdd, '__invoke'));
        $anotation = (new AnnotationReader())->getMethodAnnotation($method, DbPager::class);
        $this->assertInstanceOf(DbPager::class, $anotation);
    }

    public function testAutoloader(): void
    {
        spl_autoload_register(require dirname(__DIR__) . '/autoload.php');
        $nullClass = BarInterface::class . 'Null';
        $nullObject = new $nullClass(); // @phpstan-ignore-line
        $this->assertInstanceOf(BarInterface::class, $nullObject);
    }

    public function testInvalidClass(): void
    {
        $this->expectException(LogicException::class);
        (new NullObject())->generate(DateTime::class);
    }

    /**
     * @required PHP8
     */
    public function testNamedParamsAttribute(): void
    {
        $nullClass = $this->nullObject->save(FakeNamedParamInterface::class, $this->scriptDir);
        $nullObject = new $nullClass();
        $method = new ReflectionMethod($nullObject, '__invoke');
        $dbPager = $method->getAttributes(DbPager::class)[0];
        $instance = $dbPager->newInstance();
        $this->assertSame('id1', $instance->id);
        $this->assertSame('type1', $instance->type);
    }

    /**
     * @required PHP8
     */
    public function testOrderParamsAttribute(): void
    {
        $nullClass = $this->nullObject->save(FakeOrderParamInterface::class, $this->scriptDir);
        $nullObject = new $nullClass();
        $method = new ReflectionMethod($nullObject, '__invoke');
        $dbPager = $method->getAttributes(DbPager::class)[0];
        $instance = $dbPager->newInstance();
        $this->assertSame('id1', $instance->id);
        $this->assertSame('type1', $instance->type);
    }

    public function testCreateMultipleTimes(): void
    {
        $nullClassName1 = $this->nullObject->save(FakeChildInterface::class, $this->scriptDir);
        $nullClassName2 = $this->nullObject->save(FakeChildInterface::class, $this->scriptDir . '1');
        $this->assertTrue(class_exists($nullClassName1));
//        $this->assertTrue(class_exists($nullClassName2));
    }
}
