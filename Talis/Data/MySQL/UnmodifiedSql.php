<?php
/**
 * A class to represent a pure SQL we should not change
 * old calss = SiTEL_DataStorage__Dbutils_Sql
 */
class Data_MySQL_UnmodifiedSql{
	private $sql;
	
	public function __construct($sql){
		$this->sql = $sql;
	}
	
	public function __toString(){
		return $this->sql;
	}
}