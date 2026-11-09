<?php

declare(strict_types=1);

namespace Demo\User\Links;

use Talis\Chain\aChainLink;

/**
 * Deletes the user with the id GET param. The payload is ['deleted' => id].
 */
final class DeleteUser extends aUserLink
{
    #[\Override]
    public function process(): aChainLink
    {
        $id = $this->requestedId();
        if (!$this->store()->delete($id)) {
            return $this->notFound($id);
        }
        $this->Response->setPayload(['deleted' => $id]);
        return $this;
    }
}
