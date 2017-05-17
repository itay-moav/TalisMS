<?php
/**
 * I could not find an easy way to separate view (language) from server functionality = Sorry
 * 
 * @author itaymoav
 */
class Form_Validator_notEmpty extends Form_Validator_Abstract{
	protected $message = 'This field is required';
	
	/**
	 * Checks there is something there,
	 * 
	 * @param unknown $value
	 * @return string
	 */
	public function validate($value){
	    return trim($value)!=='';
	}
}