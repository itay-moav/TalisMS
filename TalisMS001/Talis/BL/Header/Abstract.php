<?php
/**
 * Manage data header functionality
 * 1. Provide a map between datasources values (from mysql etc) and how values will 
 *    be displayed on fron end. Useful for sorting/ordering
 *   
 * 2. Can provide a short cut for various rendering of data headers (like auto generating the <thead>)
 * 
 * @author	itaymoav
 * @date	aug-21-2014
 */
abstract class BL_Header_Abstract{
	
	protected	$order_by		= '',
				$order_by_dir	= ''
	;
	
	/**
	 * Associative array, keys are user text, values are field names from the data source
	 * @var array
	 */
	protected $map = [];
	
	/**
	 * 
	 */
	public function __construct(){
		$this->map = $this->load_map();
	}
	
	/**
	 * 
	 * @return array:
	 */
	public function get_map(){
		return $this->map;
	}
	
	/**
	 * @return array of the keys, which are user text:
	 */
	public function get_map_titles(){
		return array_keys($this->map);
	}
	
	/**
	 * @param string $k
	 * @return string:
	 */
	public function get_value($k){
		return $this->map[$k];
	}
	
	/**
	 * Sets the ordering commands
	 * 
	 * @param string $order_by hasto be a key in the MAP
	 * @param string $order_by_direction asc or desc
	 */
	public function setOrderBy($order_by,$order_by_direction){
		$this->order_by = $order_by;
		$this->order_by_dir = $order_by_direction;	
	}

	/**
	 * Return the selected values
	 * 
	 * @return array of strings BL_Aeon::ORDER_BY && BL_Aeon::ORDER_BY_DIRECTION
	 */
	public function getSelectedOrder(){
		return [BL_Aeon::ORDER_BY=>$this->order_by,BL_Aeon::ORDER_BY_DIRECTION=>$this->order_by_dir];
	}
	
	/**
	 * @return array of associative  -> into map
	 */
	abstract protected function load_map(); 
}