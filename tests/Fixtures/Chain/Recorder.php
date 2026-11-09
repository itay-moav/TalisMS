<?php

declare(strict_types=1);

namespace Talis\Tests\Fixtures\Chain;

/**
 * Shared log of which fixture links ran, in order.
 */
final class Recorder
{
    /**
     * @var list<string>
     */
    public static array $ran = [];

    public static function reset(): void
    {
        self::$ran = [];
    }

    public static function record(string $name): void
    {
        self::$ran[] = $name;
    }

    /**
     * Callable entry in 'Class::method' form.
     *
     * @param array<mixed> $params
     */
    public static function staticEntry(\Talis\Message\Request $Request, \Talis\Message\Response $Response, array $params): void
    {
        self::record('static');
    }
}
