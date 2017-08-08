<?php namespace Talis\Chain;

/**
 * Responsebility: 
 *    (JUST) Manages the filters and then the dependency chain a request has.
 *    Last block in the chain (can also be the only one) would be the 
 *    concrete BL object /array of objects for this request.
 *    
 * @author Itay Moav
 * @Date  2017-05-19
 */
abstract class aFilteredValidatedChainLink extends aChainLink{
	
	/**
	 * 
	 * @var \Ds\Queue $chain_container
	 * @var array $filters
	 * @var array $dependencies
	 * @var AChainLink $Response
	 */
	protected $filters                  = [],
		  	  $dependencies 			= []
	;
	
	/**
	 * builds the chain from the filter+dependencies+bls
	 */
	final protected function load_chain_container():void{
		$this->set_chain_container(new \Ds\Queue(array_merge($this->filters,$this->dependencies,$this->get_next_bl())));	
	}
	
	/**
	 * Return the first BL class in the actual 
	 * process.
	 * @return array with single or more BL objects
	 */
	protected function get_next_bl():array{
		return [];
	}
		
	public function __construct(\Talis\Message\Request $Request){
		parent::__construct($Request);
		$this->load_chain_container();
	}
}