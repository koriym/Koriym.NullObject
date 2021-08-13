<?php

declare(strict_types=1);

namespace Koriym\NullObject;

use function sprintf;
use function str_replace;

final class GeneratedCode
{
    /** @var class-string */
    public $class;

    /** @var string */
    public $code;

    /**
     * @param class-string $class
     */
    public function __construct(string $class, string $code)
    {
        $this->class = $class;
        $this->code = $code;
    }

    public function filePath(string $scriptDir): string
    {
        return sprintf(
            '%s/%s.php',
            $scriptDir,
            str_replace('\\', '_', $this->class)
        );
    }

    public function phpCode(): string
    {
        return sprintf("<?php\n" . $this->code);
    }
}
