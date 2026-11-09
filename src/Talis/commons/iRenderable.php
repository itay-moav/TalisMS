<?php

declare(strict_types=1);

namespace Talis\commons;

/**
 * The last link of a chain implements this, so the Response gets sent.
 */
interface iRenderable
{
    public function render(\Talis\commons\iEmitter $emitter): void;
}
