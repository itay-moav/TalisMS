<?php namespace Talis\Chain\Dependencies;

/**
 * Making sure that a body with params was sent
 * 
 * @author Itay Moav
 */
class BodyParamsFieldExist extends aDependency{
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
		
		\Talis\TalisMain::logger()->debug('RENDER input body');
		\Talis\TalisMain::logger()->debug($this->Request->getBody());
		$msg="Mising param [{$this->params['field']}] part in request body->params[]";
		\Talis\TalisMain::logger()->warning($msg,true);
		$response = new \Talis\Message\Response;
		$response->setMessage($msg);
		$response->markDependency();
		$response->setStatus(new \Talis\Message\Status\Code400);
		$emitter->emit($response);
	}		
}
