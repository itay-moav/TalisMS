<?php
/**
 * Filter to handle an array of values where the relation between them is either OR or AND
 * The relation is decided by elm['andor'] OR is null AND is 'on'
 * Right now! the assumption there is also a linked table between all cartez tables (same table) 
 * 
 * cartezian filters are collection of filters all working on the same tables and are themselves a list of values
 * the filtered result (in an AND relation) is the cartezian multiplication/join of the table with itself X n# of possible filter values
 * 
 * @author Itay Moav
 * @Date   Mar-29th 2017
 */
abstract class BL_Filter_Element_Andor extends BL_Filter_Element_Abstract{
    public const ANDOR_AND   = 'and',
                 ANDOR_OR    = 'or',
                 ANDOR_INDEX = 'andor'
    ;
    
    /**
     * The joined table all cartezian filters in a group work on
     * @var string
     */
    protected $cartezian_table = '';
    
    /**
     * The default field the filter will work on
     * @var string
     */
    protected $table_field   = '';
    
    /**
     * Filter element that has to be calculated with this one.
     * It will be moved away from main filter if it is set
     * 
     * @var BL_Filter_Element_Andor
     */
    protected $cartezian_filter_elm = null;
    
    /**
     * How do we do refer to the list?
     * OR is default behavior
     * 
     * @var string
     */
    public $andor = 'or';
    
    public function __construct(BL_Filter_Abstract $Filter){
        parent::__construct($Filter);
        if(!isset($this->rawValue['andor']) || $this->rawValue['andor'] != 'on'){
            $this->andor = self::ANDOR_AND;
        } else {
            unset($this->rawValue['andor']);
            $this->andor = self::ANDOR_OR;
        }
        
        if(is_array($this->rawValue)){
            $this->rawValue = array_keys($this->rawValue);
        }
    }

    /**
     * init with the filter I need to caterz with
     * 
     * @param BL_Filter_Element_Abstract $Filter
     */
    public function cartez_with(BL_Filter_Element_Andor $FilterElm):void{
        $this->cartezian_filter_elm = $FilterElm;
        //destroy cartez filters elm in the filter, if they are coliding -> they will be calculated internaly
        $this->destroy_cartez();
    }
    
    /**
     * Only one cartezian filter can be lead.
     */
    protected function destroy_cartez(){
        //do this only if I am still alive in the filter
        if(!isset($this->ParentFilter->filterElements[$this->elementName])){
            return;
        }
        
        if($this->andor == self::ANDOR_AND){
            $this->ParentFilter->removeFilter($this->cartezian_filter_elm->elementName);
        } elseif ($this->andor == self::ANDOR_OR && $this->cartezian_filter_elm->andor == self::ANDOR_AND){ // remove self
            $this->ParentFilter->removeFilter($this->elementName);
        }
    }
    
    /**
     * for now this is used only for user search, otherwisem the default is no longer default
     * 
     * If I am AND, I remove the other filter and calculate internaly
     * If I am OR and the other one is AND, I remove myself (return nothing).
     * 
     * {@inheritDoc}
     * @see BL_Filter_Element_Abstract::whereDefault()
     */
    public function whereDefault($starlog_table=''){
        if($this->andor == self::ANDOR_OR){
            return Data_MySQL_Shortcuts::generateWhereData([$this->table_field => $this->rawValue],$this->params);
        } else {
            return '';
        }
    }
    
    /**
     * If I am AND, I remove the other filter and calculate internaly
     * If I am OR and the other one is AND, I remove myself (return nothing).
     *
     * {@inheritDoc}
     * @see BL_Filter_Element_Abstract::joinDefault()
     */
    public function joinDefault($join=''){
        if($this->andor == self::ANDOR_OR){
            return $this->join_or();
        } else {
            return $this->join_and();
        }
    }
}
