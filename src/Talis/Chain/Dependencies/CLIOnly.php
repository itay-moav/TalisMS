<?php

declare(strict_types=1);

namespace Talis\Chain\Dependencies;

/**
 * Making sure this is run from the command line (the Cli/CliEmbedded doors,
 * a daemon worker, or any other CLI script).
 */
class CLIOnly extends aDependency
{
    #[\Override]
    protected function validate(): bool
    {
        $valid = $this->sapi() === 'cli';

        \Talis\TalisMain::logger()->debug('validator ' . self::class);
        \Talis\TalisMain::logger()->debug("Am I using CLI door? [{$valid}]");

        return $valid;
    }

    /**
     * The current SAPI name. A seam: tests override it to simulate a web request.
     */
    protected function sapi(): string
    {
        return PHP_SAPI;
    }

    #[\Override]
    public function render(\Talis\commons\iEmitter $emitter): void
    {
        $response = new \Talis\Message\Response();
        $response->markDependency();
        $response->setMessage('Accessible only from the command line (cli)');
        $response->setStatus(new \Talis\Message\Status\Code403());
        $emitter->emit($response);
    }
}
