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
use function dirname;
use function interface_exists;
use function spl_autoload_register;

class NullObjectTest extends TestCase
{
    /** @var NullObject */
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
     * @param class-string $interface
     *
     * @dataProvider interfaceProvider
     */
    public function testNewInstance(string $interface): void
    {
        assert(interface_exists($interface));
        $nullObject = $this->nullObject->newInstance($interface);
        $this->assertInstanceOf($interface, $nullObject);
    }

    public function testSave(): FakeUserAddInterface
    {
        $nullClass = $this->nullObject->save(FakeUserAddInterface::class, $this->scriptDir);
        $nullObject = new $nullClass();
        $this->assertInstanceOf(FakeUserAddInterface::class, $nullObject);
        $this->assertFileExists(__DIR__ . '/tmp/Koriym_NullObject_FakeUserAddInterfaceNull.php');

        return $nullObject;
    }

    public function testSaveTwice(): void
    {
        $this->testSave();
    }

    /**
     * @depends testSave
     */
    public function testNullObjectAttribute(FakeUserAddInterface $userAdd): void
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
}
