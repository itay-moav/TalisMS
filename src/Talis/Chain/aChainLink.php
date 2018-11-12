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
	 * @var \Talis\Message\Request $Request
	 */
	protected $Request 					= null;
	
	/**
	 * @var \Talis\Message\Response $Response
	 */
	protected $Response					= null;
	
	/**
	 * Context for the current flow. it is transfered same as Response/Request between each chain link
	 */
	protected $Context                  = null;
	
	/**
	 * Extra params some classes need
	 * @var array
	 */
	protected $params					= [];

	/**
	 * @var \Ds\Queue $chain_container
	 */
	protected $chain_container          = null;
	
	/**
	 * @var boolean
	 */
	protected $valid					= true;
	
	
	/**
	 * 
	 * @param \Talis\Message\Request $Request
	 */
	public function __construct(?\Talis\Message\Request $Request,?\Talis\Message\Response $Response,array $params=[]){
		$this->Request  = $Request;
		$this->Response = $Response;
		$this->params   = $params;
	}
	
	/**
	 * A chain of links that will be (depends on process) processed one after the other.
	 * 
	 * @param \Ds\Queue $chain_container
	 */
	public function set_chain_container(\Ds\Queue $chain_container):void{
		$this->chain_container = $chain_container;
	}
	
	/**
	 * Actual logic should happen here.
	 * 99.999999999% it should return itself!
	 * 
	 * @return aChainLink
	 */
	abstract public function process():aChainLink;
	
	/**
	 * Do the filter chain
	 * Pass the filtered get params and req body and next bl to the dependency chain
	 * Sets the result as the response
	 *
	 * @see \Talis\Chain\AChainLink::nextLinkInchain()
	 */
	final public function nextLinkInchain():\Talis\Chain\aChainLink{
		dbgn('About to process: [' . get_class($this).']');
		$FinalLink = $this->process();
		if($this->chain_container != null && !$this->chain_container->isEmpty()){
			$next_link_class = $this->chain_container->pop();
			$name   = $next_link_class[0];
			$params = $next_link_class[1];
			dbgn("STARTING NEXT CHAIN LINK WITH {$name}");
			$next_link = new $name($this->Request,$this->Response,$params);//TODO maybe array merge with current class params?
			$next_link->set_chain_container($this->chain_container);
			$FinalLink = $next_link->nextLinkInchain();
		}
		return $FinalLink;
	}
}