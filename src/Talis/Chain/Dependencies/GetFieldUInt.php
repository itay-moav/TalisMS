<?php namespace Talis\Chain\Dependencies;

/**
 * Making sure that a get field exist in the request
 * 
 * @author Itay Moav
 */
class GetFieldUInt extends aDependency{
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Talis\Chain\Dependencies\ADependency::validate()
	 */
	protected function validate():bool{
	    $field_to_validate = $this->Request->get_param($this->params['field']);
	    if(is_numeric($field_to_validate)){//if numeric, cast to number var type.
	        $field_to_validate = $field_to_validate *1;
	    }
	    $valid = is_integer($field_to_validate) && $field_to_validate >= 0;	    
		
	    \ZimLogger\MainZim::$CurrentLogger->debug('validaror ' . self::class);
		\ZimLogger\MainZim::$CurrentLogger->debug('params: [' . print_r($this->params,true) . "] is valid? [{$valid}]");
		
		return $valid;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Talis\commons\iRenderable::render()
	 */
	public function render(\Talis\commons\iEmitter $emitter):void{
		$response = new \Talis\Message\Response;
		$response->markDependency();
		$field = $this->Request->get_param($this->params['field']);
		$response->setMessage("GET PARAM {$this->params['field']} is not an unsigned int, it is: [{$field}]");
		$response->setStatus(new \Talis\Message\Status\Code500);
		$emitter->emit($response);
	}		
}