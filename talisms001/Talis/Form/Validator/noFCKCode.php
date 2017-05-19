<?php
class Form_Validator_noFCKCode extends Form_Validator_Abstract{
	protected $message = "Text is not valid";
	
	public function validate($value){
	    if (stripos($value,'CKEDITOR')!==false){
	        return false;
	    }
	    return true;
	}
	
}