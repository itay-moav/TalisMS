<?php
class Data_MySQL_Operator_Between extends Data_MySQL_Operator{
	private $v1, $v2;

	public function __construct($v1, $v2)
	{
		$this->setValue($v1, $v2);
	}

	public function setValue($v1, $v2)
	{
		$this->v1 = $v1;
		$this->v2 = $v2;
	}
	
	public function applyParameters($k2, &$p)
	{
		$p[$k2.'__1'] = $this->v1;
		$p[$k2.'__2'] = $this->v2;
	}

	public function getString($k2)
	{
		return " BETWEEN {$k2}__1 AND {$k2}__2";
	}

}
