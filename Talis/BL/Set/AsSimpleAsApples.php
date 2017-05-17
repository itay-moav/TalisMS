<?php
/**
 * A very simple and primitive data set.
 * But has filter and pager hooks...
 * 
 * @author itaymoav
 *
 */
class BL_Set_AsSimpleAsApples implements BL_iPagedDataTransport,Iterator {
	protected	$data	= [],
				/**
				 * @var BL_Filter_Abstract
				 */
				$filter = NULL,
				/**
				 * @var Data_APager
				 */
				$pager	= null,
				/**
				 * @var BL_Header_Abstract
				 */
				$header=null,
				$count	= 0,
				$additional_params = []
	;
	
	/**
	 * @param BL_Header_Abstract $header
	 */
	public function setHeader(BL_Header_Abstract $header){
		$this->header = $header;
	}
	
	/**
	 * @return BL_Header_Abstract
	 */
	public function getHeader(){
		return $this->header;
	}
	
	public function setData($v){
		$this->data = $v;
		$this->count = count($v);	
	}
	
	public function getData(){
		return $this->data;	
	}
	
	public function setFilter(BL_Filter_Abstract $Filter = null){
		$this->filter = $Filter;
		return $Filter;
	}
	
	public function getFilter(){
		return $this->filter;
	}
	
	public function addLine($row){
		$this->data[]= $row;
		$this->count++;
		return $row;
	}
	
	public function setAdditionalParams($k,$v){
		$this->additional_params[$k] = $v;
	}
	
	public function getAdditionalParams($k){
		return $this->additional_params[$k];
	}
	
	/**
	 * Getter for number of lines
	 * 
	 * @return number
	 */
	public function c(){
		return $this->count;
	}
	
	/**
	 * @param Data_APager $Pager
	 */
	public function setPager(Data_APager $Pager){
		$this->pager = $Pager;
		return $this;
	}
	
	/**
	 * @return Data_APager $Pager
	 */
	public function getPager(){
		return $this->pager;
	}
	
//--------------- ITERATOR INTERFACE -----------------------------------
	public function rewind()
	{
	    reset($this->data);
	}
	
	public function current()
	{
	    return current($this->data);
	}
	
	public function key()
	{
	    return key($this->data);
	}
	
	public function next()
	{
	    return next($this->data);
	}
	
	public function valid()
	{
	    $key = key($this->data);
	    $var = ($key !== NULL && $key !== FALSE);
	    return $var;
	}	
	
}
