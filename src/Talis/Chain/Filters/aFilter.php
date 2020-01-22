<?php namespace Talis\Chain\Filters;
/**
 * 
 * @author Itay Moav
 * @2017-05-30
 */
abstract class aFilter extends \Talis\Chain\aChainLink implements \Talis\commons\iFilter{
	/**
	 * logic to filter. Do notice, the $message is a ctually the Request in this case
	 */
    abstract public function filter(\Talis\Message\Request $Request):void;

	/**
	 * 
	 * {@inheritDoc}
	 * @see \Talis\Chain\AChainLink::process()
	 */
	final public function process():\Talis\Chain\AChainLink{
		$this->filter($this->Request);
		return $this;
	}
}
