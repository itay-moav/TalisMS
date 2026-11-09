<?php

declare(strict_types=1);

namespace Api;

use Demo\User\Dependencies\ValidEmail;
use Demo\User\Links\CreateUser;
use Talis\Chain\aFilteredValidatedChainLink;
use Talis\Chain\Dependencies\BodyParamsFieldExist;
use Talis\Chain\ResourceCreated;
use Talis\Message\Request;

/**
 * POST /demo/user with body {"params": {"name": "...", "email": "..."}}
 * Answers 201 with the new user.
 */
final class DemoUserCreate extends aFilteredValidatedChainLink
{
    #[\Override]
    protected array $dependencies = [
        [BodyParamsFieldExist::class, ['field' => 'name']],
        [BodyParamsFieldExist::class, ['field' => 'email']],
        [ValidEmail::class, []],
    ];

    #[\Override]
    protected function getNextBl(): array
    {
        return [
            // A plain function can be a chain link too.
            [static function (Request $Request): void {
                $name = $Request->getBodyParam('name');
                if (is_string($name)) {
                    $Request->addToBodyParams('name', trim($name));
                }
            }, []],
            [CreateUser::class, []],
            [ResourceCreated::class, []],
        ];
    }
}
