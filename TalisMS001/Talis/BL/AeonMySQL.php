<?php
/**
 *  Extension of BL Aeon that is used for MySQL based queries
 *  @author Preston
 */
abstract class BL_AeonMySQL extends BL_Aeon{
	
	/**
	 * @var @var Data_MySQL_DB
	 */
	protected $DB;
	/**
	 * @var string ('READ','WRITE','REPORT')
	 */
	protected $db_type = Data_MySQL_DB::READ;
	/**
	 * array or object is in the $this->row
	 * @var unknown
	 */
	protected $row_type = PDO::FETCH_OBJ;
	
	
	/**
	 * Straight forward data retrieval. Unless
	 * it needs filtering + ordering, it should really be inside
	 * a SP
	 *
	 * @param string $sql
	 * @return BL_AeonMySQL
	 */
	final protected function generateResultset($sql){
		$this->Resultset->setData($this->DB->select($sql,$this->paramArray)->fetchAll($this->row_type));
		return $this;
	}
	
	/**
	 * Returns a paged dataset by the page params
	 *
	 * @param string $sql
	 * @return BL_AeonMySQL
	 */
	final protected function pagedGenerateResultset($sql){
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
	 * @return BL_AeonMySQL
	 */
	final protected function processedGenerateResultset($sql){
		$Result = $this->DB->select($sql,$this->paramArray)->fetchAll($this->row_type);
		foreach($Result as $row){
			$this->row=&$row;
			$this->process();
			$this->Resultset->addLine($row);
		}
		
		return $this;
	}
	
	/**
	 * @param string $sql
	 * @return BL_AeonMySQL
	 */
	final protected function pagedprocessedGenerateResultset($sql){
		$Pager = new Data_MySQL_Pager($sql,$this->paramArray,$this->pageSize,$this->db_type);
		$Pager->setCurrentPage($this->page);
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
	 * @return string
	 */
	protected function getOrderBySql(){
	    if($this->orderBy){
	        return " ORDER BY {$this->orderBy} {$this->orderByDirection}";
	    }
	    return '';
	}
	
}