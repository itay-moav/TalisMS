<?php
/**
 * email validation, the string only, no dns lookups
 * 
 * @author itaymoav
 */
class Form_Validator_emailAddress extends Form_Validator_Abstract{
	
	protected $message = 'The input is not a valid email address';
	
	/**
	 * Checks value is a valid email string
	 * 
	 * @param string $value
	 * @return boolean
	 */
	public function validate($value){
		$Email_Max_Chr = 3;	// max charachter in domains' extention   name@domain.xxx	<- 2x
		$Email_Min_Chr = 2;	// min charachter in domains' extention	  name@domain.xxx   <- 3x

		$atIndex = strrpos($value, "@");
		if (is_bool($atIndex) && !$atIndex){
	    	return false;
	   	}else{
			$domain = substr($value, $atIndex+1);
			$local = substr($value, 0, $atIndex);
			$localLen = strlen($local);
			$domainLen = strlen($domain);
			$d_parts = explode('.',$domain);
			
			if (count($d_parts)<1){
				// domain's extention doesn't exist
				return false;
			}else if(strlen($d_parts[count($d_parts)-1])>$Email_Max_Chr || strlen($d_parts[count($d_parts)-1])<$Email_Min_Chr){
				// domain's extention less than Email_Min_Chr or domain's extention greater than Email_Max_Chr 
				return false;
			}else if ($localLen < 1 || $localLen > 64){
				// local part length exceeded
				return false;
			} else if ($domainLen < 1 || $domainLen > 255){
				// domain part length exceeded
				return false;
			} else if ($local[0] == '.' || $local[$localLen-1] == '.'){
				// local part starts or ends with '.'
				return false;
			} else if (preg_match('/\\.\\./', $local)){
			// local part has two consecutive dots
			    return false;
			} else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)){
				// character not valid in domain part
				return false;
			} else if (preg_match('/\\.\\./', $domain)){
				// domain part has two consecutive dots
				return false;
			} else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local))) {
				// character not valid in local part unless 
				// local part is quoted
				if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\","",$local))){
					return false;
				}
			}elseif(stripos($value,'sitelms') !== false){
			    return false;
			}elseif(stripos($value,'noemail') !== false){
			    return false;
			}elseif(stripos($value,'noemial') !== false){
			    return false;
			}elseif(stripos($value,'noreply') !== false){
			    return false;
			}elseif(stripos($value,'medstar.com') !== false){
			    return false;
			}elseif(stripos($value,'noemcil@medstar') !== false){
			    return false;
			}
			elseif(stripos($value,'moemail') !== false){
			    return false;
			}
			elseif(stripos($value,'moemial') !== false){
			    return false;
			}
			elseif(stripos($value,'momail') !== false){
			    return false;
			}
			
			
		}
		return true;
	}//EOF method
	
}