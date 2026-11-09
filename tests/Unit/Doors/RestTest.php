<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Doors;

use PHPUnit\Framework\Attributes\DataProvider;
use Talis\Doors\HTTP;
use Talis\Doors\Rest;
use Talis\Message\Renderers\HTTP as HTTPRenderer;
use Talis\Message\ResponseType;
use Talis\Tests\Fixtures\Doors\RecordingRest;

final class RestTest extends DoorTestCase
{
    private function door(): RecordingRest
    {
        return new RecordingRest($this->emitter);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function methods(): array
    {
        return [
            'POST' => ['POST', 'create'],
            'PUT' => ['PUT', 'update'],
            'PATCH' => ['PATCH', 'partialupdate'],
            'DELETE' => ['DELETE', 'delete'],
            'HEAD' => ['HEAD', 'head'],
            'OPTIONS' => ['OPTIONS', 'options'],
            'GET' => ['GET', 'read'],
            'TRACE' => ['TRACE', 'read'],
            'lower case post' => ['post', 'create'],
        ];
    }

    #[DataProvider('methods')]
    public function testMethodMapping(string $httpMethod, string $action): void
    {
        $_SERVER['REQUEST_URI'] = '/user/x';
        $_SERVER['REQUEST_METHOD'] = $httpMethod;
        $door = $this->door();

        $door->gogogo('');

        self::assertSame(['', 'user', 'x', $action], $door->seenParts);
    }

    public function testMissingRequestMethodIsRead(): void
    {
        $_SERVER['REQUEST_URI'] = '/user/x';
        unset($_SERVER['REQUEST_METHOD']);
        $door = $this->door();

        $door->gogogo('');

        self::assertSame('read', $door->seenParts[3]);
    }

    public function testActionInsertedAsThirdSegment(): void
    {
        $_SERVER['REQUEST_URI'] = '/user/x';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $door = $this->door();

        $door->gogogo('');

        self::assertSame(['', 'user', 'x', 'create'], $door->seenParts);
    }

    public function testExtraSegmentsFollowAction(): void
    {
        $_SERVER['REQUEST_URI'] = '/user/x/id/5';
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $door = $this->door();

        $door->gogogo('');

        self::assertSame(['', 'user', 'x', 'update', 'id', '5'], $door->seenParts);
    }

    public function testRootUriStrippedBeforeSplice(): void
    {
        $_SERVER['REQUEST_URI'] = '/api/user/x/id/5?z=1';
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $door = $this->door();

        $door->gogogo('/api');

        self::assertSame(['', 'user', 'x', 'delete', 'id', '5'], $door->seenParts);
        self::assertSame('/user/x/id/5?z=1', $door->seenUri);
    }

    public function testTrailingSlashAndQueryRouteTheSame(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $_SERVER['REQUEST_URI'] = '/api/fix/ping/';
        $withSlash = $this->door();
        $slashResponse = $withSlash->gogogo('/api');

        $_SERVER['REQUEST_URI'] = '/api/fix/ping?x=1';
        $withQuery = $this->door();
        $queryResponse = $withQuery->gogogo('/api');

        self::assertSame(['', 'fix', 'ping', 'read'], $withSlash->seenParts);
        self::assertSame($withSlash->seenParts, $withQuery->seenParts);
        self::assertSame(ResponseType::Response, $slashResponse->getResponseType());
        self::assertSame($slashResponse->getResponseType(), $queryResponse->getResponseType());
        self::assertCount(2, $this->emitter->emitted);
    }

    public function testUriOutsideRootIs404(): void
    {
        // '/api' in an unexpected place: the URI is left as is and matches no route
        $_SERVER['REQUEST_URI'] = '/x/api/fix/ping';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $Response = $this->door()->gogogo('/api');

        self::assertSame(404, self::code($Response));
    }

    public function testRestDefaultsAreInheritedFromHttp(): void
    {
        $door = new Rest();
        self::assertInstanceOf(HTTPRenderer::class, (new \ReflectionProperty(HTTP::class, 'emitter'))->getValue($door));

        $_SERVER['REQUEST_URI'] = '/fix/ping';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $recording = $this->door();
        $recording->input = '{bad';

        self::assertSame(400, self::code($recording->gogogo('')));
    }
}
