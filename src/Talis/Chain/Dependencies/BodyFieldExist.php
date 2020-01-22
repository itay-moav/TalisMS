<?php namespace Talis\Chain\Dependencies;

/**
 * Making sure that a body with some field was sent
 * 
 * @author Itay Moav
 */
class BodyFieldExist extends aDependency{
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Talis\Chain\Dependencies\ADependency::validate()
	 */
	protected function validate():bool{
		$body  = $this->Request->getBody();
		$field = $this->params['field'];
		return isset($body->$field);
	}
	
	public function render(\Talis\commons\iEmitter $emitter):void{
		\dbgr('RENDER input body',print_r($this->Request->getBody(),true));
		$response = new \Talis\Message\Response;
		$response->setMessage("Mising field [{$this->params['field']}] in request body");
		$response->markDependency();
		$response->setStatus(new \Talis\Message\Status\Code500);
		$emitter->emit($response);
	}		
}
