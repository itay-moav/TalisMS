<?php

declare(strict_types=1);

namespace Talis\Tests\Fixtures\Chain;

use Talis\Chain\Dependencies\aDependency;
use Talis\commons\iEmitter;

final class FailingDependency extends aDependency
{
    #[\Override]
    protected function validate(): bool
    {
        Recorder::record('dependency');
        return false;
    }

    #[\Override]
    public function render(iEmitter $emitter): void
    {
        $emitter->emit($this->Response);
    }
}
