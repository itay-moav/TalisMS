<?php
/**
 * lms3.0 Form class.
 * 
 * Form class provides:
 * 						1. The SAVE entry point
 * 						2. Elements container
 * 						3. Handles the validation (part of Save)
 * 
 * @author itaymoav
 *
 */
abstract class F{
    
    /**
     * shortcut to create Form_Element_Simple
     * 
     * @param string $name
     * @param string $value
     * @param array $validators
     */
    static public function elm_s($name,$value=null,array $validators=[]){
        return new Form_Element_Simple($name,$value,$validators);
    }
    
    
    
    
    
    
				/**
				 * @var aray clean values, straight from input
				 */
	protected	$values=[],
				/**
				 * @var array of Elements
				 */
				$elements=[],
				/**
				 *  @var array In case of validation, saves the field name, validator name and value that was validated
				 */
				$failed_validation = []
	;
		
	/**
	 * Creates a Form and populates the specified values in $values.
	 * @var $values mixed eithr a stdClass or an associative array
	 */
  	public function __construct($values=null){
		$this->setElements()
  			 ->init()
		;
		if($values) $this->setValues($values);
  	}

  	/**
  	 * Magical 
  	 * 
  	 * @param string $name
  	 * @return aray
  	 */
 	function __get($name){
		return $this->elements[$name];
	}
  	  	
  	/**
  	 * Setup elements -> be sure to call consumeElements(elm1,elm2,elm3..elm n)
  	 * 
  	 * @return F
  	 */
  	abstract protected function setElements();

  	/**
  	 * @return aray of the lements
  	 */
  	public function getElements(){
  		return $this->elements;
  	}
  	
  	/**
  	 * Get an array of elements and build from it the elements array
  	 * and the fields array
  	 * @return Form
  	 */
  	final protected function consumeElements(){
		$elements=func_get_args();
		foreach($elements as $Element){
			$this->elements[$Element->name()] = $Element;
		}
 		return $this;
  	}

	/**
	 * hookup for pre initialization code,
	 * Here you usually build the elements into the class
	 * 
	 * @return F
	 */
	protected function init(){
		return $this;		
	}

	/**
	 * Set additional values after initial setup
	 * This will also overwrite existing values.
	 * 
	 * @param array $value
	 */
	public function addValues($values){
		if (is_object($values)) {
			$values = get_object_vars($values);
		}
		foreach($this->elements as $name=>$Element){
			if(isset($values[$name])){
				$Element->set_v($values[$name]);
			}
		}
		$this->values = array_merge($this->values,$values);
		return $this;		
	}
	
	/**
	 * Depends if we already have elements or not,
	 * this will build the elements (using some default or configuration)
	 * and set those elements values.
	 * Will reset the value of any existing element with no available
	 * value in the input.
	 * 
	 * @var mixed $values array or stdClass
	 * 
	 * @return Form
	 */
	public function setValues($values){
		if (is_object($values)) {
			$values = get_object_vars($values);
		}
		/*TODO figure out if I will use it
		if(isset($values['item'])){//This is for auto completion of tags, ITEM should be a reserved word from now on!
					  //I remove the item keyword.
			foreach($values['item'] as $k=>$item){
				$values[$k] = $item;
			}
		}*/

		$this->values = $values;
		foreach($this->elements as $name => $Element){
			if(isset($values[$name])){
				$Element->set_v($values[$name]);
			}else{
				$Element->set_v(null);
			}		
		}
		return $this;
	}

	/**
	 * 
	 */
  	public function getValue($name){
  		return $this->elements[$name]->v();
  	}
	/**
	 * @return array of values [name]=>[value]
	 */
  	public function getValues(){
  		$values = [];
  		foreach($this->elements as $name => $Element){
			$values[$name] = $Element->v();
  		}
		return $values;
  	}
	
  	public function getValuesAsURL(){
  	    return http_build_query($this->getValues());
  	}
  	
  	/**
	 * 
	 */
	public function isValid(){
  		foreach($this->elements as $Element){
  			/* @var $Element Form_Element_Simple */
			if(!$Element->validate()){
				$this->failed_validation = [
					'field'		=> $Element->name(),
					'validator' => $Element->last_used_validator(),
					'value'		=> $Element->v()
				];
				dbgr('FAILED VALIDATION',$this->failed_validation);
				return false;
			}
		}
		return true;
  	}
  	
  	/**
  	 * @return array with the validation errors
  	 */
  	public function getValidationError(){
  		return $this->failed_validation;
  	}
  	
  	/**
  	 * Each for needs to be save some how.
  	 * This to be main entry point for this process.
  	 * On extremly rare occasions, this can be ignored!
  	 * I would expect here to see some calls to hubs, if a more comples logic is required,
  	 * Please create a BL to wrap this logic.
  	 * 
  	 * @return Form
  	 */
	abstract public function save();
}
