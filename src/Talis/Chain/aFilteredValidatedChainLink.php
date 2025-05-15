<?php namespace Talis\Chain;

/**
 * Responsebility: 
 *    Manages the filters and then the dependency chain a request has.
 *    Last block in the chain (can also be the only one) would be the 
 *    concrete BL object /array of objects for this request.
 * 	  Merges filters, dependencies, and BL handlers into a single execution chain
 *    where the order is Filters first, then validator then the bl Chain Links
 *    
 * @author Itay Moav
 * @Date  2017-05-19
 */
abstract class aFilteredValidatedChainLink extends aChainLink{

    /**
     * @var array<\Talis\Chain\Filters\aFilter>
     */
	protected array $filters = [];
	
	/**
	 *                   dependency class name
	 *                                param name
	 *                                        param value         
	 * @var array<int,array<mixed>>
	 */
	protected array $dependencies = [];
	
	/**
	 * builds the chain from the filter+dependencies+bls
	 *  Merges filters, dependencies, and BL handlers into a single execution chain
 	 *  where the order is Filters first, then validator then the bl Chain Links
	 */
	final protected function load_chain_container():void{
	    $this->set_chain_container(new ChainContainer(array_merge($this->filters,$this->dependencies,$this->get_next_bl())));
	}
	
	/**
	 * I am blocking any processing power, the job of this class is ONLY 
	 * to host the filters and dependencies!
	 * 
	 * {@inheritDoc}
	 * @see \Talis\Chain\aChainLink::process()
	 */
	final public function process():aChainLink{
		return $this;	
	}
	
	/**
	 * Return the first BL aChainLink class in the actual 
	 * process.
	 * [   class name,[params]  ],
	 * [   class name,[params]  ]
	 * 
	 * @return  array<array<mixed>>
	 */
	abstract protected function get_next_bl():array;
		
	/**
	 * @param \Talis\Message\Request $Request
	 * @param \Talis\Message\Response $Response
	 * @param array<mixed> $params
	 */
	public function __construct(\Talis\Message\Request $Request,\Talis\Message\Response $Response,array $params=[]){
		parent::__construct($Request,$Response,$params);
		$this->load_chain_container();
	}
}