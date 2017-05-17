<?php
/**
 * employee id is unique and not used at all or just by this user
 * 
 * @author itaymoav
 */
class Form_Validator_uniqueEmployeeId extends Form_Validator_Abstract{
	protected $message = 'This employee id is already in use. You might have another account on LMS.';
	
	/**
	 * Checks there is something there,
	 * 
	 * @param mixed $value
	 * @return boolean
	 */
	public function validate($value){
		$value = trim($value);
		if(!$value) return true;
		
		$res = IDUHub_Lms2prod_Organization_User_Enrollment::quickSelect(['employment_id'=>$value,'organization_id'=>$this->params['org_id'],'feed_verified'=>1],['rbac_user_id']);
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