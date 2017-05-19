<?php
/**
 * Length can must have at least one param of ['min'=>value,'max'=>value]
 * 
 * @author itaymoav
 */
class Form_Validator_stringLength extends Form_Validator_Abstract{
	
	/**
	 * @param string $overwrite_message
	 * @param array $elm_specific_params
	 */
	public function __construct($overwrite_message=false,array $elm_specific_params = []){
		$this->validate_params($elm_specific_params);
		
		parent::__construct($overwrite_message,$elm_specific_params);
		if(!$overwrite_message){
			$this->set_message();
		}
	}
	
	/**
	 * Validate the params for this validator
	 * 
	 * @param array $elm_specific_params
	 * @throws InvalidArgumentException
	 * @return Form_Validator_stringLength
	 */
	private function validate_params(array $elm_specific_params){
		if(!isset($elm_specific_params['min']) && !isset($elm_specific_params['max'])){
			throw new InvalidArgumentException('Missing the min and max params');
		}
		
		if(isset($elm_specific_params['min']) && isset($elm_specific_params['max']) && $elm_specific_params['max'] < $elm_specific_params['min']){
			throw new LogicException('min cant be higher than max');
		}
		
		return $this;
	}
	
	/**
	 * Sets the proper default message
	 * 
	 * @return Form_Validator_stringLength
	 */
	private function set_message(){
		if(!isset($this->params['min'])){
			$this->message = "This field can have up to {$this->params['max']} characters"; 
		}elseif(!isset($this->params['max'])){
			$this->message = "This field can have no less than {$this->params['min']} characters";
		}else{
			$this->message = "This field can have {$this->params['min']} to {$this->params['max']} characters";
		}
		
		return $this;
	}
	
	/**
	 * Checks value is between min max
	 * 
	 * @param string $value
	 * @return boolean
	 */
	public function validate($value){
		$min = isset($this->params['min'])?$this->params['min']:0;
		$max = isset($this->params['max'])?$this->params['max']:PHP_INT_MAX;
		$length = strlen($value);
		return ($length>=$min && $length<=$max);
	}
}