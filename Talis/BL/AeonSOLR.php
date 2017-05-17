<?php
/**
 *  Extension of BL Aeon that is used for SOLR based queries
 *  @author Preston
 */
abstract class BL_AeonSOLR extends BL_Aeon{
	
	protected $db_type = 'content';
	
	/**
	 * @var Data_SOLR_DB
	 */
	protected $DB;
	
	protected function query(){
		$where =  $this->Filter->getSOLRWhere();
		return $where;
	}
	
	public function __construct($process_type,array $params=[],BL_iDataTransport $Resultset=null,$page=self::PAGE,$page_size=self::PAGE_SIZE){
		$this->process_mode = $process_type;
		$this->setPaging($page, $page_size);
		$this->params = $this->originalParams=$params;
		$this->DB = Data_SOLR_DB::getInstance($this->db_type);
	
		$this->preInit()
				->generateFilter()
				->set($Resultset)
				->setOrderBy()
				->postInit()
		;
	}
	
	/**
	 * Straight forward data retrieval. Unless
	 * it needs filtering + ordering, it should really be inside
	 * a SP
	 *
	 * @param array $query
	 * @return BL_AeonSOLR
	 */
	final protected function generateResultset($query){
		$this->Resultset->setData($this->DB->query($query, $this->paramArray, $this->orderBy, '', ''));
		return $this;
	}
	
	/**
	 * Returns a paged dataset by the page params
	 *
	 * @param array $query
	 * @return BL_AeonSOLR
	 */
	final protected function pagedGenerateResultset($query){
		$Pager = new Data_SOLR_Pager($this->Filter->getWhereJoin($this->paramArray, '', []),$this->orderBy,$this->db_type,$this->paramArray,$this->pageSize);
		$Pager->setCurrentPage($this->page);
		$this->Resultset->setPager($Pager);
		$this->Resultset->setData($Pager->getPage());
		return $this;
	}
	
	/**
	 * Both paging the dataset
	 * and running the process method on top of it
	 *
	 * @param array $query
	 * @return BL_AeonSOLR
	 */
	final protected function processedGenerateResultset($query){
		$Result = $this->DB->query($query, $this->paramArray,$this->orderBy,'','');
		foreach($Result as $row){
			$this->row=&$row;
			$this->process();
			$this->Resultset->addLine($row);
		}
	
		return $this;
	}
	
	/**
	 * @param array $query
	 * @return BL_AeonSOLR
	 */
	final protected function pagedprocessedGenerateResultset($query){
		$Pager = new Data_SOLR_Pager($query,$this->orderBy,$this->db_type,$this->paramArray,$this->pageSize);
		$Pager->setCurrentPage($this->page);
		$this->Resultset->Pager = $Pager;
		$Result = $Pager->getPage();
		foreach($Result as $row){
			$this->row=&$row;
			$this->process();
			$this->Resultset->addLine($row);
		}
	
		return $this;
	}
	
	/**
	 * A deault way to generate a data set.
	 * Will decide which method to run to get data
	 */
	final protected function defaultGenerateResultset(){
		$name = 'GenerateResultset';
		if($this->process_mode%self::PROCESS_TYPE_PROCESS == 0){
			$name = 'processed' . $name;
		}
	
		if($this->process_mode%self::PROCESS_TYPE_PAGED == 0){
			$name = 'paged' . $name;
		}
		$query = $this->query();
		return $this->$name($query);
	}
}