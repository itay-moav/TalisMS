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
    protected \Talis\Message\Request $Request;
	
	/**
	 * @var \Talis\Message\Response $Response
	 */
    protected \Talis\Message\Response $Response;
	
	/**
	 * Extra params some classes need
	 * @var array<mixed>
	 */
	protected array $params;

	/**
	 * @var ?\Ds\Queue<array> $chain_container
	 */
	protected ?\Ds\Queue $chain_container = null;
	
	/**
	 * @var boolean
	 */
	protected bool $valid        		  = true;
    
	/**
	 * 
	 * @param \Talis\Message\Request $Request
	 * @param \Talis\Message\Response $Response
	 * @param array<mixed> $params
	 */
	public function __construct(\Talis\Message\Request $Request,\Talis\Message\Response $Response,array $params=[]){
		$this->Request  = $Request;
		$this->Response = $Response;
		$this->params   = $params;
	}
	
	/**
	 * A chain of links that will be (depends on process) processed one after the other.
	 * 
	 * @param \Ds\Queue<array> $chain_container
	 */
	public function set_chain_container(\Ds\Queue $chain_container):void{
		$this->chain_container = $chain_container;
	}
	
	/**
	 * Returns a copy of the response object
	 * @return \Talis\Message\Response
	 */
	public function clone_response():\Talis\Message\Response{
	    return clone $this->Response;
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
	 * @return \Talis\Chain\aChainLink
	 * 
	 * @see \Talis\Chain\AChainLink::nextLinkInchain()
	 */
	final public function nextLinkInchain():\Talis\Chain\aChainLink{
	    \ZimLogger\MainZim::$CurrentLogger->debug('About to process: [' . get_class($this).']');
		$FinalLink = $this->process();
		//If the returned chain is not a new chain (road diversion) and there are more links in the current chain, go after it.
		if($FinalLink == $this && $this->chain_container !== null && !$this->chain_container->isEmpty()){
			$next_link_class = $this->chain_container->pop();
			$name   = $next_link_class[0];
			$params = $next_link_class[1];
			\ZimLogger\MainZim::$CurrentLogger->debug("STARTING NEXT CHAIN LINK WITH {$name}");
			$next_link = new $name($this->Request,$this->Response,$params);
			$next_link->set_chain_container($this->chain_container);
			$FinalLink = $next_link->nextLinkInchain();
		}
		return $FinalLink;
	}
}