<?php
/**
 * Medstar network id/medstar email/employee id exists in feed DB
 * 
 * @author itaymoav
 * @date May-2016
 */
class Form_Validator_validUserIdentifier extends Form_Validator_Abstract{
	protected $message = 'This user identifier is not a valid MedStar identifier';
	
	/**
	 * Checks there is something there,
	 * Do notice it might be an employee id from
	 * a different org. That is fine, better validation 
	 * has to be done later on
	 * 
	 * @param string $value
	 * @return boolean
	 */
	public function validate($value){
	    //check identifier exists in system someware
	    if(IDUHub_Lms3users_UserIdentifier::count(['user_identifier' => $value])){
			return true;
		}
		return false;
	}
}