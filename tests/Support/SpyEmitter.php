<?php

declare(strict_types=1);

namespace Talis\Tests\Support;

use Talis\commons\iEmitter;
use Talis\Message\Response;

/**
 * Test double that records every emitted Response instead of sending it anywhere.
 */
final class SpyEmitter implements iEmitter
{
    /**
     * @var list<Response>
     */
    public array $emitted = [];

    #[\Override]
    public function emit(Response $message): void
    {
        $this->emitted[] = $message;
    }

    public function last(): Response
    {
        $last = end($this->emitted);

        if ($last === false) {
            throw new \RuntimeException('No response was emitted yet.');
        }

        return $last;
    }
}
