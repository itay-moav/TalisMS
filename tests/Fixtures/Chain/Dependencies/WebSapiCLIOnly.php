<?php

declare(strict_types=1);

namespace Talis\Tests\Fixtures\Chain\Dependencies;

use Talis\Chain\Dependencies\CLIOnly;

/**
 * Simulates running under a web SAPI, to test CLIOnly's failure path.
 */
final class WebSapiCLIOnly extends CLIOnly
{
    #[\Override]
    protected function sapi(): string
    {
        return 'fpm-fcgi';
    }
}
