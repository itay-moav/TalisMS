<?php namespace Talis\Chain\Dependencies;
use \Talis\Logger as L;
use function Talis\commons\array_to_object;

/**
 * Making sure that a body with params was sent
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
		$r = isset($body->params);
		$b = isset($body->params->$field);
		return $r&&$b;
	}
	
	public function render(\Talis\commons\iEmitter $emitter):void{
		L\dbgr('RENDER input body',print_r($this->Request->getBody(),true));
		$response = new \Talis\Message\Response;
		$response->setMessage("Mising param [{$this->params['field']}] part in request body->params[]");
		$response->markDependency();
		$response->setStatus(new \Talis\Message\Status\Code500);
		$emitter->emit($response);
	}		
}
