<?php namespace Talis\Chain;
use Talis\Logger as L;
use function \Talis\commons\array_to_object;

/**
 * Responsebility:
 *  Default success message
 *  Emits the final request body 
 *  
 * @author Itay Moav
 * @Date  2017-05-22
 */
class DoneSuccessfull extends aChainLink implements \Talis\commons\iRenderable{
	public function process():aChainLink{
		$this->Response->setMessage('BIG SUCCESS!');
		$this->Response->setStatus(new \Talis\Message\Status\Code200);
		$this->Response->markResponse();
		return $this;
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Talis\commons\iRenderable::render()
	 */
	public function render(\Talis\commons\iEmitter $emitter):void{
		L\dbgn($this->Request->getUri() . ' FINISHED CHAIN WITH SUCCESS');
		$emitter->emit($this->Response);
	}
}