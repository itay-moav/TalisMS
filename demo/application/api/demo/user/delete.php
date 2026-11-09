<?php

declare(strict_types=1);

namespace Api;

use Demo\User\Links\DeleteUser;
use Talis\Chain\aFilteredValidatedChainLink;
use Talis\Chain\Dependencies\GetFieldUInt;
use Talis\Chain\ResourceDeleted;

/**
 * DELETE /demo/user/id/5. 404 when there is no such user.
 */
final class DemoUserDelete extends aFilteredValidatedChainLink
{
    #[\Override]
    protected array $dependencies = [
        [GetFieldUInt::class, ['field' => 'id']],
    ];

    #[\Override]
    protected function getNextBl(): array
    {
        return [
            [DeleteUser::class, []],
            [ResourceDeleted::class, []],
        ];
    }
}
