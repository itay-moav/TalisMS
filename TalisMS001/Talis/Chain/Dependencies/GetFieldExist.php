<?php namespace Talis\Chain\Dependencies;
use \Talis\Logger as L;
/**
 * Making sure that a get field exist in the request
 * 
 * @author Itay Moav
 */
class GetFieldExist extends ADependency{
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Talis\Chain\Dependencies\ADependency::validate()
	 */
	protected function validate():bool{
		L\dbgr('validaror ' . print_r($this->params,true),isset($this->get_params[$this->params['field']]));
		return isset($this->get_params[$this->params['field']]);
	}
	
	public function render():void{
		L\dbgr('RENDER',print_r($this->params,true));
		echo "<br>BUG! Mising {$this->params['field']} <br>";
	}
}