<?php namespace Talis\Message\Status;
class Code401 extends \Talis\Message\aStatus{
    /**
     * @var int
     */
    protected int $code = 401;
    
    /**
     * @var string
     */
    protected string $msg = 'Unauthorized';
}