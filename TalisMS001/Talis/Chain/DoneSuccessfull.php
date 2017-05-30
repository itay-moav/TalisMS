<?php namespace Talis\Chain;
use Talis\Logger as L;
use function \Talis\commons\array_to_object;

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
		return $this;
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Talis\commons\iRenderable::render()
	 */
	public function render(\Talis\commons\iEmitter $emitter):void{
		L\dbgn($this->Request->getUri() . ' FINISHED CHAIN WITH SUCCESS');
		$response_body = array_to_object([	'type'   => 'response',
											'message'=> 'BIG SUCCESS!',
											'params' => '']
		);
		$response_body->params = array_to_object(
				[ 'get' 	=> print_r($this->Request->getAllGetParams(),true),
				  'body'	=> print_r($this->Request->getBody(),true)
				]
		);
				
		$response = new \Talis\Message\Response;
		$response->setBody($response_body);
		$response->setStatus(new \Talis\Message\Status\Code200);
		$emitter->emit($response);
	}
}