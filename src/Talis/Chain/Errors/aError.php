<?php

declare(strict_types=1);

namespace Talis\Chain\Errors;

/**
 * Base for a chain link that ends the chain with an error response.
 */
abstract class aError extends \Talis\Chain\aChainLink implements \Talis\commons\iRenderable
{
    protected int $httpCode = 500;

    abstract protected function formatHumanMessage(): string;

    /**
     * This is an end of the line chain link, return itself.
     */
    #[\Override]
    public function process(): \Talis\Chain\aChainLink
    {
        return $this;
    }

    #[\Override]
    public function render(\Talis\commons\iEmitter $emitter): void
    {
        \Talis\TalisMain::logger()->error($this->params, false);
        $message = $this->formatHumanMessage();
        \Talis\TalisMain::logger()->error($message, true);

        $this->Response->setMessage($message);
        $this->Response->setStatus($this->buildStatus());
        $this->Response->markError();
        $emitter->emit($this->Response);
    }

    /**
     * Builds the status for httpCode. Falls back to a 500 (and logs) when
     * there is no status class for it. Override to build a custom status.
     */
    protected function buildStatus(): \Talis\Message\aStatus
    {
        $statusClass = '\Talis\Message\Status\Code' . $this->httpCode;
        if (!is_a($statusClass, \Talis\Message\aStatus::class, true)) {
            \Talis\TalisMain::logger()->error("No status class for HTTP code {$this->httpCode}, using 500", false);
            return new \Talis\Message\Status\Code500();
        }

        /** @var class-string<\Talis\Message\aStatus> $statusClass */
        return new $statusClass();
    }
}
