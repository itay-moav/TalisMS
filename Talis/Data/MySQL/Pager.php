<?php
/**
 * @author Itay Moav <2008>
 * @license MIT - Opensource (File taken from PHPancake project)
 * 
 * Will be used to page on result sets/seraches and such
 * 
 *
 * 
 * Methods:
 * 
 * getPageSize			:		Returns the page size
 * 
 * getCurrentPage		:		Returns the current page number that was fetched.
 * 
 * getCurrentPageTotal	:		Returns number of records in this page.
 * 
 * getTotal				:		Returns total number of records in this search.
 * 
 * getTotalPages		:		Returns the total number of pages in the system.
 * 
 * getPage				:		Returns the dataset of data
 * 
 * setCurrentPage		:		Sets the pager to the correct page to fetch.
 * 
 * setCountSql			:		Sets the counting mechanizem to a user supplied SQL, to be used if none simple SQL
 * 								are used, or in some cases of optimization
 */
class Data_MySQL_Pager extends Data_APager{
	/**
	 * Shuster
	 *
	 * @var Data_MySQL_DB
	 */
	protected $DB;				//DB class.
	protected $storageNameSpace='PaginatorNS';
	protected $newCount=false;	//Decides wether to generate new count or not.

	/**
	 */
	public function __construct($sql,array $params,$page_size=BL_Aeon::PAGE_SIZE,$db_type=Data_MySQL_DB::READ){
		$this->DB=Data_MySQL_DB::getInstance($db_type);
		$this->setSession($this->storageNameSpace);
		$this->setQuery($sql,$params)
			 ->createKey();
		$this->setCount($this->getSessionValue($this->key));
		$this->pageSize=$page_size;
	}//EOF CONSTRUCTOR
	
	/**
	 * Main method of this class. It will check if a count exists, if not it will creat one, calculate the rullers
	 * update the query with the LIMIT clause, run the query and return a result set.
	 *
	 * @return lib_dbutils_RecordsetIterator
	 */
	public function getPage($fetch_type = PDO::FETCH_ASSOC) {
		//check and/or generate count
		$sql=$this->generateCountSql($this->query);
		
		//get LIMIT clause for the current page
		$limit=$this->getLimit();
		//Build SQL
		$sql=$sql.$limit;
		
		//run and return;
		$ret=$this->DB->select($sql,$this->params)
					  ->fetchAll($fetch_type);
		$this->currentPageTotal=$this->DB->numRows;
		if($this->newCount){
			$this->setCount($this->DB->select("SELECT FOUND_ROWS() AS total")->fetchObj()->total);
		}
		return($ret);
	}//EOF function getPage
	
	/**
	 * Generates the Page count. Regenerates when we hit last page or first page.
	 * @return string SQL with or without a count
	 */
	protected function generateCountSql($sql) {
		if(!$this->count
		    ||
		   $this->current_page==0 //a count might have been generated, but we are in the limits of the query
			|| 
		   $this->current_page==($this->getTotalPages()-1)
		   ){
			$this->newCount=true;
			$sql=preg_replace('"SELECT"','SELECT SQL_CALC_FOUND_ROWS ',$sql, 1);
		}
		return $sql;
	}//EOF generateCount

	/**
	 * Enter description here...
	 *
	 * @return array (start,end)
	 */
	protected function getLimitTips() {
		$start=$this->current_page*$this->pageSize;
		if($start>$this->count)	{
			$start=0;
		}
		$end=$this->pageSize;
		return ['start'=>$start,'end'=>$end];
	}
	/**
	 * get a LIMIT sql statment
	 *
	 * @return string SQL
	 */
	protected function getLimit() {
		$tips=$this->getLimitTips();
		return " LIMIT {$tips['start']},{$tips['end']} ";
	}//EOF getLimit
}//EOF CLASS
