<?php
/**
 * Validates a value is greater than a given value of an array
 * Input ['row' => &$row, 'idx' => idx] as params
 * 
 * @author holly
 */
class Form_Validator_BindHigherThan extends Form_Validator_Abstract{
    /**
     * (non-PHPdoc)
     * @see Form_Validator_Abstract::validate()
     */
    public function validate($value){
        if ($value && $this->params['row'][$this->params['idx']] <= $value) {
            return true;
        }
        
        return false;
    }
}

