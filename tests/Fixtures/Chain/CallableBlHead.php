<?php

declare(strict_types=1);

namespace Talis\Tests\Fixtures\Chain;

use Talis\Chain\aFilteredValidatedChainLink;
use Talis\Message\Request;
use Talis\Message\Response;

final class CallableBlHead extends aFilteredValidatedChainLink
{
    #[\Override]
    protected function getNextBl(): array
    {
        return [[
            static function (Request $Request, Response $Response): void {
                $payload = $Response->getPayload();
                if ($payload instanceof \stdClass) {
                    $payload->fromClosure = 'yes';
                }
            },
            [],
        ]];
    }
}
