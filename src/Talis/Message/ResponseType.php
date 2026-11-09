<?php

declare(strict_types=1);

namespace Talis\Message;

/**
 * What kind of outcome a Response carries.
 */
enum ResponseType: string
{
    case Response = 'response';
    case Dependency = 'dependency';
    case Error = 'error';
}
