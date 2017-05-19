<?php
class Form_Validator_containsNumber extends Form_Validator_Abstract{
	protected $message = 'Must contain a number';
	protected $numbers = '0123456789';
	
	/**
	 *  Validate that the value contains a number
	 *  @see Form_Validator_Abstract::validate()
	 */
	public function validate($value){
		return strpbrk($value, $this->numbers) !== FALSE;
	}
}