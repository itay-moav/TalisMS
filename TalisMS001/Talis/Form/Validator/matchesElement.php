<?php
class Form_Validator_matchesElement extends Form_Validator_Abstract{
	protected $message = 'Must match the other element';

	/**
	 * @param string $overwrite_message
	 * @param array $elm_specific_params
	 */
	public function __construct($overwrite_message=false,array $elm_specific_params = []){
		$this->validate_params($elm_specific_params);
	
		parent::__construct($overwrite_message,$elm_specific_params);
	}
	
	/**
	 * Validate the params for this validator
	 *
	 * @param array $elm_specific_params
	 * @throws InvalidArgumentException
	 * @return Form_Validator_stringLength
	 */
	private function validate_params(array $elm_specific_params){
		if(!isset($elm_specific_params['matchElement'])){
			throw new Exception_MissingParam('matchElement');
		}
		
		if(!($elm_specific_params['matchElement'] instanceof Form_Element_Simple)){
			throw new InvalidArgumentException('Element matching must use Form_Element_Simple');
		}
	
		return $this;
	}
	
	
	/**
	 *  Validate that the value contains a number
	 *  @see Form_Validator_Abstract::validate()
	 */
	public function validate($value){
		return $value == $this->params['matchElement']->get_value();
	}
}