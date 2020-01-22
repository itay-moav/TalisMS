<?php namespace Talis\Html\Form\Element;

/**
 * Shortcuts to generates a Form element
 * and some Forms maybe)
 */
abstract class API
{
    
    /*------------------ ELEMENTS SHORTCUTS ---------------------*/
    
    /**
     * Generates a hidden field
     * 
     * @param string $name
     * @param string $value
     * @param array $attr
     * @return \Talis\Html\Form\Element\Hidden
     */
    public static function hidden(string $name,string $value='',array $attr=[]){
        return new Hidden($name, $value,$attr);
    }
    
    /**
     * TEXT field
     * 
     * @param string $label
     * @param string $name
     * @param string $value
     * @param array $attr
     * @param \Talis\Html\Decorator\iDecorator $decorator
     * @return \Talis\Html\Form\Element\Text
     */
    public static function text(string $label, string $name, string $value='', array $attr=[], \Talis\Html\Decorator\iFormElementDecorator $decorator=null){
        return new Text($label, $name, $value, $attr,$decorator);
    }
    
    public static function checkbox(string $label, string $name, string $value='', array $attr=[], \Talis\Html\Decorator\iFormElementDecorator $decorator=null){
        return new Checkbox($label, $name, $value, $attr,$decorator);
    }
    
    public static function radio(string $label, string $name, string $value='', array $attr=[], \Talis\Html\Decorator\iFormElementDecorator $decorator=null){
        return new Radio($label, $name, $value, $attr,$decorator);
    }
    
    public static function textarea(string $label, string $name, string $value='', array $attr=[], \Talis\Html\Decorator\iFormElementDecorator $decorator=null){
        return new Textarea($label, $name, $value, $attr,$decorator);
    }
}


