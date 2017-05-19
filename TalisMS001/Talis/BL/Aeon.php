<?php
/**
 * @author 	Itay Moav
 * @date	07-15-2014
 * 
 * Define common API and functionality for new dataset functionality
 * Hopefully will provide better separation from presentation
 */
abstract class BL_Aeon{
	const	PROCESS_TYPE_NONE		= 2,
		 	PROCESS_TYPE_PROCESS	= 3,
		 	PROCESS_TYPE_PAGED		= 5,
		 	
			ORDER_BY				= 'order_by',
			ORDER_BY_DIRECTION		= 'order_by_dir',
			ORDER_BY_ASC			= 'asc',
			ORDER_BY_DESC			= 'desc',
			
			PAGE					= 1,
			PAGE_SIZE				= 100,
			PAGE_SIZE_AUTOPAGING	= 400
	;
	
	/**
	 * FOR IMMEDIATE RUN!
	 * 
	 * @param unknown $process_type
	 * @param array $params
	 * @param BL_iDataTransport $Resultset
	 * @param unknown $page
	 * @param unknown $page_size
	 * @return BL_iDataTransport
	 */
	static public function resultSet($process_type,array $params=[],BL_iDataTransport $Resultset=null,$page=self::PAGE,$page_size=self::PAGE_SIZE){
		return self::create($process_type,$params,$Resultset,$page,$page_size)->run()->getResultset();
	}
	
	/**
	 * Just create the object, u still need to RUN!
	 * 
	 * @param unknown $process_type
	 * @param array $params
	 * @param BL_iDataTransport $Resultset
	 * @param integer $page
	 * @param integer $page_size
	 * @return BL_Aeon
	 */
	static public function create($process_type,array $params=[],BL_iDataTransport $Resultset=null,$page=self::PAGE,$page_size=self::PAGE_SIZE){
		return new static($process_type,$params,$Resultset,$page,$page_size);
	}

	/**
	 * Do before autopaging
	 * @param array $params
	 */
	static protected function preAutoPaging(array $params = []) {	}
	
	static public function autoPagingData(array $params=[],BL_iDataTransport $Resultset=null,$page_size=self::PAGE_SIZE_AUTOPAGING){
		if(!$Resultset) $Resultset = new BL_Set_LokiFake;
	    
		static::preAutoPaging($params);
	    
		$page=1;
		$Reader = new static(self::PROCESS_TYPE_PAGED*self::PROCESS_TYPE_PROCESS,$params,$Resultset,$page,$page_size);
		$Resultset = $Reader->run()->getResultset();
		// TODO AEON INCONSISTENCIES: should be getPager() instead -- Holly
		//$num_of_pages = $Resultset->Pager->getTotalPages();
		$num_of_pages = $Resultset->getPager()->getTotalPages();
		for($page=2; $page<=$num_of_pages;$page++){
			$Reader = new static(self::PROCESS_TYPE_PAGED*self::PROCESS_TYPE_PROCESS,$params,$Resultset,$page,$page_size);
			$Reader->run();
		}
		return $Reader;
	}
		
	/**
	 * As opposed to the query above, here the result set is constantly changing 
	 * and getting smaller.
	 * Means the first page, technicaly is always the bnew page.
	 *
	 * @param unknown_type $params
	 * @param integer $page_size
	 * @return BL_Aeon
	 */
	static public function autoPagingManipulatedData(array $params=[],BL_iDataTransport $Resultset=null,$page_size=self::PAGE_SIZE_AUTOPAGING){
		if(!$Resultset) $Resultset = new BL_Set_LokiFake;
		
		static::preAutoPaging($params);
		
		$Reader = new static(self::PROCESS_TYPE_PAGED*self::PROCESS_TYPE_PROCESS,$params,$Resultset,1,$page_size);
		$Resultset = $Reader->run()->getResultset();
		// TODO AEON INCONSISTENCIES: should be getPager() instead -- Holly
		//$num_of_pages = ($Resultset->Pager->getTotalPages() -1);//The first page is allready taken care of, and won't be found again.
		$num_of_pages = ($Resultset->getPager()->getTotalPages() -1);//The first page is allready taken care of, and won't be found again.
		for($page=1; $page<=$num_of_pages;$page++){
			$Reader = new static(self::PROCESS_TYPE_PAGED*self::PROCESS_TYPE_PROCESS,$params,$Resultset,1,$page_size);
			$Reader->run();
		}
		return $Reader;
	}
	
