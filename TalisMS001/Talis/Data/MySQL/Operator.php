<?php

/**
 * Does not work for insert
 */
class Data_MySQL_Operator
{
	protected $operator;
	protected $value;
	
	public function __construct($value, $operator='')
	{
		$this->value = $value;
		$this->operator = $operator;
	}

	public function getOperator()
	{
		return $this->operator;
	}
	
	public function getValue()
	{
		return $this->value;
	}

	public function applyParameters($k2,&$p)
	{
		$p[$k2] = $this->getValue();					
	}

	public function getString($k2)
	{
		return $this->getOperator() . ' ' .$k2;
	}

	/*
	 * STATIC FUNCTIONS
	 */

	static function gt($value)
	{
		return new Data_MySQL_Operator($value, '>');
	}

	static function lt($value)
	{
		return new Data_MySQL_Operator($value, '<');
	}

	static function gte($value)
	{
		return new Data_MySQL_Operator($value, '>=');
	}

	static function lte($value)
	{
		return new Data_MySQL_Operator($value, '<=');
	}
	
	static function notequal($value)
	{
		return new Data_MySQL_Operator($value, '<>');
	}

	static function between($value1, $value2)
	{
		return new Data_MySQL_Operator_Between($value1, $value2);
	}
	
	static function like($value)
	{
		return new Data_MySQL_Operator($value, ' LIKE ');
	}
	
	static function likePlus($value)
	{
		return self::like('%'.$value.'%');
	}
	
	static function orCondition($value)
	{
		return new Data_MySQL_Operator($value, ' OR ');
	}
	
	static function notNull()
	{
		return new Data_MySQL_Operator(NULL, ' IS NOT ');
	}
	
	/**
	 * Places a NOT IN inside of a query.  For use inside WHERE clause.
	 * @param $value
	 * @return Data_MySQL_Operator
	 */
	static function notIn($value)
	{
		return new Data_MySQL_Operator_NotIn($value);
	}
	
	static function isNull()
	{
		return new Data_MySQL_Operator(NULL, ' IS ');
	}

	static function sql($value)
	{
		return new Data_MySQL_Operator_SQL($value);
	}
	
	static function now(){
		return new Data_MySQL_Operator(NULL, ' = NOW() ');
	}
}
