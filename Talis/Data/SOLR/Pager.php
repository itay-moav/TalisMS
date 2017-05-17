<?php
/**
 * @author Itay Moav <2011>
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
class Data_SOLR_Pager extends Data_APager{
    use ViewSnipets_Data_Grid_SOPager;

    const THE_ANSWER_TO_EVERYTHING_IE_PAGER_INSIDE_OTHER_FORM = 42;
    const AJAX_FILTER = 'ajax_filter';
    
    //TODO find a better solution for trait and constants above. This was a quick response to upgrade to php7.1.1
    
	protected $storageNameSpace='__SOLR__Paginator_NS__';
	
	/**
	 * @var string SOLR core name we access
	 */
	protected $core = '';
	
	/**
	 */
	public function __construct($query,$order_by,$core='content',array $params=[],$page_size=BL_Aeon::PAGE_SIZE,$current_page=1){
		$this->storage=new Data_Session($this->storageNameSpace);
		$this->core = $core;
		$this->setOrderBy($order_by)
			->setQuery($query,$params)
			->setCurrentPage($current_page);
		$this->pageSize=$page_size;
	}//EOF CONSTRUCTOR
	
	/**
	 * @param string $i_sql
	 * @param sting order_by
	 * @param array $params
	 * 
	 * @return Data_SOLR_Pager
	 */
	protected function setQuery($i_sql,array $params) {
		return parent::setQuery($i_sql,$params);	
	}//EOF setSql
	
	/**
	 *  Set Order By for to keep query params consistent
	 *  @param $order_by
	 */
	public function setOrderBy($order_by){
		$this->order_by = $order_by;
		return $this;
	}
	
	/**
	 * Main method of this class. It will check if a count exists, if not it will creat one, calculate the rullers
	 * update the query with the LIMIT clause, run the query and return a result set.
	 *
	 * @return stdClass
	 */
	public function getPage() {
		//$query = "http://localhost:8080/solr/select/?q={$query}&version=2.2indent=on&wt=json";
		//run and return - for now, I put the call directly here
		//dbgr('INIT STARDUST',$query);
		$ret = Data_SOLR_DB::getInstance($this->core)->query($this->query,$this->params,$this->order_by,'',$this->getLimit());
		//$ret = json_decode(file_get_contents($query));
		$this->generateCountSql($ret);
		return($ret->response->docs);
	}//EOF function getPage
	
	/**
	 * Generates the Page count.
	 * 
	 * @return Search_SOLR_Pager
	 */
	protected function generateCountSql(stdClass $Result) {
		$this->count = $Result->response->numFound;
		$this->currentPageTotal = count($Result->response->docs);
		return $this;
	}//EOF generateCount

	/**
	 * get a LIMIT sql statment
	 *
	 * @return string SQL
	 */
	protected function getLimit() {
		$tips=$this->getLimitTips();
		return "&start={$tips['start']}&rows={$tips['end']}";
	}//EOF getLimit

	/**
	 * Enter description here...
	 *
	 * @return array (start,end)
	 */
	protected function getLimitTips() {
		$start=$this->current_page*$this->pageSize;
		$end=$this->pageSize;
		return ['start'=>$start,'end'=>$end];
	}
	
}//EOF CLASS
