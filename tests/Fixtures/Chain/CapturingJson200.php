<?php

declare(strict_types=1);

namespace Talis\Tests\Fixtures\Chain;

use Talis\Chain\PayloadToJson200;

/**
 * Captures headers instead of sending them.
 */
final class CapturingJson200 extends PayloadToJson200
{
    /**
     * @var list<string>
     */
    public array $headers = [];

    #[\Override]
    protected function sendHeader(string $header): void
    {
        $this->headers[] = $header;
    }
}
