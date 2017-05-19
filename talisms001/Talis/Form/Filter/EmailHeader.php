<?php
/**
 * Formats data 
 * Removes unexpected characters
 *
 * @author Itay
 */
class Form_Filter_EmailHeader implements Form_Filter_i{
    /**
     * (non-PHPdoc)
     * @see Form_Filter_i::filter()
     */
    public function filter($data){
       $data = str_replace('´', '', $data);
       return $data;
    }
}