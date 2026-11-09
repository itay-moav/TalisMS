<?php

declare(strict_types=1);

namespace Demo\User\Links;

use Talis\Chain\aChainLink;

/**
 * Changes the name and/or email of the user with the id GET param.
 * Other body params are ignored. The payload is the updated user.
 */
final class UpdateUser extends aUserLink
{
    #[\Override]
    public function process(): aChainLink
    {
        $id = $this->requestedId();

        $changes = [];
        if ($this->hasBodyParam('name')) {
            $changes['name'] = $this->nameParam();
        }
        if ($this->hasBodyParam('email')) {
            $changes['email'] = $this->emailParam();
        }

        $user = $this->store()->update($id, $changes);
        if ($user === null) {
            return $this->notFound($id);
        }
        $this->Response->setPayload($user);
        return $this;
    }
}
