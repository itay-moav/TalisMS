<?php namespace Talis\Chain;
use Talis\Logger as L;

/**
 * Responsebility: 
 *    (JUST) Manages the filters and then the dependency chain a request has.
 *    Last block in the chain (can also be the only one) would be the 
 *    concrete BL object for this request.
 *    
 * @author Itay Moav
 * @Date  2017-05-19
 */
abstract class Api implements iReqRes{
	
	protected $filters                  = [],
		  	  $chain_container          = null,
			  $dependencies 			= []
	;
	
	final protected function load_chain_container():void{
		$this->chain_container = new \Ds\Queue(array_merge($this->filters,$this->dependencies,$this->get_next()?:[]));	
	}
	
	/**
	 * Return the first BL class in the actual 
	 * process.
	 * @return array with single or more BL objects
	 */
	protected function get_next():?array{
		return null;
	}
		
	public function __construct(stdClass $body=null){
		$this->load_chain_container();
	}
	
	abstract public function render():void;
}