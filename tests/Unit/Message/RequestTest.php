<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Message;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Talis\Exception\InvalidParam;
use Talis\Exception\ParamNotFound;
use Talis\Message\Request;

final class RequestTest extends TestCase
{
    public function testConstructorDefaultsBodyToEmptyStdClass(): void
    {
        $request = new Request('/a/b/c', []);

        $body = $request->getBody();

        self::assertSame([], get_object_vars($body));
    }

    public function testConstructorKeepsGivenBody(): void
    {
        $body = new \stdClass();

        $request = new Request('/a/b/c', [], $body);

        self::assertSame($body, $request->getBody());
    }

    public function testConstructorDefaultsGetParamsToEmptyArray(): void
    {
        $request = new Request('/x');

        self::assertSame([], $request->getAllGetParams());
    }

    public function testGetUriAndGetAllGetParams(): void
    {
        $params = ['a' => '1', 'b' => ['x', 'y']];

        $request = new Request('/a/b/c', $params);

        self::assertSame('/a/b/c', $request->getUri());
        self::assertSame($params, $request->getAllGetParams());
    }

    public function testSetHeaderAppendsInOrderAndReturnsSameInstance(): void
    {
        $request = new Request('/x', []);

        $returned = $request->setHeader('A: 1')->setHeader('B: 2');

        self::assertSame(['A: 1', 'B: 2'], $request->getHeaders());
        self::assertSame($request, $returned);
    }

    public function testSetBodyReplacesBodyAndReturnsIt(): void
    {
        $request = new Request('/x', []);
        $body = new \stdClass();

        $returned = $request->setBody($body);

        self::assertSame($body, $returned);
        self::assertSame($body, $request->getBody());
    }

    public function testGetParamNullReturnsString(): void
    {
        $request = new Request('/x', ['id' => '5']);

        self::assertSame('5', $request->getParamNull('id'));
    }

    public function testGetParamNullReturnsNullWhenMissingOrNull(): void
    {
        $request = new Request('/x', ['a' => null]);

        self::assertNull($request->getParamNull('missing'));
        self::assertNull($request->getParamNull('a'));
    }

    /**
     * @return iterable<string, array{mixed, string}>
     */
    public static function scalarValues(): iterable
    {
        yield 'int' => [5, '5'];
        yield 'float' => [1.5, '1.5'];
        yield 'true' => [true, '1'];
        yield 'false' => [false, ''];
    }

    #[DataProvider('scalarValues')]
    public function testGetParamNullCastsScalars(mixed $value, string $expected): void
    {
        $request = new Request('/x', ['a' => $value]);

        self::assertSame($expected, $request->getParamNull('a'));
    }

    public function testGetParamDefaultReturnsValue(): void
    {
        $request = new Request('/x', ['a' => 'x']);

        self::assertSame('x', $request->getParamDefault('a', 'd'));
    }

    public function testGetParamDefaultReturnsDefaultWhenMissingOrNull(): void
    {
        $request = new Request('/x', ['a' => null]);

        self::assertSame('d', $request->getParamDefault('missing', 'd'));
        self::assertSame('d', $request->getParamDefault('a', 'd'));
    }

    public function testGetParamExistsReturnsValue(): void
    {
        $request = new Request('/x', ['a' => 'x', 'n' => 7]);

        self::assertSame('x', $request->getParamExists('a'));
        self::assertSame('7', $request->getParamExists('n'));
    }

    public function testGetParamExistsThrowsWhenMissing(): void
    {
        $request = new Request('/x', []);

        $this->expectException(ParamNotFound::class);

        $request->getParamExists('a');
    }

    public function testGetParamOrFailReturnsValue(): void
    {
        $request = new Request('/x', ['a' => 'x']);

        self::assertSame('x', $request->getParamOrFail('a'));
    }

    public function testGetParamOrFailThrowsWhenMissing(): void
    {
        $request = new Request('/x', []);

        $this->expectException(ParamNotFound::class);

        $request->getParamOrFail('a');
    }

