<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Chain;

use PHPUnit\Framework\TestCase;
use Talis\Chain\aChainLink;
use Talis\Chain\ChainContainer;
use Talis\Chain\DoneSuccessful;
use Talis\Chain\Errors\ApiNotFound;
use Talis\Chain\FunctionalChainlinkWrapper;
use Talis\Exception\InvalidChainEntry;
use Talis\Message\Request;
use Talis\Message\Response;
use Talis\Message\Status\Code200;
use Talis\TalisMain;
use Talis\Tests\Fixtures\Chain\AppendingLink;
use Talis\Tests\Fixtures\Chain\BailingLink;
use Talis\Tests\Fixtures\Chain\BailingSuccessLink;
use Talis\Tests\Fixtures\Chain\ClearingLink;
use Talis\Tests\Fixtures\Chain\CloningLink;
use Talis\Tests\Fixtures\Chain\DivertingLink;
use Talis\Tests\Fixtures\Chain\LinkA;
use Talis\Tests\Fixtures\Chain\LinkB;
use Talis\Tests\Fixtures\Chain\LinkC;
use Talis\Tests\Fixtures\Chain\ProbeLink;
use Talis\Tests\Fixtures\Chain\Recorder;
use Talis\Tests\Fixtures\Chain\RequestReplacingLink;
use Talis\Tests\Support\SpyLogger;

final class aChainLinkTest extends TestCase
{
    private Request $Request;

    private Response $Response;

    protected function setUp(): void
    {
        Recorder::reset();
        ProbeLink::$last = null;
        $this->Request = new Request('/test');
        $this->Response = new Response();
    }

    protected function tearDown(): void
    {
        TalisMain::setLogger(new \Talis\commons\NullLogger());
    }

    /**
     * Builds a head link that has $entries waiting in its container.
     *
     * @param array<mixed> $entries raw entries, may be malformed on purpose
     */
    private function headWith(array $entries): ProbeLink
    {
        $container = new ChainContainer();
        (new \ReflectionProperty(ChainContainer::class, 'chainLinks'))->setValue($container, $entries);
        $head = new ProbeLink($this->Request, $this->Response);
        $head->setChainContainer($container);
        return $head;
    }

    public function testConstructorStoresRequestResponseParams(): void
    {
        $link = new ProbeLink($this->Request, $this->Response, ['x' => 1]);

        self::assertSame($this->Response, $link->getResponse());
        self::assertSame($this->Request, $link->request());
        self::assertSame(['x' => 1], $link->params());
    }

    public function testNewLinkHasEmptyContainer(): void
    {
        $link = new ProbeLink($this->Request, $this->Response);

        self::assertSame($link, $link->nextLinkInChain());
        self::assertTrue($link->container()->isEmpty());
    }

    public function testSetChainContainerIsUsedForTraversal(): void
    {
        $link = new ProbeLink($this->Request, $this->Response);
        $link->setChainContainer(new ChainContainer([[LinkB::class, []]]));

        self::assertInstanceOf(LinkB::class, $link->nextLinkInChain());
        self::assertSame(['probe', 'B'], Recorder::$ran);
    }

    public function testAppendChainLinkPushesClassAndReturnsSelf(): void
    {
        $link = new ProbeLink($this->Request, $this->Response);

        self::assertSame($link, $link->appendChainLink(LinkB::class));
        self::assertInstanceOf(LinkB::class, $link->nextLinkInChain());
        self::assertSame(['probe', 'B'], Recorder::$ran);
    }

    public function testAppendChainLinkAcceptsCallable(): void
    {
        $link = new ProbeLink($this->Request, $this->Response);
        $link->appendChainLink(static function (Request $Request, Response $Response, array $params): void {
            Recorder::record('closure:' . (is_string($params[0] ?? null) ? $params[0] : ''));
        }, ['p']);

        self::assertInstanceOf(FunctionalChainlinkWrapper::class, $link->nextLinkInChain());
        self::assertSame(['probe', 'closure:p'], Recorder::$ran);
    }

    public function testAppendDuringProcessRunsAtEndOfChain(): void
    {
        $head = new ProbeLink($this->Request, $this->Response);
        $head->setChainContainer(new ChainContainer([[AppendingLink::class, []], [LinkA::class, []]]));

        self::assertInstanceOf(LinkC::class, $head->nextLinkInChain());
        self::assertSame(['probe', 'appending', 'A', 'C'], Recorder::$ran);
    }

