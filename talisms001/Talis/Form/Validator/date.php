<?php
/**
 * Validator for correct Y-m-d datetime format
* @author holly
*/
class Form_Validator_date extends Form_Validator_Abstract{
    protected $message = 'Not a valid date. Make sure date format is mm/dd/YYYY';

    public function validate($value){
        return DateTime::createFromFormat('m/d/Y', $value);
    }
}