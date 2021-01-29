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
		$this->Response->setMessage('Resource Created Successfully!');
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
	    \ZimLogger\MainZim::$CurrentLogger->debug($this->Request->getUri() . ' FINISHED CHAIN WITH SUCCESS');
	    \ZimLogger\MainZim::$CurrentLogger->debug('RESPONSE: ');
	    \ZimLogger\MainZim::$CurrentLogger->debug($this->Response);
		$emitter->emit($this->Response);
	}
}