<?php
/**
 * Validator for correct Y-m-d H:i:s datetime format
* @author holly
*/
class Form_Validator_DatetimeYMDHIS extends Form_Validator_Abstract{
    protected $message = 'This is not a valid datetime format.';

    public function validate($value){
        return DateTime::createFromFormat('Y-m-d H:i:s', $value);
    }
}