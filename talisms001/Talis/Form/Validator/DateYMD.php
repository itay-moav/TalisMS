<?php
/**
 * Validator for correct Y-m-d datetime format
* @author holly
*/
class Form_Validator_DateYMD extends Form_Validator_Abstract{
    protected $message = 'This is not a valid date format.';

    public function validate($value){
        return DateTime::createFromFormat('Y-m-d', $value);
    }
}