<?php

declare(strict_types=1);

namespace Talis\Tests\Fixtures\Chain\Errors;

use Talis\Chain\Errors\aError;
use Talis\commons\iEmitter;
use Talis\Message\Status\Code422;

/**
 * An error fixture that overrides render() entirely, the way consumer apps
 * add custom statuses aError does not ship.
 */
final class CustomRenderError extends aError
{
    protected int $httpCode = 0;

    #[\Override]
    protected function formatHumanMessage(): string
    {
        return 'custom render error';
    }

    #[\Override]
    public function render(iEmitter $emitter): void
    {
        $this->Response->setMessage($this->formatHumanMessage());
        $this->Response->setStatus(new Code422());
        $this->Response->markError();
        $emitter->emit($this->Response);
    }
}
