<?php namespace Talis\Chain;

/**
 * TODO: Move into the error chainlink folder
 *
 * Responsebility:
 * 
 *  Alerts the client that a create call is trying to create a duplicate resource, let's see how client can handle it
 *  
 * @author Itay Moav
 * @Date  2023-03-02
 */
class DuplicateResource extends aChainLink implements \Talis\commons\iRenderable{
    /**
     * {@inheritDoc}
     * @see \Talis\Chain\aChainLink::process()
     */
	public function process():aChainLink{
		$this->Response->setMessage('duplicate resource');
		$this->Response->setStatus(new \Talis\Message\Status\Code422);
		$this->Response->markError();
		return $this;
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Talis\commons\iRenderable::render()
	 */
	public function render(\Talis\commons\iEmitter $emitter):void{
	    \Talis\TalisMain::logger()->debug($this->Request->getUri() . ' CHAIN ENDS ABROPTLY: Trying to create a duplicate resource');
	    \Talis\TalisMain::logger()->debug('RESPONSE: ');
	    \Talis\TalisMain::logger()->debug($this->Response);
		$emitter->emit($this->Response);
	}
}
