<?php namespace Talis\Chain\Errors;
use \Talis\Chain as Chain;

class ApiNotFound implements Chain\iReqRes{
	private $error = '';
	
	public function __construct(string $error){
		$this->error = $error;
	}
	
	/**
	 * This is an end of the line chain link, return itself.
	 * @return Talis\Chain\iReqRes
	 */
	public function process():Chain\iReqRes{
		return $this;
	}
	
	public function render(){
		echo "{type:error,msg:API {$this->error}}";
	}
}