<?php namespace Talis\Chain\Dependencies;
use \Talis\Logger as L;
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
		L\dbgr('validaror ' . self::class, 'params: [' . print_r($this->params,true) . "] is valid? [{$valid}]");
		return $valid;
	}
	
	public function render(\Talis\commons\iEmitter $emitter):void{
		L\dbgr('RENDER',print_r($this->params,true));
		$response = new \Talis\Message\Response;
		$response->setBody(array_to_object(['type'=>'dependency','message'=>"Mising {$this->params['field']}"]));
		$response->setStatus(new \Talis\Message\Status\Code500);
		$emitter->emit($this->Response);
	}		
}