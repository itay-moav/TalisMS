<?php

declare(strict_types=1);

namespace Api;

use Demo\User\Links\ListUsers;
use Demo\User\Links\ReadUser;
use Talis\Chain\aFilteredValidatedChainLink;
use Talis\Chain\Dependencies\GetFieldUInt;
use Talis\Chain\DoneSuccessful;
use Talis\Chain\Filters\FormPostIntoGetOverride;

/**
 * GET /demo/user lists every user; GET /demo/user/id/5 reads one.
 */
final class DemoUserRead extends aFilteredValidatedChainLink
{
    // Lets a plain HTML form POST the id (id=5) to /http/demo/user/read:
    // the filter copies $_POST into the GET params before ReadUser runs.
    protected array $filters = [
        [FormPostIntoGetOverride::class, []],
    ];

    #[\Override]
    protected function getNextBl(): array
    {
        // This list is built before the filter runs, so look at $_POST here too.
        if (!isset($this->Request->getAllGetParams()['id']) && !isset($_POST['id'])) {
            return [
                [ListUsers::class, []],
                [DoneSuccessful::class, []],
            ];
        }

        return [
            [GetFieldUInt::class, ['field' => 'id']],
            [ReadUser::class, []],
            [DoneSuccessful::class, []],
        ];
    }
}
