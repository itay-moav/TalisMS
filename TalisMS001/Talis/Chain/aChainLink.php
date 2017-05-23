<?php namespace Talis\Chain;


/**
 * Responsebility: 
 *    (JUST) Manages the filters and then the dependency chain a request has.
 *    Last block in the chain (can also be the only one) would be the 
 *    concrete BL object for this request.
 *    
 * @author Itay Moav
 * @Date  2017-05-19
 */
abstract class aChainLink{
	
	/**
	 * @var array $get_params
	 * @var ?stdClass $req_body
	 * @var \Ds\Queue $chain_container
	 */
	protected $req_body					= null,
			  $get_params				= [],
			  $chain_container          = null
			  
	;
	
	public function __construct(array $get_params,?stdClass $req_body){
		$this->get_params = $get_params;
		$this->req_body   = $req_body;
	}
	
	/**
	 * A chain of links that will be (depends on process) processed one after the other.
	 * 
	 * @param \Ds\Queue $chain_container
	 */
	public function set_chain_container(\Ds\Queue $chain_container):void{
		$this->chain_container = $chain_container;
	}
	
	abstract public function process():aChainLink;
}