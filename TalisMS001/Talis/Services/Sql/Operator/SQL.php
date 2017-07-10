<?php
class Data_MySQL_Operator_SQL extends Data_MySQL_Operator{
	private $sql;

	public function __construct($sql)
	{
		$this->sql = " $sql";
	}
	
	public function applyParameters($k2, &$p)
	{
	}

	public function getString($k2)
	{
		return $this->sql;
	}

}
