<?php
declare(strict_types=1);

namespace Thorm;

final class Globals
{
    /** @var array<string, string> */
    private static array $map = [];

    public static function declare(string $name, string $source): void
    {
        self::$map[$name] = $source;
    }

    /**
     * @return array<string, string>
     */
    public static function all(): array
    {
        return self::$map;
    }
}
