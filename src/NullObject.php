<?php

declare(strict_types=1);

namespace Koriym\NullObject;

use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

use function class_exists;
use function file_put_contents;
use function filemtime;
use function implode;
use function interface_exists;
use function is_string;
use function sprintf;
use function str_replace;

use const PHP_EOL;

final class NullObject
{
    private const CLASS_TEMPLATE = <<<EOT
<?php
namespace %s;

class %s implements \%s
{
%s
}
EOT;
    /** @var string */
    private $tmpDir;

    public function __construct(string $tmpDir)
    {
        $this->tmpDir = $tmpDir;
    }

    /**
     * @phpstan-param class-string $interface
     *
     * @pslam-param interface-string $interface
     */
    public function __invoke(string $interface): ?string
    {
        $class = new ReflectionClass($interface);
        if (! interface_exists($class->getName())) {
            return null;
        }

        $ns = $class->getNamespaceName();
        $className = $class->getShortName() . 'Null';
        if (! class_exists($className, false)) {
            $class = sprintf(self::CLASS_TEMPLATE, $ns, $className, $interface, $this->getMethods($interface));
            $file = $this->getNullFilePath($interface);
            file_put_contents($file, $class);

            require $file;
        }

        return $className;
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
            $methodStrings[] = sprintf("%s\npublic function %s(%s): %s {}", $methodMeta, $method->getName(), $paramList, $method->getReturnType());
        }

        return implode(PHP_EOL, $methodStrings);
    }

    private function getMethodMeta(ReflectionMethod $method): string
    {
        $attrs = $method->getAttributes();
        $attrList = [];
        if ($attrs) {
            foreach ($attrs as $attr) {
                $args = $attr->getArguments();
                $argList = [];
                foreach ($args as $arg) {
                    $argList[] = is_string($arg) ? sprintf("'%s'", $arg) : $arg;
                }

                $attrList[] = sprintf('#[\%s(%s)]', $attr->getName(), implode(', ', $argList));
            }
        }

        $attr = implode(PHP_EOL, $attrList);

        return sprintf("%s\n%s", $method->getDocComment(), $attr);
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

    /**
     * @param class-string $interfaceName
     */
    public function getNullFilePath(string $interfaceName): string
    {
        $fileName = (string) (new ReflectionClass($interfaceName))->getFileName();

        return sprintf(
            '%s/%s_%s.php',
            $this->tmpDir,
            str_replace('\\', '_', $interfaceName),
            filemtime($fileName)
        );
    }
}
