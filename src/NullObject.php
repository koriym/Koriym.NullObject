<?php

declare(strict_types=1);

namespace Koriym\NullObject;

use Koriym\NullObject\Exception\FileNotWritable;
use Koriym\NullObject\Exception\LogicException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;

use function array_slice;
use function assert;
use function class_exists;
use function dirname;
use function file;
use function file_put_contents;
use function implode;
use function interface_exists;
use function is_dir;
use function is_string;
use function mkdir;
use function rename;
use function sprintf;
use function str_replace;
use function tempnam;
use function unlink;

use const PHP_EOL;
use const PHP_VERSION_ID;

final class NullObject implements NullObjectInterface
{
    private const CLASS_TEMPLATE = <<<EOT
%sclass %s implements \%s
{
%s}
EOT;

    /**
     * {@inheritDoc}
     */
    public function newInstance(string $interface): object
    {
        $generated = $this->generate($interface);
        eval($generated->code);
        $class = $generated->class;

        /** @psalm-suppress MixedMethodCall */
        $object = new $class();
        assert($object instanceof $interface);

        return $object;
    }

    /**
     * {@inheritDoc}
     */
    public function generate(string $interface): GeneratedCode
    {
        $class = new ReflectionClass($interface);
        if (! interface_exists($class->getName())) {
            throw new LogicException($class->getName());
        }

        $classMeta = $this->getClassMeta($class);

        $shortNullClassName = $class->getShortName() . 'Null';
        /** @var class-string $fqClassName */
        $fqClassName = $class->getName() . 'Null';
        $code = sprintf(self::CLASS_TEMPLATE, $classMeta, $shortNullClassName, $interface, $this->getMethods($class));

        return new GeneratedCode($fqClassName, $code);
    }

    /**
     * {@inheritDoc}
     */
    public function save(string $interface, string $scriptDir): string
    {
        $nullClass = $interface . 'Null';
        if (class_exists($nullClass, false)) {
            return $nullClass;
        }

        $generated = $this->generate($interface);
        $filePath = $generated->filePath($scriptDir);
        $this->filePutContents($filePath, $generated->phpCode());
        /** @psalm-suppress UnresolvableInclude */
        require $filePath;

        return $generated->class;
    }

    /**
     * @param ReflectionClass<object> $class
     */
    private function getClassMeta(ReflectionClass $class): string
    {
        $file = (array) file((string) $class->getFileName());
        $fileMeta = array_slice($file, 1, $class->getStartLine() - 2);

        return implode('', $fileMeta);
    }

    /**
     * @param ReflectionClass<object> $class
     */
    private function getMethods(ReflectionClass $class): string
    {
        $methods = $class->getMethods();
        $methodStrings = [];
        $file = (array) file((string) $class->getFileName());
        foreach ($methods as $method) {
            $interfaceMethod = implode(PHP_EOL, array_slice($file, $method->getStartLine() - 1, $method->getEndLine()  - $method->getStartLine() + 1));
            $methodSignature = str_replace(';', "\n    {\n    }", $interfaceMethod);
            $methodStrings[] = $this->getMethodMeta($method) . $methodSignature;
        }

        return implode(PHP_EOL, $methodStrings);
    }

    private function getMethodMeta(ReflectionMethod $method): string
    {
        $attr =  PHP_VERSION_ID >= 80000 ? sprintf("    %s\n", $this->getAttributes($method)) : '';

        return sprintf("    %s\n%s", $method->getDocComment(), $attr);
    }

    private function getAttributes(ReflectionMethod $method): string
    {
        /** @var list<ReflectionAttribute> $attrs */
        $attrs = $method->getAttributes();
        $attrList = [];
        if ($attrs) {
            foreach ($attrs as $attr) {
                $attrList[] = $this->getAttribute($attr);
            }
        }

        return implode(PHP_EOL, $attrList);
    }

    public function filePutContents(string $filename, string $content): void
    {
        $dir = dirname($filename);
        ! is_dir($dir) && ! mkdir($dir, 0777, true) && ! is_dir($dir);
        $tmpFile = tempnam(dirname($filename), 'swap');
        if (is_string($tmpFile) && file_put_contents($tmpFile, $content) && @rename($tmpFile, $filename)) {
            return;
        }

        // @codeCoverageIgnoreStart
        @unlink((string) $tmpFile);

        throw new FileNotWritable($filename);
        // @codeCoverageIgnoreEnd
    }

    private function getAttribute(ReflectionAttribute $attr): string
    {
        /** @var array<float|int|string> $args */
        $args = $attr->getArguments();
        $argList = [];
        /** @var mixed $arg */
        foreach ($args as $key => $arg) {
            $val = is_string($arg) ? sprintf("'%s'", $arg) : (string) $arg;
            $argList[$key] = "{$key}: $val";
        }

        /** @var class-string $class */
        $class = $attr->getName();
        $shortName = (new ReflectionClass($class))->getShortName();

        return sprintf('#[%s(%s)]', $shortName, implode(', ', $argList));
    }
}
