<?php
/**
 * Fake dataset, we do not need datasets 
 * for every thing. So this is to satisfy the BL contracts.
 * 
 * @author itaymoav
 */
class BL_Set_LokiFake implements  BL_iDataTransport {
    
    protected   $pager;
    
	public function setFilter(BL_Filter_Abstract $Filter = null){
		//buhahahah I do nothing :-DDDDD
		return $Filter;		
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
	

	
	/**
	 * TODO TEMP FIX added functions due to changes in Aeon for inconsistencies -- holly
	 */
	public function setHeader($header) {
	    //buhahahah I do nothing :-DDDDD
	    return $header;
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
}
