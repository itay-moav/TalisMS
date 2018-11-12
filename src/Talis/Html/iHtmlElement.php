<?php namespace Talis\Html;
/**
 * Any class that can be echoed as self contained
 * html element.
 * 
 * the __tostring is what should be used on the actual view
 * if the element is decorable, it should handle it inside his to string method.
 * 
 * @author itay
 *
 */
interface iHtmlElement{
    /**
     * This should be used most of the time
     * this should also implement the decoration, if
     * needed.
     * 
     * @return string
     */
    public function __toString():string;
    
    /**
     * Generates the pure html for this object.
     * 
     * @return string
     */
    public function html():string;
}

