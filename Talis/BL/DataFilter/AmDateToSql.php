<?php
/**
 * Transforms mm/dd/yyyy to yyyy-mm-dd
 * @author Itay Moavs
 * 
 * TODO USE HOLLY'S CODE BECAUSE IT'S BETTER
 * NAGHMEH HELPED TO MAKE IT BETTER
 * Form_Filter_DateYMD
 */
class BL_DataFilter_AmDateToSql implements Form_Filter_i{
    public function filter($value){
        $parts = explode('/',$value);
        return "{$parts[2]}-{$parts[0]}-{$parts[1]}";
    }
}