<?php

declare(strict_types=1);

namespace Demo\User\Links;

use Talis\Chain\aChainLink;

/**
 * The payload is ['users' => every user].
 */
final class ListUsers extends aUserLink
{
    #[\Override]
    public function process(): aChainLink
    {
        $this->Response->setPayload(['users' => $this->store()->all()]);
        return $this;
    }
}
