<?php namespace Talis\Chain;
use Talis\Logger as L;

/**
 * Responsebility:
 *  Default success message 
 *  
 * @author Itay Moav
 * @Date  2017-05-22
 */
class DoneSuccessfull extends aChainLink implements \Talis\commons\iRenderable{
	public function process():aChainLink{
		return $this;
	}
	
	public function render():void{
		L\dbgn($this->Request->getUri() . ' FINISHED CHAIN WITH SUCCESS');
		echo 'BIG SUCCESS IN A GENERAL WAY!';
	}
}