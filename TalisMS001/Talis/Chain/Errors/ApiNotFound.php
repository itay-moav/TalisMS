<?php namespace Talis\Chain\Errors;
use \Talis\Chain as Chain;

class ApiNotFound implements \Talis\Chain\aChainLink{
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
	
	public function render():void{
		echo "{type:error,msg:API {$this->error}}";
	}
}
