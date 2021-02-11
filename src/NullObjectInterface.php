<?php

declare(strict_types=1);

namespace Koriym\NullObject;

interface NullObjectInterface
{
    /**
     * @param class-string $interface
     */
    public function newInstance(string $interface): object;

    /**
     * @phpstan-param class-string $interface
     *
     * @pslam-param interface-string $interface
     */
    public function generate(string $interface): GeneratedCode;

    /**
     * @param class-string $interface
     *
     * @return class-string
     */
    public function save(string $interface, string $scriptDir): string;
}
