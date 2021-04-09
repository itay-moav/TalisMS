<?php namespace Talis\Chain;


/**
 * Responsebility: 
 *    Wraps functional chainlinks for easier use
 *    
 * @author Itay Moav
 * @Date  2021-04-08
 */
class FunctionalChainlinkWrapper extends aChainLink{
    
    /**
     * @var callable function to run in process
     */
    private $wrappedFunction;
    
	/**
	 * @param callable $wrappedFunction
	 * @param \Talis\Message\Request $Request
	 * @param \Talis\Message\Response $Response
	 * @param array<mixed> $params
	 */
    public function __construct(callable $wrappedFunction,\Talis\Message\Request $Request,\Talis\Message\Response $Response,array $params){
	    parent::__construct($Request,$Response,$params);
	    $this->wrappedFunction = $wrappedFunction;
	}
	
    /**
     * @return aChainLink
     */
	public function process():aChainLink{
	    $func = $this->wrappedFunction;
	    $func($this->Request,$this->Response,$this->params);
	    return $this;
	}
}
