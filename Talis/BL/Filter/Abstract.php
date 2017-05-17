<?php
/**
 * Rewrite of the old filter to match Aeonflux FW
 * 
 * @author 	Itay Moav
 * @date	22-07-2014
 * 
 * The abstarct filter for all filters
 */
abstract class BL_Filter_Abstract{
	/**
	 * Making sure indexes are same as elements names, will have to make sure about the local ids tooo
	 *
	 */
	static public function arrayBuilder(){
		$filter_elements=func_get_args();
		$ret=[];
		foreach($filter_elements as $FilterElement){
			$ret[$FilterElement->elementName]=$FilterElement;
		}
		return $ret;
	}

	/**
	 * Get back to you with the correct filter for your report class.
	 * If no specific filter exists it will return the current filter (self) i.e. default filter
	 * 
	 * @param object $owner, class owning the filter.
	 * @param array $request_params
	 * @return BL_Filter_Abstract|NULL
	 */
	public static function factory($owner,array $request_params=[]){
		$father_name = get_class($owner);
		
		//init filter
		$class_name=$father_name . 'Filter';//The filter class is defined in the same file as the report class itself and should have the exact same name + Filter.
											//The reasoning is to achieve something similar to Assembly in C#
		if(class_exists($class_name,false)){
			dbgn('in filter for ' . $father_name);
			$filter = new $class_name($father_name,$request_params);
			return $filter;
		}else{
			dbgn('No filter supplied');
			return null;
		}
	}	

	/**
	 * Decides if filter is cached and for how long
	 * @var integer
	 */
	public $cachForXSeconds = 0;
	/**
	 * @var string to manually append to filter caching, in case filter has different behaviors at different scenarios
	 * 				(like AD seeing catalog vs learner).
	 */
	public $cachAppend = '';	
	/**
	 * @var string owner of this filter (the DG that instantiated it)
	 */
	public $owner='';
	/**
	 * @var array $rawRequestParams
	 */
	public 	$rawRequestParams;
	/**
	 * @var array multi dim of filter elements.
	 * 
	 * Actual format must conform to the view file rendering it.
	 * Did not went on a general approach, may be in the future 
	 * given enough reason to do so.
	 */
	public	$filterElements=[];
	/**
	 * @var array of filter elements accessed by element name which the filter element creates
	 */
	public	$indexedFilterElements=[];
	
	/**
	 * Constructs & Retrieves the Where and Join statment from the filters 
	 * And the parameters of the query
	 * 
	 * @return array of WHERE | JOIN
	 */
	public function getWhereJoin(array &$param_array,$starlog_table='', $extra_params=''){
		$join='';
		$group_by=[];
		$where=[];
		foreach ($this->filterElements as $MyFilterElement){
			/*@var $MyFilterElement BL_Filter_Element_Abstract */
			if(!$MyFilterElement->isActivated()){
				continue;
			}
			$join.=$MyFilterElement->getSqlJoin($starlog_table,$extra_params);
			$w=$MyFilterElement->getSqlWhere($starlog_table,$extra_params);
			if($w){
				$where[]=$w;
			}
			if($MyFilterElement->getSqlGroupBy($starlog_table, $extra_params)){
				$group_by[]=$MyFilterElement->getSqlGroupBy($starlog_table, $extra_params);
			}
			$MyFilterElement->populateArray($param_array);
		}
		
		$where=implode(' AND ',$where);
		if($where){
			$where=' WHERE ' . $where;
		}
		
		return ['WHERE'=>$where,'JOIN'=>$join,'GROUPBY'=>$group_by];
	}
	
	/**
	 *  Gets filters for SOLR
	 */
	public function getSOLRWhere(){
		$where = [];
		foreach( $this->filterElements as $MyFilterElement){
			/*@var $MyFilterElement BL_Filter_Element_Abstract */
			if(!$MyFilterElement->isActivated()){
				continue;
			}
			$where[]=$MyFilterElement->getSqlWhere();
		}
		$where = implode(' AND ',$where);
		return $where;
	}

	/**
	 * Returns all the filters with values formated as a query string
	 * (this can be done inside getWhereJoin, but I'll leave it outside for better code maintainability)
	 * 
	 * @return string
	 */
	public function getFiltersAsQueryString(){
		$query_string='';
		foreach ($this->filterElements as $MyFilterElement){
			/*@var $MyFilterElement BL_Filter_Element_Abstract */
			$query_string .= $MyFilterElement->getAsQueryString();
		}
		return $query_string;
	}

	
	/**
	 * Late additions of parameters to the filter
	 * For now I will assume it is onlu hiddens and group IDs
	 *
	 * @param string $param_key
	 * @param mixed $param_value
	 */
	public function addParam($param_key,$param_value){
		foreach ($this->filterElements as $MyFilterElement){
			if($MyFilterElement->elementName == $param_key){
				$MyFilterElement->setRawValue($param_value);
				return $this;
			}
		}
		return $this;
	}
	
	/**
	 * GETTTT
	 * 
	 * @param string $param_key
	 */
	public function getParam($param_key){
		return isset($this->indexedFilterElements[$param_key])?$this->indexedFilterElements[$param_key]->getRawValue():null;
	}

	/**
	 * Removes a filter element (not destroying it) from the filter
	 * 
	 * @param string $param_key
	 * @return BL_Filter_Element_Abstract|NULL
	 */
	public function removeFilter(string $param_key):?BL_Filter_Element_Abstract{
	    $ret = null;
	    if(isset($this->indexedFilterElements[$param_key])){
	        $ret = $this->indexedFilterElements[$param_key];
	        unset($this->indexedFilterElements[$param_key]);
	        
	        foreach ($this->filterElements as $k=>$MyFilterElement){
	            if($MyFilterElement->elementName == $param_key){
	                unset($this->filterElements[$k]);
                    break;
	            }
	        }
	    }
	    return $ret;
	}

	/**
	 * 
	 * @param array $request_params
	 * @param string $report_name 
	 */
	public function __construct($owner,array $request_params){
		$this->init();
		$this->owner=$owner;
		$this->rawRequestParams=$request_params;
		dbgr('RAW PARAMS IN FILTER',$this->rawRequestParams);
		$this->constructElements();
	}//EOF constructor
	
	protected function init(){
		
	}
	
	/**
	 * Construct the filter elements under three sub categories(who, what, where).
	 * The order of elemens as created here is the order you will see them in the view!
	 * 
	 * @return SiTEL_Reports_Filter_Default
	 */
	abstract protected function constructElements();
}
