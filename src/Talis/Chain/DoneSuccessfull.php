<?php namespace Talis\Chain;

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
		$this->Response->setMessage('GREAT SUCCESS!');
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
	    \Talis\TalisMain::logger()->debug($this->Request->getUri() . ' FINISHED CHAIN WITH SUCCESS');
	    \Talis\TalisMain::logger()->debug('RESPONSE: ');
	    \Talis\TalisMain::logger()->debug($this->Response);
		$emitter->emit($this->Response);
	}
}