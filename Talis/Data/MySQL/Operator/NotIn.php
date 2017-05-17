<?php

class Data_MySQL_Operator_NotIn extends Data_MySQL_Operator
{
	private $params=array();
	
	/**
	 * Merges params for the sql
	 * @param unknown_type $k2
	 * @param unknown_type $p
	 * @return Data_MySQL_Operator
	 */
	public function applyParameters($key, &$p){
		$p = array_merge($p,$this->params);
		return $this;
	}

	
	/**
	 * Strips text for proper use in SQL and stores it in IN for merge then returns SQL string
	 * @see application/lib/SiTEL/Model/Data_MySQL_Operator#getString($k2)
	 */
	public function getString($k2){
		$IN=Data_MySQL_Shortcuts::generateInData($this->value,true,$k2);
		$this->params = $IN['params'];
		return ' NOT IN ' . $IN['str'];
	}

}
