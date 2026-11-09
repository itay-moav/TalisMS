<?php

declare(strict_types=1);

namespace Talis\commons;

interface iFilter
{
    /**
     * Modifies the given Request in place (it is passed by reference); returns nothing.
     */
    public function filter(\Talis\Message\Request $Request): void;
}
