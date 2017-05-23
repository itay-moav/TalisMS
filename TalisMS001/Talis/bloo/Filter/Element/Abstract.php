<?php
/**
 * Same as the abstract, just removed excess code we no longer need
 * 
 * @author 	Itay Moav
 * @date	23-07-2014
 */
abstract class BL_Filter_Element_Abstract{
	
	static protected $allreadyJoinedTables=array();
	
	static protected $join_type = 'join';
	
	
	const EMPTY_FILTER_ELEMENT = 'EMPTYFILTERELEMENT';
	
	static public function resetAllreadyJoinedTables(){
		self::$allreadyJoinedTables=array();
	}
	
	/**
	 * I use this to cache the values I get in getSqlWhere() for the mysql binding
	 */
	protected $params=[];
	
	/**
	 * @var string Owner class name of the filter
	 */
	protected $owner='';
	
	/**
	 * Needs to be overidden in each concrete usage
	 * @var string $elementName key in the input array of values.
	 */
	public $elementName=''; 
	/**
	 * @var mixed $rawValue the raw value from the input array to the constructor.
	 */
	protected $rawValue;
	/**
	 * @var array of all the other filter elements values
	 */
	protected $otherRawValue;
	
	/**
	 * @var boolean used only with SOLR where some filter values should be separated from the query (q=... and fq=.....)
	 */
	public $solrFL=false;
	/**
	 * @var BL_Filter_Abstract
	 */
	public $ParentFilter=null;
	
	//----------------------------------------------------------------- methods -----------------------------------------------------
	
	/**
	 * Stores the relevant data piece to this element from an array of input data (like a POST array)
	 */
	public function __construct($Filter){
		$this->owner         = $Filter->owner;
		$this->rawValue      = $Filter->rawRequestParams[$this->elementName]??null;
		$this->otherRawValue = $Filter->rawRequestParams;
		$Filter->indexedFilterElements[$this->elementName]=$this;
		$this->ParentFilter  = $Filter;
	}//EOF constructor
	
	/**
	 * @return string relevant sql `where` statment.
	 */
	public function getSqlWhere($starlog_table='', $extra_params = null){
		if(!$this->rawValue) return '';
		$join_method='where' . $starlog_table;

		if(method_exists($this,$join_method)){
			return $this->{$join_method}($starlog_table, $extra_params);
		}
		$join_method='where' . $this->owner;

		if(method_exists($this,$join_method)){
			return $this->{$join_method}($starlog_table, $extra_params);
		}
		return $this->whereDefault($starlog_table, $extra_params);
	}

	/**
	 * populate the params array (by ref) with :param]=value
	 * 
	 * @return array
	 */
	public function populateArray(array &$params){
		$params=array_merge($params,$this->params);
	}
	
	
	public function getOwner(){
		return $this->owner;
	}
	
	
	/**
	 * The default where statment to call if no report specific exists
	 */
	abstract protected function whereDefault($starlog_table);

	/**
	 * This is the only part that is different between reports.
	 * How should I handle this,array or strategy or Lambda...?
	 * 
	 * @return string relevant sql `join` statment.
	 */
	protected function joinDefault($join=''){
		return $join;
	}
		
	/**
	 * @return string relevant sql `join` statment.
	 */
	public function getSqlJoin($starlog_table='', $extra_params=null){
		$join_method='join' . $starlog_table;
		if(method_exists($this,$join_method)){
			return $this->{$join_method}();
		}
		$join_method='join' . $this->owner;
		if(method_exists($this,$join_method)){
			return $this->{$join_method}($extra_params);
		}
		return $this->joinDefault();
	}
	
	/**
	 * Method to register all ready joined tables 
	 * so I won't join the same table twice. Still, it is
	 * very easy to by pass in the individual join methods of the filters.
	 */
	static protected function getJoinIfNotRegistered($table_name,$join, $join_type = 'join'){
		if(isset(self::$allreadyJoinedTables[$table_name])){
			return '';
		}
		
		self::setJoinType($join_type);
		self::$allreadyJoinedTables[$table_name]='';
		return $join;
	}
	
	static protected function getJoinType(){
		return self::$join_type;
	}
	
	static protected function setJoinType($join_type = 'join'){
		self::$join_type = $join_type;
	}
	
	/**
	 * @return string relevant sql `group by` statment.
	 */
	public function getSqlGroupBy($starlog_table='', $extra_params=null){
		$group_by_method='groupBy' . $starlog_table;
		if(method_exists($this,$group_by_method)){
			return $this->{$group_by_method}();
		}
		$group_by_method='groupBy' . $this->owner;
		if(method_exists($this,$group_by_method)){
			return $this->{$group_by_method}($extra_params);
		}
		return $this->groupByDefault();
	}
	
	/**
	 * Default Group By overwritten in child classes.  Allows for variable Group By
	 * @param string $group_by
	 * @return unknown
	 */
	protected function groupByDefault($group_by=''){
		return $group_by;
	}
	
	public function getAsQueryString(){
		if(is_array($this->rawValue)){
			$ret='';
			foreach($this->rawValue as $k=>$v){
				$ret .= "&{$this->elementName}[{$k}]={$v}";
			}
			return $ret;
		}
		if($this->rawValue){
			return '&' . $this->elementName . '=' . $this->rawValue;
		}
		return '';
	}
	
	/**
	 * @return boolean if to activate this filter element or not
	 */
	public function isActivated(){
		return $this->rawValue;
	}
	
	public function getRawValue(){
		return $this->rawValue;
	}
	
	public function setRawValue($param_value){
		$this->rawValue = $param_value;
		return $this;
	}
	
	public function getOtherRawValue(){
		return $this->otherRawValue;
	}
}
