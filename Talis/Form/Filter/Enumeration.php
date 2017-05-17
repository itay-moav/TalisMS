<?php
/**
 * Filter the input into a table by limiting the input to 
 * available enumerations
 * @author Itay
 */
class Form_Filter_Enumeration implements Form_Filter_i{
	
	private $table,
			$fields,
			$enumerations,
			$default;
	
	/**
	 *  Filter parameters set
	 *  Param should be an array representing enum with default value first
	 *  @param string $table
	 *  @param string $fields
	 */
	public function __construct($table,$field){
		
		$sql	= "
			SHOW COLUMNS FROM {$table} WHERE field = :field;
				";
		$columns	= rwdb()->select($sql,[
			':field'	=> $field
		])->fetchAll();
		
		$sql	= "
			SELECT DEFAULT({$field}) FROM {$table} LIMIT 1
				";
		$default	= rwdb()->select($sql,[
			':field'	=> $field
		])->fetchAll();
		$enum				= $columns[0]['Type'];
		$enum				= substr($enum,5,-1);
		$this->enumerations	= explode(',',$enum);
		$this->default		= $default[0]["DEFAULT({$field})"];
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Form_Filter_i::filter()
	 */
    public function filter($data){
        return in_array($data, $this->enumerations)?$data:$this->default;
    }
}