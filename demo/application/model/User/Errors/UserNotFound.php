<?php

declare(strict_types=1);

namespace Demo\User\Errors;

use Talis\Chain\Errors\aError;

/**
 * 404 for a user id that is not in the store. Expects params ['id' => int].
 */
final class UserNotFound extends aError
{
    protected int $httpCode = 404;

    #[\Override]
    protected function formatHumanMessage(): string
    {
        $id = $this->params['id'] ?? null;
        $shown = is_int($id) || is_string($id) ? (string) $id : get_debug_type($id);
        return "User [{$shown}] not found";
    }
}
