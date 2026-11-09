<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Message\Renderers;

use PHPUnit\Framework\TestCase;
use Talis\Message\Renderers\Cli;
use Talis\Message\Response;
use Talis\Message\Status\Code200;

final class CliTest extends TestCase
{
    public function testEmitPrintsPrettyJsonAndFinLine(): void
    {
        $response = new Response();
        $response->setStatus(new Code200());
        $cli = new Cli();

        ob_start();
        $cli->emit($response);
        $output = ob_get_clean();

        self::assertSame(
            json_encode($response->getBody(), JSON_PRETTY_PRINT) . "\nFIN with [200]\n",
            $output
        );
    }

    public function testEmitWithoutStatusPrints500(): void
    {
        $response = new Response();
        $cli = new Cli();

        ob_start();
        $cli->emit($response);
        $output = ob_get_clean();

        self::assertNotFalse($output);
        self::assertStringEndsWith("\nFIN with [500]\n", $output);
    }

    public function testEmitThrowsJsonExceptionAndPrintsNothing(): void
    {
        $response = new Response();
        $response->setPayload(INF);
        $cli = new Cli();

        ob_start();
        try {
            $cli->emit($response);
            self::fail('Expected a JsonException to be thrown.');
        } catch (\JsonException) {
        }
        $output = ob_get_clean();

        self::assertSame('', $output);
    }
}
