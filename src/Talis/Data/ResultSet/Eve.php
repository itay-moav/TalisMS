<?php namespace Talis\Data\ResultSet;
/**
 * The basic data set storage unit.
 * 
 * @author itaymoav
 *
 */
class Eve implements i{
	/**
	 * The actual collection of data
	 * @var array $data
	 */
	protected	$data	= [],
	
	/**
	 * @var BL_Filter_Abstract
	 */
	$Queryfilter = NULL,
	/**
	 * @var Data_APager
	 */
	$pager	= null,
	/**
	 * @var BL_Header_Abstract
	 */
	$header=null,
	/**
	 * Real page size
	 */
	$count	= 0,
	/**
	 * What ever
	 */
	$additional_params = []
	;
	
	/**
	 * @param BL_Header_Abstract $header
	 */
	/**TODO
	public function setHeader(BL_Header_Abstract $header){
		$this->header = $header;
	}*/
	
	/**
	 * @return BL_Header_Abstract
	 */
	/**TODO
	public function getHeader(){
		return $this->header;
	}
	*/
	
	public function setData($v){
		$this->data = $v;
		$this->count = count($v);
	}
	
	public function getData(){
		return $this->data;
	}
	
	public function setFilter($QueryFilter){
		$this->Queryfilter = $QueryFilter;
		return $QueryFilter;
	}
	
	public function getFilter(){
		return $this->Queryfilter;
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
	 * @param \Talis\Data\aPager $Pager
	 */
	public function setPager(\Talis\Data\aPager $Pager):i{
		$this->pager = $Pager;
		return $this;
	}
	
	/**
	 * @return \Talis\Data\aPager $Pager
	 */
	public function getPager():?\Talis\Data\aPager{
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
