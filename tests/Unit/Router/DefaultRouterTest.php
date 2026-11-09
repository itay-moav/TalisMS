<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Router;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Talis\Exception\BadUri;
use Talis\Message\Request;
use Talis\Message\Response;
use Talis\Router\DefaultRouter;
use Talis\TalisMain;

final class DefaultRouterTest extends TestCase
{
    #[\Override]
    protected function setUp(): void
    {
        TalisMain::$appPath = dirname(__DIR__, 2) . '/Fixtures/app';
    }

    #[\Override]
    protected function tearDown(): void
    {
        TalisMain::$appPath = '';
    }

    public function testFewerThanFourPartsThrowsBadUri(): void
    {
        $router = new DefaultRouter(['', 'a', 'b'], []);

        $this->expectException(BadUri::class);
        $this->expectExceptionMessage('/a/b');
        $router->generateRoute();
    }

    public function testDotDotSegmentIsBadUri(): void
    {
        $router = new DefaultRouter(['', '..', 'x', 'read'], []);

        $this->expectException(BadUri::class);
        $router->generateRoute();
    }

    /**
     * @return iterable<string, array{list<string>}>
     */
    public static function badSegments(): iterable
    {
        foreach (['..', '', 'a.b', 'a-b', 'a b', '%2e%2e', "\u{e9}"] as $bad) {
            foreach ([1, 2, 3] as $position) {
                $parts = ['', 'a', 'b', 'c'];
                $parts[$position] = $bad;
                yield "[{$bad}] at {$position}" => [$parts];
            }
        }
    }

    /**
     * @param list<string> $parts
     */
    #[DataProvider('badSegments')]
    public function testInvalidRouteSegmentThrowsBadUri(array $parts): void
    {
        $router = new DefaultRouter($parts, []);

        $this->expectException(BadUri::class);
        $router->generateRoute();
    }

    public function testSegmentWithTrailingNewlineIsBadUri(): void
    {
        $router = new DefaultRouter(['', 'fix', 'ping', "ping\n"], []);

        $this->expectException(BadUri::class);
        $router->generateRoute();
    }

    public function testValidSegmentsAccepted(): void
    {
        $router = new DefaultRouter(['', 'Ab_1', 'c', 'D9'], []);
        $router->generateRoute();

        $this->expectException(BadUri::class);
        $this->expectExceptionMessage('/api/Ab_1/c/D9.php');
        $router->getChainHead(new Request('/x'), new Response());
    }

    public function testRootSegmentNotValidated(): void
    {
        $router = new DefaultRouter(['../x', 'fix', 'ping', 'read'], []);
        $router->generateRoute();

        self::assertInstanceOf(\Api\FixPingRead::class, $router->getChainHead(new Request('/x'), new Response()));
    }

    public function testGenerateQueryPairsExtraSegments(): void
    {
        $router = new DefaultRouter(['', 'a', 'b', 'c', 'id', '5', 'name', 'x'], []);

        self::assertSame(['id' => '5', 'name' => 'x'], $router->generateQuery());
    }

    public function testExtraSegmentsAreNotValidated(): void
    {
        $parts = explode('/', '/a/b/read/slice/340b-encounters/types/Initial%20Examination');
        $router = new DefaultRouter($parts, []);
        $router->generateRoute();

        self::assertSame(
            ['slice' => '340b-encounters', 'types' => 'Initial%20Examination'],
            $router->generateQuery(),
        );
    }

    public function testExtraSegmentsNotValidated(): void
    {
        $router = new DefaultRouter(['', 'a', 'b', 'c', 'x', '-', 'y', 'a,b', 'z', 'a%20b'], []);

        self::assertSame(['x' => '-', 'y' => 'a,b', 'z' => 'a%20b'], $router->generateQuery());
    }

    public function testTrailingKeyGetsEmptyString(): void
    {
        $router = new DefaultRouter(['', 'a', 'b', 'c', 'flag'], []);

        self::assertSame(['flag' => ''], $router->generateQuery());
    }

    public function testNoExtrasGivesEmptyArray(): void
    {
        $router = new DefaultRouter(['', 'a', 'b', 'c'], []);

        self::assertSame([], $router->generateQuery());
    }

    public function testGetParamsOverridePathParams(): void
    {
        $router = new DefaultRouter(['', 'a', 'b', 'c', 'id', '5'], ['id' => '9', 'q' => 'z']);

        self::assertSame(['id' => '9', 'q' => 'z'], $router->generateQuery());
    }

    public function testNumericKeysKept(): void
    {
        $router = new DefaultRouter(['', 'a', 'b', 'c', '7', 'seven'], [3 => 'x']);

        self::assertSame([7 => 'seven', 3 => 'x'], $router->generateQuery());
    }

    public function testChainHeadBeforeRouteThrowsBadUri(): void
    {
        $router = new DefaultRouter(['', 'fix', 'ping', 'read'], []);

        $this->expectException(BadUri::class);
        $router->getChainHead(new Request('/x'), new Response());
    }

    public function testMissingFileThrowsBadUri(): void
    {
        $router = new DefaultRouter(['', 'fix', 'ping', 'missing'], []);
        $router->generateRoute();

        $this->expectException(BadUri::class);
        $this->expectExceptionMessage('/api/fix/ping/missing.php');
        $router->getChainHead(new Request('/x'), new Response());
    }

    public function testFileWithoutClassThrowsBadUri(): void
    {
        $router = new DefaultRouter(['', 'fix', 'ping', 'noclass'], []);
        $router->generateRoute();

        $this->expectException(BadUri::class);
        $this->expectExceptionMessage('Api\\fixpingnoclass');
        $router->getChainHead(new Request('/x'), new Response());
    }

    public function testClassNotChainLinkIsBadUri(): void
    {
        $router = new DefaultRouter(['', 'fix', 'ping', 'notlink'], []);
        $router->generateRoute();

        $this->expectException(BadUri::class);
        $this->expectExceptionMessage('Api\\fixpingnotlink');
        $router->getChainHead(new Request('/x'), new Response());
    }

    public function testReturnsChainHeadWithRequestAndResponse(): void
    {
        $Request = new Request('/fix/ping/read');
        $Response = new Response();
        $router = new DefaultRouter(['', 'fix', 'ping', 'read'], []);
        $router->generateRoute();

        $head = $router->getChainHead($Request, $Response);

        self::assertInstanceOf(\Api\FixPingRead::class, $head);
        self::assertSame($Response, $head->getResponse());
        self::assertSame($Request, $head->getRequest());
    }
}
