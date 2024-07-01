<?php namespace Talis\Chain;

/**
 * This finishes a chain link with the iRenderer to echo a stringified version of the payload.
 * status 200.
 * 
 * @author Itay Moav
 * @date 20202-04-30
 */
class PayloadToString200 extends aChainLink implements \Talis\commons\iRenderable{
    /**
     * No processing, just rendering
     * 
     * {@inheritDoc}
     * @see \Talis\Chain\aChainLink::process()
     */
	public function process():aChainLink{
	    /*
		$this->Response->setMessage('GREAT SUCCESS!');
		$this->Response->setStatus(new \Talis\Message\Status\Code200);
		$this->Response->markResponse();
		*/
		return $this;
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Talis\commons\iRenderable::render()
	 */
	public function render(\Talis\commons\iEmitter $emitter):void{
	    $res = $this->Response->getPayload() . '';
	    
	    \Talis\Corwin::logger()->debug('PAYLOAD RESPONSE STRINGIFIED');
	    \Talis\Corwin::logger()->debug($res);

	    header('HTTP/1.1 200 Ok');
	    $all_other_headers = $this->Response->getHeaders();
	    if($all_other_headers){
	    
	        \Talis\Corwin::logger()->debug('SENDING HEADERS');
	        \Talis\Corwin::logger()->debug($all_other_headers);
	        
	        foreach($all_other_headers as $other_header){
	            header($other_header);
	        }
	    }
	    echo $res;
	}
}