<?php namespace Talis\Chain;

/**
 * Responsebility:
 *  reports successfull resource updating (not creating)
 *  
 * @author Itay Moav
 * @Date  2021-04-05
 */
class ResourceUpdated extends aChainLink implements \Talis\commons\iRenderable{
    /**
     * {@inheritDoc}
     * @see \Talis\Chain\aChainLink::process()
     */
	public function process():aChainLink{
		$this->Response->setMessage('Resource updated');
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
	    \Talis\Corwin::logger()->debug($this->Request->getUri() . ' FINISHED CHAIN WITH SUCCESS');
	    \Talis\Corwin::logger()->debug('RESPONSE: ');
	    \Talis\Corwin::logger()->debug($this->Response);
		$emitter->emit($this->Response);
	}
}
