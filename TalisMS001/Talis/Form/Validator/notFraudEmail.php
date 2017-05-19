<?php
/**
 * Digits
 * 
 * @author itaymoav
 */
class Form_Validator_notFraudEmail extends Form_Validator_Abstract{
	protected $message = 'This user name is not a valid email address';
	
	/**
	 * Checks there is something there,
	 * 
	 * @param mixed $value
	 * @return boolean
	 */
	public function validate($value){
		
		//  Check for fraudulent email addresses
		$is_email_fraud	= IDUHub_Lms2prod_Rbac_User::count([
			'username'			=> trim($value),
			'is_email_fraud'	=> 1
		]);
		
		$is_merged_email_fraud	= IDUHub_Lms3userMerge_Rbac_User::count([
			'username'			=> trim($value),
			'is_email_fraud'	=> 1
		]);
		
		if($is_email_fraud || $is_merged_email_fraud){
			return false;
		}
		
		return true;
	}
}