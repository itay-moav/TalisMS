<?php

declare(strict_types=1);

namespace Talis\Tests\Fixtures\Doors;

/**
 * Feeds the HTTP body from a property instead of php://input.
 */
trait FakeInput
{
    public string $input = '';

    public int $readInputCalls = 0;

    #[\Override]
    protected function readInput(): string
    {
        $this->readInputCalls++;
        return $this->input;
    }
}
