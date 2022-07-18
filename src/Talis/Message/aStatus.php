<?php namespace Talis\Message;
abstract class aStatus{
    
    /**
     * @var int
     */
    protected int $code;
    
    /**
     * @var string
     */
    protected string $msg;
    
    /**
     * @var string
     */
    protected string $dyn_error_msg = '';
	
    /**
     * 
     * @return int
     */
	public function getCode():int{
		return $this->code;
	}

	/**
	 * 
	 * @return string
	 */
	public function getMsg():string{
		return $this->msg;
	}
	
	/**
	 * @param string $msg
	 * @return string
	 */
	public function dynamic_message(string $msg):string{
		$this->dyn_error_msg = $msg;
		return $this->dyn_error_msg;
	}
}