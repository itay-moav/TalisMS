<?php
/**
 * Validator for corret h:i:s time format
 * @author holly
 */
class Form_Validator_TimeHIS extends Form_Validator_Abstract{
    protected $message = 'This is not a valid H:i:s time format.';
    
    public function validate($value){
        return DateTime::createFromFormat('H:i:s', $value);
    }
}