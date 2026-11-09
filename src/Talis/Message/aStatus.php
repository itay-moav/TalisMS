<?php

declare(strict_types=1);

namespace Talis\Message;

abstract class aStatus
{
    protected int $code;

    protected string $msg;

    public function getCode(): int
    {
        return $this->code;
    }

    public function getMsg(): string
    {
        return $this->msg;
    }
}
