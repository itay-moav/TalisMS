<?php

declare(strict_types=1);

namespace Talis\Tests\Fixtures\Doors;

use Talis\Message\Response;

/**
 * Records what a door passes to dispatch(), then runs the real dispatch().
 */
trait RecordsDispatch
{
    public int $dispatchCalls = 0;

    /**
     * @var list<string>
     */
    public array $seenParts = [];

    public ?\stdClass $seenBody = null;

    public string $seenUri = '';

    /**
     * @param list<string> $requestParts
     */
    #[\Override]
    protected function dispatch(array $requestParts, ?\stdClass $body, string $fullUri): Response
    {
        $this->dispatchCalls++;
        $this->seenParts = $requestParts;
        $this->seenBody = $body;
        $this->seenUri = $fullUri;
        return parent::dispatch($requestParts, $body, $fullUri);
    }
}
