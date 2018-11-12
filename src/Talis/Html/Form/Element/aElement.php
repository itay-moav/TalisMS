<?php namespace Talis\Html\Form\Element;

/**
 * 
 * @author itay
 *
 */
abstract class aElement implements \Talis\Html\iHtmlElement,\Talis\Html\Decorator\iFormElementDecorable{
    
    public const TYPE='abstract';

    /**
     * @var array of actual html attributes
     */
    protected $attr = [];
    
    /**
     * @var string text ONLY of the form label
     */
    protected $label;
    
    /**
     * @var \Talis\Html\Decorator\iDecorator
     */
    protected $decorator = null;
    
    /**
     * 
     */
    protected $default_value ='';
    
    
    /**
     *
     * @param String $label
     * @param String $name
     * @param String $type
     * @param array $attr
     * @param \Talis\Html\Decorator\iDecorator $decorator
     */
    function __construct(string $label, string $name, string $value='', array $attr=[], \Talis\Html\Decorator\iFormElementDecorator $decorator=null){
        $this->decorator = $decorator?:new \Talis\Html\Decorator\None;
        $this->attr = $attr;
        $this->attr['name']=$name;
        $this->set_id();
        $this->label = $label;
        $this->set_value($value);
        $this->attr['type'] = static::TYPE;
    }
    
    /**
     * sets the id
     */
    protected function set_id(){
        if (!isset($this->attr['id'])){
            $this->attr['id'] = str_replace(array('[',']'),'_',$this->attr['name']);
        }
    }
    
    /**
     * Sets the value for appropriate elements in below priority
     * 1. if value was set in the attributes then it will go with that value
     * 3. otherwise the defualt value will be assign to the element
     *
     * @return \Talis\Html\Form\Element\aElement
     */
    public function set_value(string $value):\Talis\Html\Form\Element\aElement{
        if($value){
            $this->attr['value']=$value;
        }
        
        if(!isset($this->attr['value'])){
            $this->attr['value'] = $this->default_value;
        }
        return $this;
    }
    /**
     * get the Name attr
     */
    public function name():string{
        return $this->attr['name'];
    }
    
    /**
     * I do not render it, as the decorator might
     * need to ask me some questions before he runs the 
     * render.
     * You can always render this element without a decorator
     * using the html() method
     */
    public function __toString():string{
        try {
            return $this->decorator->decorate($this);
            
        } catch(\Exception $e){
            \fatal($e->getMessage());
            return '';
        }
    }
    
    /**
     *
     */
    public function add_css_class(string $class){
        if (isset($this->attr['class'])){
            $this->attr['class'] .= " {$class}";
        } else {
            $this->attr['class'] = $class;
        }
    }
    
    /**
     * 
     *
     */
    public function get_id():string{
        return $this->attr['id'];
    }
    
    /**
     * Generates the element's string
     *
     * @return String
     */
    public function html():string{
        return '<input ' . $this->unpack_attr() . '/>';
    }
    
     /**
      *
      */
     public function get_value(){
         return $this->attr['value'];
     }
     
     /**
      *
      */
     public function get_label():string{
         return $this->label;
     }
     
     public function set_label(string $value):aElement{
         $this->label = $value;
         return $this;
     }
     
     /**
      *
      * @param \Talis\Html\Decorator\iDecorator $decorator
      * @return aElement
      */
     public function set_decorator(\Talis\Html\Decorator\iFormElementDecorator $decorator){
         $this->decorator = $decorator;
         return $this;
     }
     
     public function get_decorator():\Talis\Html\Decorator\iFormElementDecorator{
         return $this->decorator;
     }
     
     /**
      * From array to string which is html attributes legit
      * @return string
      */
     protected function unpack_attr():string{
         // Attribute string formatted for use inside HTML element
         $unpacked_attribs = '';
         foreach($this->attr as $k=>$v){
             $v = htmlspecialchars($v);
             $unpacked_attribs .= "{$k}='{$v}' ";
         }
         return $unpacked_attribs;
     }
}

