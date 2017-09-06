<?php namespace Talis\Data\Filter;
/**
 * Trim an explicit string field
 * @author Itay
 */
class Trim implements i{
    public function filter($data){
        return trim($data);
    }
}