    public function testGetParamOrFailThrowsWhenNull(): void
    {
        $request = new Request('/x', ['a' => null]);

        $this->expectException(ParamNotFound::class);

        $request->getParamOrFail('a');
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function scalarGetterMethods(): iterable
    {
        yield 'getParamNull' => ['getParamNull'];
        yield 'getParamDefault' => ['getParamDefault'];
        yield 'getParamExists' => ['getParamExists'];
        yield 'getParamOrFail' => ['getParamOrFail'];
    }

    #[DataProvider('scalarGetterMethods')]
    public function testScalarGettersThrowOnArrayValue(string $method): void
    {
        $request = new Request('/x', ['a' => ['x', 'y']]);

        try {
            if ($method === 'getParamDefault') {
                $request->getParamDefault('a', 'd');
            } else {
                $request->$method('a');
            }
            self::fail('Expected InvalidParam to be thrown');
        } catch (InvalidParam $e) {
            self::assertStringContainsString('a', $e->getMessage());
            self::assertStringContainsString('array', $e->getMessage());
        }
    }

    public function testArrayGetParamThrowsInvalidParam(): void
    {
        // Simulates a request built from a URL like ?id[]=1
        $request = new Request('/x', ['id' => ['1']]);

        $this->expectException(InvalidParam::class);

        $request->getParamNull('id');
    }

    public function testScalarGettersThrowOnObjectValue(): void
    {
        $request = new Request('/x', ['a' => new \stdClass()]);

        $this->expectException(InvalidParam::class);

        $request->getParamNull('a');
    }

    public function testGetBodyParamsCreatesAndStoresEmptyParamsWhenMissing(): void
    {
        $request = new Request('/x', []);

        $params = $request->getBodyParams();

        self::assertSame([], get_object_vars($params));
        self::assertSame($params, $request->getBody()->params);
    }

    public function testGetBodyParamsCreatesParamsWhenNull(): void
    {
        $body = new \stdClass();
        $body->params = null;
        $request = new Request('/x', [], $body);

        $params = $request->getBodyParams();

        self::assertSame([], get_object_vars($params));
    }

    public function testGetBodyParamsReturnsExistingObject(): void
    {
        $body = new \stdClass();
        $body->params = (object) ['a' => 1];
        $request = new Request('/x', [], $body);

        self::assertSame($body->params, $request->getBodyParams());
    }

    public function testGetBodyParamsCastsJsonArrayToObject(): void
    {
        $body = json_decode('{"params":[]}');
        $request = new Request('/x', [], $body);

        $params = $request->getBodyParams();

        self::assertSame([], get_object_vars($params));
        self::assertSame($params, $request->getBody()->params);
    }

    public function testGetBodyParamsCastsArrayToObjectWithValues(): void
    {
        $body = new \stdClass();
        $body->params = ['a' => 1];
        $request = new Request('/x', [], $body);

        $params = $request->getBodyParams();

        self::assertSame(1, $params->a);
    }

    public function testGetBodyParamsThrowsWhenParamsIsScalar(): void
    {
        $body = new \stdClass();
        $body->params = 'x';
        $request = new Request('/x', [], $body);

        $this->expectException(InvalidParam::class);

        $request->getBodyParams();
    }

    public function testGetBodyParamReturnsValue(): void
    {
        $body = (object) ['params' => (object) ['a' => 1]];
        $request = new Request('/x', [], $body);

        self::assertSame(1, $request->getBodyParam('a'));
    }

    public function testGetBodyParamReturnsDefaultWhenMissingOrNull(): void
    {
        $body = (object) ['params' => (object) ['a' => null]];
        $request = new Request('/x', [], $body);

        self::assertSame('d', $request->getBodyParam('missing', 'd'));
        self::assertSame('d', $request->getBodyParam('a', 'd'));
        self::assertNull($request->getBodyParam('missing'));
    }

    public function testGetBodyParamExistsReturnsValueIncludingNull(): void
    {
        $body = (object) ['params' => (object) ['a' => null, 'b' => 0]];
        $request = new Request('/x', [], $body);

        self::assertNull($request->getBodyParamExists('a'));
        self::assertSame(0, $request->getBodyParamExists('b'));
    }

    public function testGetBodyParamExistsThrowsWhenMissing(): void
    {
        $request = new Request('/x', []);

        $this->expectException(ParamNotFound::class);

        $request->getBodyParamExists('a');
    }

    public function testGetBodyParamOrFailReturnsValue(): void
    {
        $body = (object) ['params' => (object) ['a' => 'v']];
        $request = new Request('/x', [], $body);

        self::assertSame('v', $request->getBodyParamOrFail('a'));
    }

    public function testGetBodyParamOrFailThrowsWhenMissing(): void
    {
        $request = new Request('/x', []);

        $this->expectException(ParamNotFound::class);

        $request->getBodyParamOrFail('a');
    }

    public function testGetBodyParamOrFailThrowsWhenNull(): void
    {
        $body = (object) ['params' => (object) ['a' => null]];
        $request = new Request('/x', [], $body);

        $this->expectException(ParamNotFound::class);

        $request->getBodyParamOrFail('a');
    }

    public function testAddToBodyParamsSetsAndReturnsValue(): void
    {
        $request = new Request('/x', []);

        $returned = $request->addToBodyParams('a', 'v');

        self::assertSame('v', $returned);
        self::assertSame('v', $request->getBodyParam('a'));
    }

    public function testSerializeShape(): void
    {
        $request = new Request('/x', ['a' => '1'], (object) ['params' => (object) ['a' => 1]]);
        $request->setHeader('A: 1');

        $data = $request->__serialize();

        self::assertEquals(['headers', 'fullUri', 'body', 'getParams'], array_keys($data));
        self::assertEquals(true, is_string($data['body']));
        self::assertEquals(true, is_string($data['getParams']));
    }

    public function testSerializeRoundTrip(): void
    {
        $request = new Request('/x/y', ['a' => '1'], (object) ['params' => (object) ['a' => 1]]);
        $request->setHeader('A: 1');

        /** @var Request $restored */
        $restored = unserialize(serialize($request));

        self::assertSame($request->getHeaders(), $restored->getHeaders());
        self::assertSame($request->getUri(), $restored->getUri());
        self::assertSame($request->getAllGetParams(), $restored->getAllGetParams());
        self::assertEquals($request->getBody(), $restored->getBody());
    }

    public function testUnserializeTurnsNonStdClassObjectsInBodyIntoIncompleteClass(): void
    {
        $body = new \stdClass();
        $body->weird = new \ArrayObject(['x']);
        $request = new Request('/x', [], $body);

        /** @var Request $restored */
        $restored = unserialize(serialize($request));

        self::assertInstanceOf(\__PHP_Incomplete_Class::class, $restored->getBody()->weird);
    }

    public function testUnserializeTurnsObjectsInGetParamsIntoIncompleteClass(): void
    {
        $request = new Request('/x', ['a' => new \stdClass()]);

        /** @var Request $restored */
        $restored = unserialize(serialize($request));

        self::assertInstanceOf(\__PHP_Incomplete_Class::class, $restored->getAllGetParams()['a']);
    }

    public function testUnserializeRejectsForeignClasses(): void
    {
        $bodyWithForeignObject = new \stdClass();
        $bodyWithForeignObject->inner = new \ArrayObject(['x']);
        $request = new Request('/x', ['p' => new \stdClass()], $bodyWithForeignObject);

        /** @var Request $restored */
        $restored = unserialize(serialize($request));

        // Top-level body stays a stdClass; foreign classes inside it do not survive as-is.
        self::assertInstanceOf(\__PHP_Incomplete_Class::class, $restored->getBody()->inner);
        // getParams allows no classes at all, including stdClass.
        self::assertInstanceOf(\__PHP_Incomplete_Class::class, $restored->getAllGetParams()['p']);
    }

    /**
     * @return iterable<string, array{array<array-key, mixed>}>
     */
    public static function badUnserializeData(): iterable
    {
        yield 'missing headers' => [[
            'fullUri' => '/x',
            'body' => serialize(new \stdClass()),
            'getParams' => serialize([]),
        ]];
        yield 'headers not a list of strings' => [[
            'headers' => [1, 2],
            'fullUri' => '/x',
            'body' => serialize(new \stdClass()),
            'getParams' => serialize([]),
        ]];
        yield 'fullUri not a string' => [[
            'headers' => [],
            'fullUri' => 123,
            'body' => serialize(new \stdClass()),
            'getParams' => serialize([]),
        ]];
        yield 'body not a string' => [[
            'headers' => [],
            'fullUri' => '/x',
            'body' => new \stdClass(),
            'getParams' => serialize([]),
        ]];
        yield 'body string that is not a stdClass' => [[
            'headers' => [],
            'fullUri' => '/x',
            'body' => serialize([1]),
            'getParams' => serialize([]),
        ]];
        yield 'getParams not a string' => [[
            'headers' => [],
            'fullUri' => '/x',
            'body' => serialize(new \stdClass()),
            'getParams' => [],
        ]];
        yield 'getParams string that is not an array' => [[
            'headers' => [],
            'fullUri' => '/x',
            'body' => serialize(new \stdClass()),
            'getParams' => serialize('nope'),
        ]];
    }

    /**
     * @param array<array-key, mixed> $data
     */
    #[DataProvider('badUnserializeData')]
    public function testUnserializeRejectsBadData(array $data): void
    {
        $request = (new \ReflectionClass(Request::class))->newInstanceWithoutConstructor();

        $this->expectException(\UnexpectedValueException::class);

        $request->__unserialize($data);
    }
}