    public function testCloneResponseReturnsEqualCopy(): void
    {
        $link = new ProbeLink($this->Request, $this->Response);

        $copy = $link->cloneResponse();

        self::assertEquals($link->getResponse(), $copy);
        self::assertNotSame($link->getResponse(), $copy);
    }

    public function testBailOutClearsChainAndReturnsProcessedError(): void
    {
        $head = new ProbeLink($this->Request, $this->Response);
        $head->setChainContainer(new ChainContainer([[BailingLink::class, []], [LinkB::class, []]]));

        $result = $head->nextLinkInChain();

        self::assertInstanceOf(ApiNotFound::class, $result);
        self::assertSame(BailingLink::$error, $result);
        self::assertSame(['probe', 'bailing'], Recorder::$ran);
    }

    public function testBailOutAcceptsSuccessLink(): void
    {
        $head = new ProbeLink($this->Request, $this->Response);
        $head->setChainContainer(new ChainContainer([[BailingSuccessLink::class, []], [LinkB::class, []]]));

        $result = $head->nextLinkInChain();

        self::assertInstanceOf(DoneSuccessful::class, $result);
        self::assertInstanceOf(Code200::class, $result->getResponse()->getStatus());
        self::assertSame(['probe', 'bailing-success'], Recorder::$ran);
    }

    public function testBailOutWithoutContainerWorks(): void
    {
        $link = new BailingLink($this->Request, $this->Response);

        self::assertInstanceOf(ApiNotFound::class, $link->process());
    }

    public function testBailOutWithoutContainer(): void
    {
        $link = new BailingSuccessLink($this->Request, $this->Response);

        self::assertInstanceOf(DoneSuccessful::class, $link->nextLinkInChain());
    }

    public function testNextLinkInChainRunsLinksInOrder(): void
    {
        $head = new LinkA($this->Request, $this->Response);
        $head->setChainContainer(new ChainContainer([[LinkB::class, []], [LinkC::class, []]]));

        self::assertInstanceOf(LinkC::class, $head->nextLinkInChain());
        self::assertSame(['A', 'B', 'C'], Recorder::$ran);
    }

    public function testDivertedLinkStopsChain(): void
    {
        $head = new DivertingLink($this->Request, $this->Response);
        $head->setChainContainer(new ChainContainer([[LinkA::class, []]]));

        $result = $head->nextLinkInChain();

        self::assertSame(DivertingLink::$other, $result);
        self::assertSame(['diverting'], Recorder::$ran);
    }

    public function testReturnedOtherLinkReplacesRestOfChain(): void
    {
        $head = new LinkA($this->Request, $this->Response);
        $head->setChainContainer(new ChainContainer([[DivertingLink::class, []], [LinkB::class, []]]));

        $result = $head->nextLinkInChain();

        self::assertSame(DivertingLink::$other, $result);
        self::assertSame(['A', 'diverting'], Recorder::$ran);
    }

    public function testEqualButNotIdenticalLinkStopsChain(): void
    {
        $head = new CloningLink($this->Request, $this->Response);
        $head->setChainContainer(new ChainContainer([[LinkA::class, []]]));

        $result = $head->nextLinkInChain();

        self::assertSame(CloningLink::$copy, $result);
        self::assertNotSame($head, $result);
        self::assertSame(['cloning'], Recorder::$ran);
    }

    public function testNextLinkReceivesSameContainer(): void
    {
        $container = new ChainContainer([[ProbeLink::class, []], [ClearingLink::class, []], [LinkB::class, []]]);
        $head = new LinkA($this->Request, $this->Response);
        $head->setChainContainer($container);

        self::assertInstanceOf(ClearingLink::class, $head->nextLinkInChain());
        self::assertSame($container, ProbeLink::$last?->container());
        self::assertSame(['A', 'probe', 'clearing'], Recorder::$ran);
    }

    public function testNextLinkReceivesReplacedRequest(): void
    {
        $head = new RequestReplacingLink($this->Request, $this->Response);
        $head->setChainContainer(new ChainContainer([[ProbeLink::class, []]]));

        $head->nextLinkInChain();

        self::assertSame('/replaced', ProbeLink::$last?->request()->getUri());
        self::assertSame($this->Response, ProbeLink::$last->getResponse());
    }

    public function testCallableEntryIsWrapped(): void
    {
        $seen = [];
        $head = $this->headWith([[
            static function (Request $Request, Response $Response, array $params) use (&$seen): void {
                $seen = [$Request, $Response, $params];
            },
            ['k' => 'v'],
        ]]);

        self::assertInstanceOf(FunctionalChainlinkWrapper::class, $head->nextLinkInChain());
        self::assertSame([$this->Request, $this->Response, ['k' => 'v']], $seen);
    }