	/**
	 * If the input where statment has no where in it, I'll add it with a mock condition (I assume it is followed by an AND)
	 *
	 * @param string $where
	 * @return sql	WHERE statment
	 */
	static protected function putWhere($where){
		if(!$where){
			return ' WHERE 1=1 ';	
		}
		return $where;
	}
	
	/**
	 * Generate group by string
	 * @param array $group_by
	 */
	static protected function putGroupBy($group_by){
		if(!empty($group_by)){
			$group_by = implode(',', $group_by);
			$group_by = ' GROUP BY '. $group_by;
			return $group_by;
		}
	
		return '';
	}

	/**
	 * The header class, if exists!
	 * 
	 * @var BL_Header_Abstract
	 */
	protected $Header = null;
	
	/**
	 * @var array the $params in the constructor
	 */
	protected $originalParams = [];
	/**
	 * @var array params I some time need to process.
	*/
	protected $params = [];
	
	/**
	 * @var array of parameter values to use in sql, this is for the prepared statments
	*/
	protected $paramArray = [];
	/**
	 * @var Data_MySQL_DB
	 */
	protected $DB;
	/**
	 * @var string ('READ','WRITE','REPORT')
	 */
	protected $db_type = Data_MySQL_DB::READ;
	/**
	 * The currntly processed row, this is a by ref assignment
	 *
	 * @var array ref
	 */
	protected $row = [];
	/**
	 * array or object is in the $this->row
	 * @var unknown
	 */
	protected $row_type = PDO::FETCH_OBJ;
	/**
	 * Pager values
	*/
	protected $page;
	protected $pageSize;
	/**
	 * Default value for the order by clause - overwritten by header
	 *
	 * @var string 
	 */
	protected $orderBy='';
	/**
	 * Order by direction - overwritten by header
	 *
	 * @var string 
	 */
	protected $orderByDirection=' ASC ';
	/**
	 * just for auto completion sake
	 * 
	 * @var BL_iDataTransport
	 */
	protected $Resultset;
	/**
	 * @var BL_Filter_Abstract $Filter to generate the result set upon
	 */
	protected $Filter;
	/**
	 * Mode of processing
	 * 2 - PROCESS_TYPE_NONE		
	 * 3 - PROCESS_TYPE_PROCESS
	 * 5 - PROCESS_TYPE_PAGED		
	 * 
	 * @var integer
	 */
	protected $process_mode = self::PROCESS_TYPE_NONE;

	public function __construct($process_type,array $params=[],BL_iDataTransport $Resultset=null,$page=self::PAGE,$page_size=self::PAGE_SIZE){
		$this->process_mode = $process_type;
		$this->setPaging($page, $page_size);
		$this->params = $this->originalParams=$params;
		$this->DB=Data_MySQL_DB::getInstance($this->db_type);
		
		$this->preInit()
			 ->generateFilter()
			 ->set($Resultset)
			 ->setOrderBy()
			 ->postInit()
		;
	}

	/**
	 * paging values
	 *
	 * @param integer $page
	 * @param integer $page_size
	 * @return BL_Aeon
	 */
	public function setPaging($page,$page_size){
		$this->page=$page;
		$this->pageSize=$page_size;
		return $this;
	}
	
	/**
	 * @param string $row_field
	 * @return BL_Aeon
	 */
	protected function butcher($row_field){
	    if($this->row_type == PDO::FETCH_ASSOC){
	        unset($this->row[$row_field]);
	    }else{
	        unset($this->row->$row_field);
	    }
		return $this;
	}

	/**
	 * This method should generate string with the query of this dataset.
	 * @return mixed resource/query to loop around.
	 */
	abstract protected function query();

	/**
	 * @function preInit
	 * Place holder for extra things to do at constructor, should be used
	 * only by abstract classes inheriting this one.
	 *
	 * @return BL_Aeon
	 */
	protected function preInit(){return $this;}
	
	/**
	 * @function postInit
	 * Place holder for extra things to do at constructor, should be used
	 * only by abstract classes inheriting this one.
	 *
	 * @return BL_Aeon
	 */
	protected function postInit(){return $this;}
	
	/**
	 * @function process
	 * The default process of a row - override in each report as necessary
	 */
	protected function process(){return $this;}
	
