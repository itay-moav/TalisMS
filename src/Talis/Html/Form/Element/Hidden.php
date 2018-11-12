<?php namespace Talis\Html\Form\Element;

/**
 * @author itay
 */
class Hidden extends aElement{
    public const TYPE='hidden';
    
    public function __construct(string $name, string $value='', array $attr=[]){
        parent::__construct('',$name,$value,$attr);
    }
}

