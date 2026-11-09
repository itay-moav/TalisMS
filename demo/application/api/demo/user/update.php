<?php

declare(strict_types=1);

namespace Api;

use Demo\User\Links\UpdateUser;
use Talis\Chain\aFilteredValidatedChainLink;
use Talis\Chain\Dependencies\GetFieldUInt;
use Talis\Chain\Dependencies\HasBody;
use Talis\Chain\ResourceUpdated;
use Talis\Message\Request;

/**
 * PUT /demo/user/id/5 with body {"params": {"name": "...", "email": "..."}}
 * Either field may be left out. 404 when there is no such user.
 */
final class DemoUserUpdate extends aFilteredValidatedChainLink
{
    #[\Override]
    protected array $dependencies = [
        [GetFieldUInt::class, ['field' => 'id']],
        [HasBody::class, []],
    ];

    #[\Override]
    protected function getNextBl(): array
    {
        return [
            // Same name trimming as DemoUserCreate.
            [static function (Request $Request): void {
                $name = $Request->getBodyParam('name');
                if (is_string($name)) {
                    $Request->addToBodyParams('name', trim($name));
                }
            }, []],
            [UpdateUser::class, []],
            [ResourceUpdated::class, []],
        ];
    }
}
