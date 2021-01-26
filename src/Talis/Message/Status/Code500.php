<?php namespace Talis\Message\Status;
class Code500 extends \Talis\Message\aStatus{
    /**
     * @var int
     */
    protected int $code = 500;
    
    /**
     * @var string
     */
    protected string $msg = 'Server Error';
}