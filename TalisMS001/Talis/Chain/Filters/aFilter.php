<?php namespace Talis\Chain\Filters;
/**
 * 
 * @author Itay Moav
 * @2017-05-30
 */
abstract class aFilter extends \Talis\Chain\aChainLink implements \Talis\commons\iMessageFilter{
	/**
	 * logic to filter. Do notice, the $message is a ctually the Request in this case
	 */
	abstract public function filter(\Talis\Message\aMessage $message):void;

	/**
	 * 
	 * {@inheritDoc}
	 * @see \Talis\Chain\AChainLink::process()
	 */
	final public function process():\Talis\Chain\AChainLink{
		$this->filter($this->Request);
		if(!$this->chain_container->isEmpty()){
			$next_link_class = $this->chain_container->pop();
			$name   = $next_link_class[0];
			$params = $next_link_class[1];
			$next_link = new $name($this->Request,$this->Response,$params);
			$next_link->set_chain_container($this->chain_container);
			$response = $next_link->process();
		} else {//how can we have a filter only with no continuence? There always must be a BL at the end.
			$response =  new \Talis\Chain\Errors\BLLinkMissingInChain($this->Request,null);
		}
		return $response;
	}
}
