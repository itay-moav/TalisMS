<?php
class Form_Validator_emailActive extends Form_Validator_Abstract{
	protected $message = "Email address is invalid.";
	
	public function validate($email){
		$user = IDUHub_Lms2prod_Rbac_User::quickSelect(['username'=>$email],['id']);
		
		$user_id = isset($user->id)?$user->id:0;
		
		if(!$user_id) return false;
		if(!IDUHub_Lms2prod_Organization_User_Enrollment::count(['rbac_user_id'=>$user_id, 'status'=>IDUHub_Lms2prod_Organization_User_Enrollment::STATUS__APPROVED])) return false;	
		
		return true;
	}
	
}