<?php

declare(strict_types=1);

namespace Talis\Tests\Fixtures\Chain;

use Talis\Chain\aChainLink;
use Talis\Chain\Errors\ApiNotFound;

final class BailingLink extends aChainLink
{
    public static ?ApiNotFound $error = null;

    #[\Override]
    public function process(): aChainLink
    {
        Recorder::record('bailing');
        self::$error = new ApiNotFound($this->Request, $this->Response);
        return $this->bailOut(self::$error);
    }
}
