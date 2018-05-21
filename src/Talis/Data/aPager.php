<?php namespace Talis\Data;
/**
 * @author Itay Moav <2008>
 * @license MIT - Opensource (File taken from PHPancake project)
 * 
 * Will be used to define the pager interface 
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
abstract class aPager{
	use \Talis\Services\Session\tHelper;

	protected	$pageSize,					//Page size to show.
				$current_page,				//current page requested.
				$count			= false,	//Number of rows expected from this query.
				$currentPageTotal,			//Total entries in this page.
				$query,						//The query I need to page.
				$key,						//The key in the cache for the sql.
				$params						//Params the query uses, we can have several similar base queries which differ only by the params (WHERE/filters etc)
				
	;
				
	/**
	 * @return integer page size
	 */
	public function getPageSize():int{
		return $this->pageSize;	
	}

	public function setPageSize($page_size){
		$this->pageSize=$page_size*1;
	}
	
	public function setCurrentPage($current_page):aPager{
		$this->current_page=($current_page-1)*1; //defaults to 0
		return $this;
	}

	/**
	 * Optional method to set manualy the count. Will save a query if there is pre knowledge of the count.
	 * Otherwise, will run a COUNT query once per new sql.
	 *
	 * @param integer $count
	 * @return lib_dbutils_SqlPager
	 */
	public function setCount(int $count):aPager{
		$this->count=$count*1; //*1 is to make it an int instead of a string
		$this->setSessionValue($this->key,$count);
		return $this;
	}

	public function getNextPageNumber():int {
		$c=$this->current_page;
		$s=$this->pageSize;
		$cn=$this->count;
		if((($c+1)*$s)>=$cn) {
			return 0;
		}else{
			return (++$c);
		}
	}//EOF getNextPageNumber
	
	public function getBackPageNumber():int{
		$c=$this->current_page;
		$s=$this->pageSize;
		$cn=$this->count;
	
		if($c<=0){
			return ((int)($cn/$s));
		}else{
			return (--$c);
		}
	}//EOF getBackPageNumber
	
	/**
	 * returns total entries in the query (without a limit)
	 * @return integer total entries
	 */
	public function getTotal():int{
		return $this->count*1;
	}//EOF getTotal
	
	/**
	 * returns number of records in this page
	 */
	public function getCurrentPageTotal():int {
		return $this->currentPageTotal;
	}//EOF getTotalThisPage
	
	public function getCurrentPage():int {
		return $this->current_page+1;
	}//EOF getCurrentPage

	/**
	 * @param string $query
	 * @param array $params
	 *
	 * @return aPager
	 */
	protected function setQuery(string $query,array $params):aPager {
		$this->query=$query;
		$this->params=$params;
		return $this;
	}
	
	/**
	 * Create the key to get the count, if it  is stored
	 *
	 * @return aPager
	 */
	protected function createKey():aPager{
		$params=print_r($this->params,true);
		$this->key=md5($this->query.$params);
		return $this;
	}
	
	/**
	 * @return string count key for current SQL
	 */
	protected function getKey():string {
		return $this->key;
	}
	
	/**
	 * returns number of pages in query.
	 * 
	 * @return integer number of pages in query
	 */
	public function getTotalPages():int {
		$total=$this->count/$this->pageSize;
		if($total>((int)$total)){
			$total++;
		}
		return ((int)$total);
	}//EOF getTotalPages
	
	/**
	 * Main method of this class. It will check if a count exists, if not it will creat one, calculate the rullers
	 * update the query with the LIMIT clause, run the query and return a result set.
	 */
	abstract public function getPage();
}//EOF CLASS
