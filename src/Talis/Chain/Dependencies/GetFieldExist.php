<?php namespace Talis\Chain\Dependencies;
use function Talis\commons\array_to_object;

/**
 * Making sure that a get field exist in the request
 * 
 * @author Itay Moav
 */
class GetFieldExist extends aDependency{
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Talis\Chain\Dependencies\ADependency::validate()
	 */
	protected function validate():bool{
		$valid = isset($this->Request->getAllGetParams()[$this->params['field']]);
		\dbgr('validaror ' . self::class, 'params: [' . print_r($this->params,true) . "] is valid? [{$valid}]");
		return $valid;
	}
	
	public function render(\Talis\commons\iEmitter $emitter):void{
		//\dbgr('RENDER',print_r($this->params,true));
		$response = new \Talis\Message\Response;
		$response->markDependency();
		$response->setMessage("Mising URI PARAM {$this->params['field']}");
		$response->setStatus(new \Talis\Message\Status\Code500);
		$emitter->emit($response);
	}		
}