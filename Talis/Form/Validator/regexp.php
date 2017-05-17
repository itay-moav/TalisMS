<?php
/**
 * Digits
 * 
 * @author itaymoav
 */
class Form_Validator_regexp extends Form_Validator_Abstract{
	protected $message = 'Pattern did not match';
	
	/**
	 * Checks there is something there,
	 * 
	 * @param mixed $value
	 * @return boolean
	 */
	public function validate($value){
	    return boolval(preg_match ($this->params['pattern'] ,$value));
	}
}
