<?php

declare(strict_types=1);

namespace Demo\User\Links;

use Talis\Chain\aChainLink;

/**
 * Adds a user. The payload is the new user.
 */
final class CreateUser extends aUserLink
{
    #[\Override]
    public function process(): aChainLink
    {
        $this->Response->setPayload($this->store()->create($this->nameParam(), $this->emailParam()));
        return $this;
    }
}
