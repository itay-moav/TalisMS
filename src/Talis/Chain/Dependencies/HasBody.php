<?php namespace Talis\Chain\Dependencies;
use function Talis\commons\array_to_object;

/**
 * Making sure that a body with params was sent
 * 
 * @author Itay Moav
 */
class HasBody extends aDependency{
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Talis\Chain\Dependencies\ADependency::validate()
	 */
	protected function validate():bool{
		return isset($this->Request->getBody()->params) && count($this->Request->getBody()->params);
	}
	
	public function render(\Talis\commons\iEmitter $emitter):void{
		\dbgr('RENDER input body',print_r($this->Request->getBody(),true));
		$response = new \Talis\Message\Response;
		$response->setBody(array_to_object(['type'=>'dependency','message'=>"Mising params part in request"]));
		$response->setStatus(new \Talis\Message\Status\Code500);
		$emitter->emit($response);
	}		
}
