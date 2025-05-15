<?php namespace Talis\Chain;


/**
 * Responsebility: 
 *    Wraps functional chainlinks for easier use
 * 
 * Useful for quick logic injection without needing to define a full class
 * Instead of creating a class the user can do:
 * [[ClassName::class, [param1, param2, ...],[createFunc(), [param1, param2, ...]]
 * 
 * function createFunc():callable{
 *      return function (\Talis\Message\Request $Request,\Talis\Message\Response $Response,array $params){
 *          // do something with Request and params and adds output into Response->getPayload()->someVar=???
 *      }
 * }
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
