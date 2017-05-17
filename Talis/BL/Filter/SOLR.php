<?php
/**
 * @author 	Itay Moav/Preston
 * 
 * The abstarct filter for SOLR filters
 */
abstract class BL_Filter_SOLR extends BL_Filter_Abstract{
	/**
	 * Constructs & Retrieves the Where and Join statment from the filters 
	 * And the parameters of the query
	 * @param $param_array parameters being set
	 * @param $starlog_table table to get values from
	 * @param $extra_params unused
	 * @return string actual query
	 */
    public function getWhereJoin(array &$param_array,$starlog_table='', $extra_params=''){
		$where=array();
		foreach ($this->filterElements as $MyFilterElement){
			
			/*@var $MyFilterElement DataEntities_Filter_Element_Abstract */
			if(!$MyFilterElement->isActivated()){//is there any eaw value?
				continue;
			}
			$w=$MyFilterElement->getSqlWhere($starlog_table);
			if($w){
				if(!$MyFilterElement->solrFL){
					$where[]=$w;							
				}else{
					$param_array[]=$w;
				}
			}
				
			
		}
		return implode(' AND ',$where);
	}
}
