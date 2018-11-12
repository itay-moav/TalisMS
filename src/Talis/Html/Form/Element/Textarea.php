<?php namespace Talis\Html\Form\Element;

/**
 * @author itay
 */
class Textarea extends aElement{
    public const TYPE='';
    
    /**
     * Generates the element's string
     *
     * @return String
     */
    public function html():string{
        $attr = $this->attr;
        unset($this->attr['type']);
        $value = $this->attr['value'];
        unset($this->attr['value']);
        $res = '<textarea ' . $this->unpack_attr() . ">{$value}</textarea>";
        $this->attr = $attr;
        return $res;
    }
}

