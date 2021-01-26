<?php namespace Talis\Message\Status;
class Code422 extends \Talis\Message\aStatus{
    /**
     * @var int
     */
    protected int $code = 422;
    
    /**
     * @var string
     */
    protected string $msg = 'Unprocessable Entity';
}