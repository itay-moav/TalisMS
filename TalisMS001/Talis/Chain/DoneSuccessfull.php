<?php namespace Talis\Chain;
use Talis\Logger as L;

/**
 * Responsebility:
 *  Default success message 
 *  
 * @author Itay Moav
 * @Date  2017-05-22
 */
class DoneSuccessfull extends AChainLink implements \Talis\commons\iRenderable{
	public function process():AChainLink{
		return $this;
	}
	
	public function render():void{
		L\dbgn('FINISHED CHAIN WITH SUCCESS');
		echo 'BIG SUCCESS IN A GENERAL WAY!';
	}
}