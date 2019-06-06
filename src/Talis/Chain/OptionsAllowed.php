<?php namespace Talis\Chain;

/**
 * Responsebility:
 *  Response for an OPTIONS request
 *  You must specify in a the chain params a string of `allowed` request methods. 
 *  
 * @author Itay Moav
 * @Date  2019-06-06
 */
class OptionsAllowed extends aChainLink implements \Talis\commons\iRenderable{
    
    /**
     * can be GET,POST,OPTIONS,DELETE,FETCH,PATCH etc 
     * @return string "...OPTIONS, DELETE, FETCH..."
     */
    protected function allowed():string{
        return 'OPTIONS';
    }
    
    final public function process():aChainLink{
        $this->Response->setHeader('Allow: ' . $this->allowed());
		return $this;
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Talis\commons\iRenderable::render()
	 */
	public function render(\Talis\commons\iEmitter $emitter):void{
		\dbgn($this->Request->getUri() . ' FINISHED CHAIN WITH SUCCESS');
		$this->Response->setMessage('Allowed Options');
		$this->Response->setStatus(new \Talis\Message\Status\Code204);
		$this->Response->markResponse();
		$emitter->emit($this->Response);
	}
}