    public function testTwoParamClosureEntryWorks(): void
    {
        $head = new ProbeLink($this->Request, $this->Response);
        $head->setChainContainer(new ChainContainer([[
            static function (Request $Request, Response $Response): void {
                Recorder::record('two-params');
            },
            ['ignored'],
        ]]));

        $head->nextLinkInChain();

        self::assertSame(['probe', 'two-params'], Recorder::$ran);
    }

    public function testClosureWithTwoParamsStillWorks(): void
    {
        $head = new ProbeLink($this->Request, $this->Response);
        $head->appendChainLink(fn (Request $q, Response $r): mixed => $r->setMessage('from closure'));

        $result = $head->nextLinkInChain();

        self::assertInstanceOf(FunctionalChainlinkWrapper::class, $result);
        self::assertSame('from closure', $this->Response->getMessage());
    }

    public function testCallableStringEntryIsWrapped(): void
    {
        $head = $this->headWith([[Recorder::class . '::staticEntry', []]]);

        self::assertInstanceOf(FunctionalChainlinkWrapper::class, $head->nextLinkInChain());
        self::assertSame(['probe', 'static'], Recorder::$ran);
    }

    public function testEntryNotArrayThrows(): void
    {
        $head = $this->headWith(['not an array']);

        $this->expectException(InvalidChainEntry::class);
        $head->nextLinkInChain();
    }

    public function testEntryMissingParamsThrows(): void
    {
        $head = $this->headWith([[LinkB::class]]);

        $this->expectException(InvalidChainEntry::class);
        $head->nextLinkInChain();
    }

    public function testEntryParamsNotArrayThrows(): void
    {
        $head = $this->headWith([[LinkB::class, 'x']]);

        $this->expectException(InvalidChainEntry::class);
        $head->nextLinkInChain();
    }

    public function testEntryNonLinkClassThrows(): void
    {
        $head = $this->headWith([[\stdClass::class, []]]);

        $this->expectException(InvalidChainEntry::class);
        $head->nextLinkInChain();
    }

    public function testEntryAbstractLinkClassThrows(): void
    {
        $head = $this->headWith([[aChainLink::class, []]]);

        $this->expectException(InvalidChainEntry::class);
        $this->expectExceptionMessage('abstract');
        $head->nextLinkInChain();
    }

    public function testEntryUnknownStringThrows(): void
    {
        $head = $this->headWith([['NoSuchThing', []]]);

        $this->expectException(InvalidChainEntry::class);
        $head->nextLinkInChain();
    }

    public function testBadEntryThrowsInvalidChainEntry(): void
    {
        $head = $this->headWith([['NotAClass', []]]);

        $this->expectException(InvalidChainEntry::class);
        $this->expectExceptionMessage('NotAClass');
        $head->nextLinkInChain();
    }

    public function testEntryIntThrows(): void
    {
        $head = $this->headWith([[42, []]]);

        $this->expectException(InvalidChainEntry::class);
        $this->expectExceptionMessage('int');
        $head->nextLinkInChain();
    }

    public function testParamStringReturnsValue(): void
    {
        $link = new ProbeLink($this->Request, $this->Response, ['field' => 'id', 0 => 'zero']);

        self::assertSame('id', $link->readParamString('field'));
        self::assertSame('zero', $link->readParamString(0));
    }

    public function testParamStringMissingThrows(): void
    {
        $link = new ProbeLink($this->Request, $this->Response);

        $this->expectException(InvalidChainEntry::class);
        $this->expectExceptionMessage('Param [field] for [' . ProbeLink::class . '] is missing or not a string');
        $link->readParamString('field');
    }

    public function testParamStringNonStringThrows(): void
    {
        $link = new ProbeLink($this->Request, $this->Response, ['field' => 5]);

        $this->expectException(InvalidChainEntry::class);
        $link->readParamString('field');
    }

    public function testNextLinkInChainLogsDebug(): void
    {
        $logger = new SpyLogger();
        TalisMain::setLogger($logger);
        $head = new LinkA($this->Request, $this->Response);
        $head->appendChainLink(LinkB::class);

        $head->nextLinkInChain();

        $debug = array_column(array_filter($logger->calls, static fn (array $c): bool => $c['level'] === 'debug'), 'inp');
        self::assertContains('About to process: [' . LinkA::class . ']', $debug);
        self::assertContains('About to process: [' . LinkB::class . ']', $debug);
    }
}
