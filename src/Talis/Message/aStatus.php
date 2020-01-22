<?php namespace Talis\Message;
abstract class aStatus{
	protected	$code          = '',
				$msg           = '',
				$dyn_error_msg = ''
	;
	
	public function getCode():int{
		return $this->code;
	}

	public function getMsg():string{
		return $this->msg;
	}
	
	public function dynamic_message($msg){
		$this->dyn_error_msg = $msg;
	}
}