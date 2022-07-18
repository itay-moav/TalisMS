<?php namespace Talis\Message\Status;
/**
 * User is logged in, but is not authorized here
 * 
 * @author itay
 *
 */
class Code403 extends \Talis\Message\aStatus{
    
    /**
     * @var int
     */
    protected int $code = 403;
    
    /**
     * @var string
     */
    protected string $msg = 'Forbbiden';
}
