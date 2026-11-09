<?php

declare(strict_types=1);

namespace Demo\User\Links;

use Talis\Chain\aChainLink;

/**
 * Finds one user by the id GET param. The payload is the user.
 */
final class ReadUser extends aUserLink
{
    #[\Override]
    public function process(): aChainLink
    {
        $id = $this->requestedId();
        $user = $this->store()->find($id);
        if ($user === null) {
            return $this->notFound($id);
        }
        $this->Response->setPayload($user);
        return $this;
    }
}