	/**
	 * @return BL_Header_Abstract
	 */
	protected function getHeader(){
		$class_name = static::class . 'Header';
		if(!$this->Header && class_exists($class_name,false)){
			$this->Header = new $class_name;
			$this->Resultset->setHeader($this->Header);
		}
		return $this->Header;
	}
	
	/**
	 * @function postProcess
	 * A place holder function (hookup) to be run
	 * logic once the entire processing steps are done.
	 *
	 * @return BL_Aeon
	 */
	protected function postProcess(){return $this;}

	/**
	 * The main entry point to the report generating algorithem
	 * 
	 * @return BL_Aeon
	 */
	final public function run(){
		$this->localGenerateResultset()
		  	 ->postProcess()
		;
		return $this;
	}

	/**
	 * Holds the specific structure of the filter for each data set
	 * This is the default behaviour - feel free to override it
	 * 
	 * @return BL_Aeon
	 */
	public function generateFilter(){
		$this->Filter = BL_Filter_Abstract::factory($this, $this->params);
		return $this;		
	}
	
	/**
	 * Sets this class data set, this is the default
	 * @return BL_Aeon
	 */
	protected function set(BL_iDataTransport $Resultset=null){
		$this->Resultset=$Resultset?:new BL_Set_AsSimpleAsApples;
		if($this->Filter) $this->Resultset->setFilter($this->Filter);
		return $this;
	}
	
	/**
	 * @return BL_iDataTransport
	 */
	public function getResultset(){
		return $this->Resultset;
	}
	
	/**
	 * set the order by field
	 *
	 * @param array $params
	 * @return BL_Aeon
	 */
	public function setOrderBy(){
		$order_by = $this->getParam(self::ORDER_BY);
		if($this->getHeader() && $order_by){
			$this->orderBy=$this->getHeader()->get_value($order_by);
		
			$order_by_dir = $this->getParam(self::ORDER_BY_DIRECTION);
			if($order_by_dir){
				$this->orderByDirection=($order_by_dir==self::ORDER_BY_DESC)?self::ORDER_BY_DESC:self::ORDER_BY_ASC;
			}
			
			$this->getHeader()->setOrderBy($order_by,$this->orderByDirection);
		}
		return $this;	
	}
	
	/**
	 * Holds the specific structure of the filter for each report object
	 * @return  @return BL_Filter_Simple
	 */
	public function getFilter(){
		return $this->Filter;	
	}	
	
	/**
	 * the most commonly used way of localGenerateDataset
	 * Overwrite this if u wish to have different method
	 * 
	 * @return BL_Aeon
	 */
	protected function localGenerateResultset(){
		return $this->defaultGenerateResultset();
	}

	/**
	 * A deault way to generate a data set.
	 * Will decide which method to run to get data
	 */
	protected function defaultGenerateResultset(){
		$name = 'GenerateResultset';
		if($this->process_mode%self::PROCESS_TYPE_PROCESS == 0){
			$name = 'processed' . $name;
		}
		
		if($this->process_mode%self::PROCESS_TYPE_PAGED == 0){
			$name = 'paged' . $name;
		}
		$query = $this->query() . $this->getOrderBySql();
		return $this->$name($query);
	}
	
	/**
	 * Straight forward data retrieval. Unless
	 * it needs filtering + ordering, it should really be inside 
	 * a SP
	 * 
	 * @param string $sql
	 * @return BL_Aeon
	 */
	protected function generateResultset($sql){
		$this->Resultset->setData($this->DB->select($sql,$this->paramArray)->fetchAllObj());
		return $this;
	}
	
	/**
	 * Returns a paged dataset by the page params
	 * 
	 * @param string $sql
	 * @return BL_Aeon
	 */
	protected function pagedGenerateResultset($sql){
		$this->Filter->getWhereJoin($this->paramArray);//TODO why is this here?!
		$Pager = new Data_MySQL_Pager($sql,$this->paramArray,$this->pageSize,$this->db_type);
		$Pager->setCurrentPage($this->page);
		$this->Resultset->setPager($Pager);
		$this->Resultset->setData($Pager->getPage($this->row_type));
		return $this;
	}
	
	/**
	 * Both paging the dataset
	 * and running the process method on top of it
	 * 
	 * @param string $sql
	 * @return BL_Aeon
	 */
	protected function processedGenerateResultset($sql){
		$Result = $this->DB->select($sql,$this->paramArray)->fetchAllObj();
		foreach($Result as $row){
			$this->row=&$row;
			$this->process();
			$this->Resultset->addLine($row);
		}
		
		return $this;
	}
	
