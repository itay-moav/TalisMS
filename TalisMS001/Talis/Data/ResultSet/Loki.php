<?php namespace Talis\Data\ResultSet;
/**
 * A fake dataset storage unit.
 * Sometimes u want to use aeon loopers just to work, no need to store anything.
 * Pass them this
 * 
 * It will store the pager and the filter though
 * 
 * @author Itay Moav
 *
 */

class Loki implements i{
	private   	$pager,
				$QueryFilter
	;
	
	public function setFilter($QueryFilter){
		$this->QueryFilter = $QueryFilter;
		return $this->QueryFilter;
	}
	
	public function getFilter(){
		return $this->QueryFilter;
	}
	
	public function setData($v){
		//buhahahah I do nothing :-DDDDD
		return $v;
	}
	
	public function getData(){
		//buhahahah I do nothing :-DDDDD
		return null;
	}
	
	public function addLine($row){
		//buhahahah I do nothing :-DDDDD
		return $row;
	}

	public function setPager(\Talis\Data\aPager $Pager):i{
		$this->pager = $Pager;
		return $this;
	}
	
	public function getPager():?\Talis\Data\aPager{
		return $this->pager;
	}
	
	//--------------- ITERATOR INTERFACE -----------------------------------
	public function rewind()
	{
		
	}
	
	public function current()
	{
		return [];
	}
	
	public function key()
	{
		return null;
	}
	
	public function next()
	{
		return null;
	}
	
	public function valid()
	{
		return false;
	}
}