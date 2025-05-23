<?php namespace Api;
/**
 * Default installation with standalone will pop this up with http://localhost:8001/api/talis/ping/
 * in the browser
 */
class TalisPingRead extends \Talis\Chain\aFilteredValidatedChainLink{
    
    /**
     * If I got here, it means the dependencies where satisified.
     * The next link is where we route depending on the Action value
     * 
     * Notice last element in the chain must implement  \Talis\commons\iRenderable
     * otherwise the response can not be rendered and it will error out after the last chainlink is processed
     * 
     * @return array with single or more BL aChainLink objects
     */
    protected function get_next_bl():array{
        return [
            [Pong::class,[]]
        ];
    }
}



/**
 * Return basic response to confirm chain operation and input capture
 */
class Pong extends \Talis\Chain\aChainLink implements \Talis\commons\iRenderable{
    public function process():\Talis\Chain\aChainLink{
        $this->Response->setMessage('PONG ... PING PONG!');
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
        \dbgn($this->Request->getUri() . ' PONG !');
        $emitter->emit($this->Response);
    }
}