<?php namespace Talis\Chain\Dependencies;
use \Talis\Logger as L;
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
	
	public function render():void{
		L\dbgr('RENDER',print_r($this->params,true));
		echo "<br>BUG! Mising {$this->params['field']} <br>";
	}
}