<?php

declare(strict_types=1);

namespace Talis\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Talis\Chain\Errors\ApiNotFound;
use Talis\commons\NullLogger;
use Talis\Context;
use Talis\Exception\LastLinkNotRenderable;
use Talis\Message\Request;
use Talis\Router\DefaultRouter;
use Talis\TalisMain;
use Talis\Tests\Fixtures\Router\FixedHead;
use Talis\Tests\Fixtures\Router\FixedRouter;
use Talis\Tests\Support\SpyEmitter;

final class TalisMainTest extends TestCase
{
    private const string GOOD_URI = '/fix/ping/read';

    /**
     * @var array<array-key, mixed>
     */
    private array $savedGet = [];

    protected function setUp(): void
    {
        $this->savedGet = $_GET;
        $_GET = [];
        $this->resetStatics();
    }

    protected function tearDown(): void
    {
        $_GET = $this->savedGet;
        $this->resetStatics();
        TalisMain::setLogger(new NullLogger());
    }

    private function resetStatics(): void
    {
        (new \ReflectionProperty(TalisMain::class, 'logger'))->setValue(null, null);
        TalisMain::$appPath = '';
        TalisMain::$registeredRouter = DefaultRouter::class;
        TalisMain::$registeredInitFunc = null;
    }

    private function useFixtureApp(): void
    {
        TalisMain::$appPath = __DIR__ . '/../Fixtures/app';
    }

    /**
     * @return list<string>
     */
    private static function parts(string $uri): array
    {
        return explode('/', $uri);
    }

    public function testLoggerFallbackIsCached(): void
    {
        self::assertSame(TalisMain::logger(), TalisMain::logger());
    }

    public function testDefaults(): void
    {
        self::assertSame('', TalisMain::$appPath);
        self::assertSame(DefaultRouter::class, TalisMain::$registeredRouter);
        self::assertNull(TalisMain::$registeredInitFunc);
    }

    public function testBeginReturnsSameInstance(): void
    {
        $this->useFixtureApp();
        $main = new TalisMain();

        self::assertSame($main, $main->begin(self::parts(self::GOOD_URI), null, self::GOOD_URI));
    }

    public function testBeginResetsContext(): void
    {
        $this->useFixtureApp();
        $old = new Context();
        $old->set('x', 1);
        TalisMain::$context = $old;

        (new TalisMain())->begin(self::parts(self::GOOD_URI), null, self::GOOD_URI);

        self::assertNotSame($old, TalisMain::$context);
        self::assertFalse(TalisMain::$context->has('x'));
    }

    public function testGoodRouteReturnsRenderableLastLink(): void
    {
        $this->useFixtureApp();

        $link = (new TalisMain())->begin(self::parts(self::GOOD_URI), null, self::GOOD_URI)->nextLinkInChain();

        // aChainLink&iRenderable is guaranteed by the return type
        self::assertInstanceOf(\Api\FixPingRead::class, $link);
    }

    public function testRequestGetsQueryParamsBodyAndUri(): void
    {
        $this->useFixtureApp();
        $uri = '/fix/ping/read/id/5';
        $body = (object) ['params' => (object) ['a' => 1]];

        $link = (new TalisMain())->begin(self::parts($uri), $body, $uri)->nextLinkInChain();

        self::assertInstanceOf(\Api\FixPingRead::class, $link);
        $Request = $link->getRequest();
        self::assertSame($uri, $Request->getUri());
        self::assertSame('5', $Request->getParamOrFail('id'));
        self::assertSame($body, $Request->getBody());
    }

    public function testQueryStringParamsComeFromGetSuperGlobal(): void
    {
        $this->useFixtureApp();
        $_GET = ['q' => 'z'];

        $link = (new TalisMain())->begin(self::parts(self::GOOD_URI), null, self::GOOD_URI)->nextLinkInChain();

        self::assertInstanceOf(\Api\FixPingRead::class, $link);
        self::assertSame(['q' => 'z'], $link->getRequest()->getAllGetParams());
    }

    public function testInitFuncRunsWithRequest(): void
    {
        $this->useFixtureApp();
        /** @var list<Request> $seen */
        $seen = [];
        TalisMain::$registeredInitFunc = static function (Request $Request) use (&$seen): void {
            $seen[] = $Request;
        };

        $link = (new TalisMain())->begin(self::parts(self::GOOD_URI), null, self::GOOD_URI)->nextLinkInChain();

        self::assertCount(1, $seen);
        self::assertSame(self::GOOD_URI, $seen[0]->getUri());
        self::assertInstanceOf(\Api\FixPingRead::class, $link);
        self::assertSame($link->getRequest(), $seen[0]);
    }

    public function testInitFuncSkippedWhenNull(): void
    {
        $this->useFixtureApp();

        $link = (new TalisMain())->begin(self::parts(self::GOOD_URI), null, self::GOOD_URI)->nextLinkInChain();

        self::assertInstanceOf(\Api\FixPingRead::class, $link);
    }

    public function testBadUriGivesApiNotFoundHead(): void
    {
        $link = (new TalisMain())->begin(['', 'a'], null, '/a')->nextLinkInChain();

        self::assertInstanceOf(ApiNotFound::class, $link);
        $link->render(new SpyEmitter());
        self::assertSame(404, $link->getResponse()->getStatus()->getCode());
        self::assertStringContainsString('/a', $link->getResponse()->getMessage());
    }

    public function testInitFuncNotCalledOnBadUri(): void
    {
        $calls = 0;
        TalisMain::$registeredInitFunc = static function () use (&$calls): void {
            $calls++;
        };

        (new TalisMain())->begin(['', 'a'], null, '/a');

        self::assertSame(0, $calls);
    }

    public function testUsesRegisteredRouter(): void
    {
        TalisMain::$registeredRouter = FixedRouter::class;

        $link = (new TalisMain())->begin(['', 'any'], null, '/any')->nextLinkInChain();

        self::assertInstanceOf(FixedHead::class, $link);
    }

    public function testExceptionFromInitFuncPropagates(): void
    {
        $this->useFixtureApp();
        TalisMain::$registeredInitFunc = static function (): void {
            throw new \RuntimeException('init failed');
        };

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('init failed');
        (new TalisMain())->begin(self::parts(self::GOOD_URI), null, self::GOOD_URI);
    }

    public function testNonRenderableLastLinkThrows(): void
    {
        $this->useFixtureApp();
        $main = (new TalisMain())->begin(self::parts('/fix/ping/plain'), null, '/fix/ping/plain');

        $this->expectException(LastLinkNotRenderable::class);
        $this->expectExceptionMessage('Api\\FixPingPlain');
        $main->nextLinkInChain();
    }
}
