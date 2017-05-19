<?php
/**
 * Digits
 * 
 * @author itaymoav
 */
class Form_Validator_digits extends Form_Validator_Abstract{
	protected $message = 'This field can have only numbers';
	
	/**
	 * Checks there is something there,
	 * 
	 * @param mixed $value
	 * @return boolean
	 */
	public function validate($value){
		return !$value || is_numeric($value);
	}
}