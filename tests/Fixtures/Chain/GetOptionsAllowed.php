<?php

declare(strict_types=1);

namespace Talis\Tests\Fixtures\Chain;

use Talis\Chain\OptionsAllowed;

final class GetOptionsAllowed extends OptionsAllowed
{
    #[\Override]
    protected function allowed(): string
    {
        return 'GET, OPTIONS';
    }
}
