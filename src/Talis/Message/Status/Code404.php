<?php namespace Talis\Message\Status;
class Code404 extends \Talis\Message\aStatus{
    /**
     * @var int
     */
    protected int $code = 404;
    
    /**
     * @var string
     */
    protected string $msg = 'Not Found';
}