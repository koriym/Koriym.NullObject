<?php

declare(strict_types=1);

namespace Koriym\NullObject;

use Koriym\NullObject\Exception\FileNotWritable;
use Koriym\NullObject\Exception\LogicException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

use function class_exists;
use function dirname;
use function file_put_contents;
use function implode;
use function interface_exists;
use function is_dir;
use function is_string;
use function mkdir;
use function rename;
use function sprintf;
use function tempnam;
use function unlink;

use const PHP_EOL;
use const PHP_VERSION_ID;

final class NullObject implements NullObjectInterface
{
    private const CLASS_TEMPLATE = <<<EOT
%s
class %s implements \%s
{
%s
}
EOT;

    /**
     * {@inheritDoc}
     */
    public function newInstance(string $interface): object
    {
        $generated = $this->generate($interface);
        eval($generated->code);

        /** @psalm-suppress MixedMethodCall */
        return new ($generated->class)();
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

        $ns = $class->getNamespaceName();
        $shortNullClassName = $class->getShortName() . 'Null';
        /** @var class-string $fqClassName */
        $fqClassName = $class->getName() . 'Null';
        $nsSyntax = $ns ? sprintf("namespace %s;\n", $ns) : '';
        $code = sprintf(self::CLASS_TEMPLATE, $nsSyntax, $shortNullClassName, $interface, $this->getMethods($interface));

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
        $this->filePutContets($filePath, $generated->phpCode());
        /** @psalm-suppress UnresolvableInclude */
        require $filePath;

        return $generated->class;
    }

    /**
     * @param class-string $interface
     */
    private function getMethods(string $interface): string
    {
        $methods = (new ReflectionClass($interface))->getMethods();
        $methodStrings = [];
        foreach ($methods as $method) {
            $methodMeta = $this->getMethodMeta($method);
            $params = $method->getParameters();
            $paramList = $this->getParamList($params);
            $return = $this->getReturn($method);
            $methodStrings[] = sprintf("%s\n    public function %s(%s)%s {}", $methodMeta, $method->getName(), $paramList, $return);
        }

        return implode(PHP_EOL, $methodStrings);
    }

    private function getMethodMeta(ReflectionMethod $method): string
    {
        $attr =  PHP_VERSION_ID >= 80000 ? $this->getAttributes($method) : '';

        return sprintf("    %s\n    %s", $method->getDocComment(), $attr);
    }

    /**
     * @param array<ReflectionParameter> $params
     */
    private function getParamList(array $params): string
    {
        $paramStrings = [];
        foreach ($params as $param) {
            $paramType = $param->getType();
            $hint = $paramType instanceof ReflectionNamedType ? $paramType->getName() : '';
            $name = $param->getName();
            /** @var mixed $defaultValue */
            $defaultValue = $param->isDefaultValueAvailable() ? sprintf(' = %s', (string) $param->getDefaultValue()) : '';
            $default = $defaultValue ? sprintf(' = %s', (string) $defaultValue) : '';
            $paramStrings[] = sprintf('%s $%s%s', $hint, $name, $default);
        }

        return implode(', ', $paramStrings);
    }

    private function getReturn(ReflectionMethod $method): string
    {
        if (! $method->hasReturnType()) {
            return '';
        }

        $returnType = $method->getReturnType();

        $returnTypeString =  $returnType instanceof ReflectionNamedType ? $returnType->getName() : '';

        return ': ' . (class_exists($returnTypeString) ? '\\' . $returnTypeString : $returnTypeString);
    }

    private function getAttributes(ReflectionMethod $method): string
    {
        /** @var list<ReflectionAttribute> $attrs */
        $attrs = $method->getAttributes();
        $attrList = [];
        if ($attrs) {
            foreach ($attrs as $attr) {
                /** @var array<float|int|string> $args */
                $args = $attr->getArguments();
                $argList = [];
                /** @var mixed $arg */
                foreach ($args as $arg) {
                    $argList[] = is_string($arg) ? sprintf("'%s'", $arg) : (string) $arg;
                }

                $attrList[] = sprintf('#[\%s(%s)]', (string) $attr->getName(), implode(', ', $argList));
            }
        }

        return implode(PHP_EOL, $attrList);
    }

    public function filePutContets(string $filename, string $content): void
    {
        $dir = dirname($filename);
        ! is_dir($dir) && mkdir($dir, 0777, true);
        $tmpFile = tempnam(dirname($filename), 'swap');
        if (is_string($tmpFile) && file_put_contents($tmpFile, $content) && @rename($tmpFile, $filename)) {
            return;
        }

        // @codeCoverageIgnoreStart
        @unlink((string) $tmpFile);

        throw new FileNotWritable($filename);
        // @codeCoverageIgnoreEnd
    }
}