	/**
	 * @param string $sql
	 * @return BL_Aeon
	 */
	protected function pagedprocessedGenerateResultset($sql){
		$Pager = new Data_MySQL_Pager($sql,$this->paramArray,$this->pageSize,$this->db_type);
		$Pager->setCurrentPage($this->page);
		// TODO AEON INCONSISTENCIES: should be setPager() instead -- Holly
		//$this->Resultset->Pager = $Pager;
		$this->Resultset->setPager($Pager);
		$Result = $Pager->getPage($this->row_type);
		foreach($Result as $row){
			$this->row=&$row;
			$this->process();
			$this->Resultset->addLine($row);
		}
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	protected function getOrderBySql(){
		if($this->orderBy){
			return " ORDER BY {$this->orderBy} {$this->orderByDirection}";
		}
		return '';
	}
	
	/**
	 * self explanatory, if u have simple fields in the select, u can use that 
	 * to manage the field list in select with the headers
	 */
	protected function extractFieldsFromDS(){
		$fields = [];
		foreach($this->Resultset->getHeaders() as $Header){
			$fields[]=is_string($Header)?$Header:$Header->getOrderBy();
		}
		return join(',',$fields);
	}
		
	/**
	 * Get the WHERE and JOIN statment is a default way from the filters
	 */
	protected function getWhereJoin($starlog_table='', $extra_params = null){
		$this->paramArray=[];//we might get usage of those params more then once in a report, so I need to clean those as not all are used on the same part
		$where_join = $this->Filter?$this->Filter->getWhereJoin($this->paramArray,$starlog_table, $extra_params):['WHERE'=>'','JOIN'=>'','GROUPBY'=>''];
		BL_Filter_Element_Abstract::resetAllreadyJoinedTables();
		return $where_join;
	}
	
	/**
	 * Return just the where condition, with no WHERE
	 */
	protected function justGetWhere($starlog_table='',$extra_params = null){
		$w_j = $this->getWhereJoin($starlog_table, $extra_params);
		return (strlen(trim($w_j['WHERE']))>5) ? $w_j['WHERE'] : '';
	}
	
	 /**
	  * Marks columns as N/A if condition isn't valid.
	  * Will also mark it in the Resultset, so if the entire coulumn is N/A it will hide it
	  *
	  * @param string $column		column in row, and the column index to decide if to hide or not in Resultset
	  * @param mixed  $condition	Condition to test by
	  * @param string $actual_data 	optional, if data value is different from the condition
	  * 
	  * @return BL_Aeon
	  */
	 protected function NA($column,$condition='bobo',$value='',$string=''){
         if($this->row_type == PDO::FETCH_ASSOC){
	         $column_value = (isset($this->row[$column]))?$this->row[$column]:-1111;
	     }else{
	         $column_value = (isset($this->row->$column))?$this->row->$column:-1111;
	     }
	     
	 	if($column_value == -1111 || ($condition=='bobo' && !$column_value) || !$condition){
			$column_value=$string;// N/A
		}elseif($value){
			$column_value=$value;
		}elseif($condition=='bobo'){
			//do nothing, keep the current value in the column
		}else{
			$column_value=$condition;
		}

		if($this->row_type == PDO::FETCH_ASSOC){
		    $this->row[$column] = $column_value;
		}else{
		    $this->row->$column = $column_value;
		}
		
		return $this;
	}
	
	//  Require a mandatory filter in the dataset.  Not all will need this as some are hardcoded into sql.
	protected function requireMandatory(){
		foreach($this->Filter->filterElements as $filterElement){
			if($filterElement instanceof DataEntities_Filter_Element_MandatoryHidden){
				return $this;
			}
		}
		throw new Exception("This class must have a mandatory filter");
	}
	
	/**
	 * @return BL_Aeon
	 */
	protected function getParam($param_key, $default = null){
		if(isset($this->params[$param_key])){
			return $this->params[$param_key];
		}
		return $default;
	}
	
	/**
	 * @return BL_Aeon
	 */
	protected function setParam($param_key,$param_value){
		$this->params[$param_key] = $param_value;
		return $this;
	}
	
	/**
	 * @return BL_Aeon
	 */
	protected function unsetParam($param_key){
		unset($this->params[$param_key]);
		return $this;
	}
}//EOF CLASS

