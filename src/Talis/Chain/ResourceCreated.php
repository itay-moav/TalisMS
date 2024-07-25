<?php namespace Talis\Chain;

/**
 * Responsebility:
 *  reports successfull resource creations (POSTs)
 *  NOTICE! if you want to return the new resource, you need to handle it in your chain links.
 *  
 * @author Itay Moav
 * @Date  2019-05-13
 */
class ResourceCreated extends aChainLink implements \Talis\commons\iRenderable{
    /**
     * {@inheritDoc}
     * @see \Talis\Chain\aChainLink::process()
     */
	public function process():aChainLink{
		$this->Response->setMessage('Resource created');
		$this->Response->setStatus(new \Talis\Message\Status\Code201);
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
