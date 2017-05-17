<?php
/**
 * Change any value to a numeric value, if it has one in it, or zero
 * @author Itay
 */
class Form_Filter_ToNumeric implements Form_Filter_i{
    public function filter($data){
        return $data*1;
    }
}