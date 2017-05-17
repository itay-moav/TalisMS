<?php
/**
 * TODO this is here just for sake of example.
 *
 * Trim an explicit string field
 * @author Itay
 */
class Form_Filter_Trim implements Form_Filter_i{
    public function filter($data){
        return trim($data);
    }
}