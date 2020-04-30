<?php namespace Talis\Chain;

/**
 * This finishes a chain link with the iRenderer to echo a stringified version of the payload.
 * status 200.
 * 
 * @author Itay Moav
 * @date 20202-04-30
 */
class PayloadToString200 extends aChainLink implements \Talis\commons\iRenderable{
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
	    dbgr('PAYLOAD RESPONSE STRINGIFIED',$res);
	    header('HTTP/1.1 200 Ok');
	    $all_other_headers = $this->Response->getHeaders();
	    if($all_other_headers){
	        \dbgr('SENDING HEADERS',$all_other_headers);
	        
	        foreach($all_other_headers as $other_header){
	            header($other_header);
	        }
	    }
	    echo $res;
	}
}