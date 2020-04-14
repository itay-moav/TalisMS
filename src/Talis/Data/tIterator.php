<?php namespace Talis\Data;

/**
 * Helper trait for iteratoring through files that need iteration
 *
 * @author holly
 */
trait tIterator{
	/**
	 * Current row
	 */
	protected $row = true; //initial value, as iterator starts by asking questions here.
	
	/**
	 * Row number
	 * @var int row_index
	 */
	public $row_index = 0;
	
	/**
	 * Returns row index
	 * (non-PHPdoc)
	 * @see \Iterator::key()
	 * @return number
	 */
	public function key () {
		return $this->row_index;
	}
	
	/**
	 * Returns row
	 * @return multitype:
	 */
	public function valid () {
		return $this->row;
	}
	
	/**
	 * Return current row
	 */
	public function current() {
		return $this->row;
	}
}