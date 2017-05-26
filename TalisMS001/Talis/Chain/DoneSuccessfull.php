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
	
	public function render(\Talis\commons\iEmitter $emitter):void{
		L\dbgn($this->Request->getUri() . ' FINISHED CHAIN WITH SUCCESS');
		$response = new \Talis\Message\Response;
		$response->setBody(\Talis\commons\array_to_object(['type'=>'response','message'=>'BIG SUCCESS!','params'=>print_r($this->Request->getAllGetParams(),true)]));
		$response->setStatus(new \Talis\Message\Status\Code200);
		$emitter->emit($response);
	}
}