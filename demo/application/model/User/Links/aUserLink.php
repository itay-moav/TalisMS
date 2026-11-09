<?php

declare(strict_types=1);

namespace Demo\User\Links;

use Demo\User\Errors\UserNotFound;
use Demo\User\UserStore;
use Talis\Chain\aChainLink;
use Talis\Exception\InvalidParam;
use Talis\TalisMain;

/**
 * Shared helpers for the user links: the store, the id and the body params.
 * An InvalidParam thrown here becomes a 400 in the door.
 */
abstract class aUserLink extends aChainLink
{
    /**
     * The bootstrap init func puts the store in the Context.
     */
    protected function store(): UserStore
    {
        $store = TalisMain::$context->get('userStore');
        if (!$store instanceof UserStore) {
            throw new \LogicException('No userStore in the Context. Did the demo bootstrap run?');
        }
        return $store;
    }

    /**
     * The id GET param. The GetFieldUInt dependency already checked it.
     */
    protected function requestedId(): int
    {
        return (int) $this->Request->getParamOrFail('id');
    }

    protected function notFound(int $id): aChainLink
    {
        return $this->bailOut(new UserNotFound($this->Request, $this->Response, ['id' => $id]));
    }

    protected function hasBodyParam(string $key): bool
    {
        return property_exists($this->Request->getBodyParams(), $key);
    }

    protected function nameParam(): string
    {
        $name = $this->Request->getBodyParam('name');
        if (!is_string($name) || $name === '') {
            throw new InvalidParam('name', 'must be a non empty string');
        }
        return $name;
    }

    protected function emailParam(): string
    {
        $email = $this->Request->getBodyParam('email');
        if (!is_string($email) || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidParam('email', 'is not a valid email');
        }
        return $email;
    }
}
