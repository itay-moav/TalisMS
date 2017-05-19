<?php
/**
 * Adds MySQL db functionality
 * 
 * @author itaymoav
 */
trait BL_tDB{
	/**
	 * @var Data_MySQL_DB
	 */
	protected $DB=null;				//The main DB connection for this object

	/**
	 * @return Data_MySQL_DB
	 */
	protected function setDB(Data_MySQL_DB $DB=null,$type=Data_MySQL_DB::READ){
		return $this->DB=$DB?: Data_MySQL_DB::getInstance($type);
	}
	
	/**
	 * @return Data_Mysql_DB
	 */
	protected function DB(){
		return $this->DB;
	}

	/**
	 * @param string $method where transaction starts
	 *
	 * @return Data_MySQL_DB
	 */
	public function beginTransaction($method){
		return $this->DB->beginTransaction($method);
	}
	
	/**
	 * @param string $method where transaction ends
	 * 
	 * @return Data_MySQL_DB
	 */
	public function endTransaction($method){
		return $this->DB->endTransaction($method);
	}
	
	/**
	 * @param string $method where transaction rollbacks
	 * 
	 * @return Data_MySQL_DB
	 */
	public function rollbackTransaction($method){
		return $this->DB->rollbackTransaction($method);
	}
	
}
