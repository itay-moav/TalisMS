<?php

declare(strict_types=1);

namespace Api;

use Talis\Chain\OptionsAllowed;

/**
 * OPTIONS /demo/user: lists the methods the user resource takes.
 */
final class DemoUserOptions extends OptionsAllowed
{
    #[\Override]
    protected function allowed(): string
    {
        return 'OPTIONS,GET,POST,PUT,DELETE';
    }
}
