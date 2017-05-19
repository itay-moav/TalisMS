<?php
class Form_Validator_containsLetter extends Form_Validator_Abstract{
	protected $message = 'Must contain a letter';
	protected $letters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

	/**
	 *  Validate that the value contains a number
	 *  @see Form_Validator_Abstract::validate()
	 */
	public function validate($value){
		return strpbrk($value, $this->letters) !== FALSE;
	}
}