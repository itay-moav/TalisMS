<?php

declare(strict_types=1);

namespace Talis\Tests\Fixtures\Chain;

use Talis\Chain\PayloadToString200;

/**
 * Captures headers instead of sending them.
 */
final class CapturingString200 extends PayloadToString200
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
