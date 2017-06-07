<?php namespace Talis\Chain\Errors;

use Talis\Chain\aChainLink;

/**
 * basic error/problem class
 * 
 * @author Itay Moav
 * @date 2017-05-23
 *
 */
abstract class aError extends \Talis\Chain\aChainLink{
	protected $error_params	 = [],
			  $http_code	 = 0
	;
	
	abstract protected function format_human_message():string;
	
	public function __construct(?\Talis\Message\Request $Request,array $error_params=[]){
		parent::__construct($Request);
		$this->error_params = $error_params;
	}
	
	/**
	 * This is an end of the line chain link, return itself.
	 * @return Talis\Chain\iReqRes
	 */
	public function process():\Talis\Chain\aChainLink{
		return $this;
	}
	
	/**
	 *  
	 */
	public function render():void{
		echo $this->format_human_message();
	}
}
