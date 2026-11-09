<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Chain;

use PHPUnit\Framework\TestCase;
use Talis\Chain\FunctionalChainlinkWrapper;
use Talis\Message\Request;
use Talis\Message\Response;
use Talis\Tests\Fixtures\Chain\LinkA;

final class FunctionalChainlinkWrapperTest extends TestCase
{
    public function testProcessCallsFunctionWithRequestResponseParams(): void
    {
        $Request = new Request('/x');
        $Response = new Response();
        $seen = [];
        $wrapper = new FunctionalChainlinkWrapper(
            static function (Request $q, Response $r, array $p) use (&$seen): void {
                $seen = [$q, $r, $p];
            },
            $Request,
            $Response,
            ['a' => 1],
        );

        $wrapper->process();

        self::assertSame([$Request, $Response, ['a' => 1]], $seen);
    }

    public function testProcessReturnsSelf(): void
    {
        $wrapper = new FunctionalChainlinkWrapper(static fn (): null => null, new Request('/x'), new Response());

        self::assertSame($wrapper, $wrapper->process());
    }

    public function testTwoParamClosureWorks(): void
    {
        $Response = new Response();
        $wrapper = new FunctionalChainlinkWrapper(
            fn (Request $Request, Response $Response): mixed => $Response->setPayload('set'),
            new Request('/x'),
            $Response,
            ['extra'],
        );

        $wrapper->process();

        self::assertSame('set', $Response->getPayload());
    }

    public function testReturnValueIsIgnored(): void
    {
        $wrapper = new FunctionalChainlinkWrapper(
            static fn (Request $q, Response $r): LinkA => new LinkA($q, $r),
            new Request('/x'),
            new Response(),
        );

        self::assertSame($wrapper, $wrapper->process());
    }

    public function testDefaultParamsIsEmptyArray(): void
    {
        $seen = null;
        $wrapper = new FunctionalChainlinkWrapper(
            static function (Request $q, Response $r, array $p) use (&$seen): void {
                $seen = $p;
            },
            new Request('/x'),
            new Response(),
        );

        $wrapper->process();

        self::assertSame([], $seen);
    }
}
