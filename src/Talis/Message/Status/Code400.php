<?php namespace Talis\Message\Status;
class Code400 extends \Talis\Message\aStatus{
    /**
     * @var int
     */
    protected int $code = 400;
    
    /**
     * @var string
     */
    protected string $msg = 'Bad Request';
}