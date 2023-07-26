<?php namespace Talis\Chain;

/**
 * Responsebility:
 *  reports successfull resource deleted
 *  
 * @author Itay Moav
 * @Date  2021-04-05
 */
class ResourceDeleted extends aChainLink implements \Talis\commons\iRenderable{
    /**
     * {@inheritDoc}
     * @see \Talis\Chain\aChainLink::process()
     */
	public function process():aChainLink{
		$this->Response->setMessage('Resource deleted');
		$this->Response->setStatus(new \Talis\Message\Status\Code204);
		$this->Response->markResponse();
		return $this;
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Talis\commons\iRenderable::render()
	 */
	public function render(\Talis\commons\iEmitter $emitter):void{
	    \ZimLogger\MainZim::$CurrentLogger->debug($this->Request->getUri() . ' RESOURCE DELETED!');
	    \ZimLogger\MainZim::$CurrentLogger->debug('RESPONSE: ');
	    \ZimLogger\MainZim::$CurrentLogger->debug($this->Response);
	    $emitter->emit($this->Response);
	}
}
