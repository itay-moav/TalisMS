<?php
/**
 * the most simple of form elements.
 * Has no type and gives only the basic functionality:
 * 1. validator
 * 2. Output the vnc values formated as HTML
 * 3. Getter/Setters
 * 
 * @author itaymoav
 */
class Form_Element_Simple{
	protected	$name		= '',
				$value		= NULL,
				$validators	= [],
				$validator_classes = '',
				$last_used_validator_class_name = ''
	;
	
	/**
	 * @param string $value
	 * @param array $validators
	 */
	public function __construct($name,$value=null,array $validators=[]){
		$this->name = $name;
		$this->set_value($value);
		$this->validators = $validators;
	}
	
	/**
	 * Set the value
	 * 
	 * @param mixed $v
	 * @param mixed $default
	 * 
	 * @return mixed the value
	 */
	public function set_value($v,$default=null){
		$this->value = $v?:$default;
		return $this->value;
	}
	
	/**
	 * alias set_value
	 * 
	 * @param mixed $v
	 * @param mixed $default
	 * 
	 * @return mixed the value
	 */
	public function set_v($v,$default=null){
		return $this->set_value($v,$default);
	}
	
	/**
	 * Get the value
	 *
	 * @param mixed $default
	 *
	 * @return mixed the value
	 */
	public function get_value($default=null){
		return $this->value?:$default;
	}
	
	/**
	 * alias to get_value
	 * 
	 * @param string $default
	 * @return mixed
	 */
	public function v($default=null){
		return $this->get_value($default);
	}
	
	/**
	 * Get element validators
	 */
	public function get_validators(){
		return $this->validators;
	}
	
	public function name(){
		return $this->name;
	}
	
	/**
	 * Loop on all validators
	 * TODO: set meaningful msg somewhere
	 * 
	 * @return boolean
	 */
	public function validate(){
		foreach($this->validators as $validator){
			if(!$validator->validate($this->v())){
				$this->last_used_validator_class_name = get_class($validator);
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Get the last validator used in validate 
	 * Populates ONLY on failure to validate
	 * 
	 * @return string class name of validator lst used 
	 */
	public function last_used_validator(){
		return $this->last_used_validator_class_name;
	}
	
	/**
	 * Return value
	 * 
	 * @return string
	 */
	public function __toString(){
		return $this->value . '';
	}
}