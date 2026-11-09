<?php

declare(strict_types=1);

namespace Demo\User\Dependencies;

use Talis\Chain\Dependencies\aDependency;
use Talis\commons\iEmitter;
use Talis\Message\Response;
use Talis\Message\Status\Code422;

/**
 * Checks that body->params->email looks like an email address.
 * An app-made dependency: extend aDependency, write validate() and render().
 */
final class ValidEmail extends aDependency
{
    #[\Override]
    protected function validate(): bool
    {
        $email = $this->Request->getBodyParam('email');
        return is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    #[\Override]
    public function render(iEmitter $emitter): void
    {
        $email = $this->Request->getBodyParam('email');
        $shown = is_string($email) ? $email : get_debug_type($email);

        $response = new Response();
        $response->markDependency();
        $response->setMessage("Email [{$shown}] is not valid");
        $response->setStatus(new Code422());
        $emitter->emit($response);
    }
}
