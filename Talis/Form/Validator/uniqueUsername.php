<?php
/**
 * Digits
 * 
 * @author itaymoav
 */
class Form_Validator_uniqueUsername extends Form_Validator_Abstract{
    
    /**
     * Message needs concatenating ... 
     * 
     * @param string $overwrite_message
     * @param array $elm_specific_params
     */
	public function __construct($overwrite_message=false,array $elm_specific_params = []){
	    $this->message = 'An account already exists for this email address. <a href="' . commons\url\www() . '/login/restore/">Reset Your Password</a>';
	    parent::__construct($overwrite_message,$elm_specific_params);    
	}
	
	/**
	 * Checks there is something there,
	 * 
	 * @param mixed $value
	 * @return boolean
	 */
	public function validate($value){
	    if(!trim($value)){ //I ignore empty strings.
	        return true;
	    }
	    
		$res = IDUHub_Lms3users_UserIdentifier::quickSelect(['user_identifier'=>trim($value)],['rbac_user_id']);
		$status = true;//input is unique
		if($res && isset($res->rbac_user_id)){
			if(User_Current::id() && $res->rbac_user_id != User_Current::id()){
				$status = false;
			}elseif(!User_Current::id()){
				$status = false;
			}
		}
		return $status;
	}
}