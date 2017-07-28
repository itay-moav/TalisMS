<?php namespace Talis\Services\Sql;
/**
 * Purpose: a helper class to generate SQL statments from arrays of data.
 * This can be a factory dependng on the DB type (maybe I should use this too to add ORDER/LIMIT/CALC ROWS thingies
 */
class Shortcuts{

	const EMPTY_IN_VALUE = -12837;
	static private $param_count = array();
	
	/**
	 * Generates an IN values part.
	 * expects: [v0][v1][v2][v3]
	 * 
	 * returns array
	 * [str]='(:invv0,:invv1,:invv2,:invv3)'
	 * [params]=array(':invv0'=>v0,':invv1'=>v1,':invv2'=>v2,':invv3'=>v3)
	 * 
	 * If data does not need to be cleaned, returns [str]=('v1','v2','v3'....'vn');
	 * 
	 * @var array $values
	 * @var bolean $clean_data
	 * 
	 * @return array
	 */
	static public function generateInData(array $values,$clean_data=true,$prefix=''){
	    if (empty($values)) {
			return [
				'str'		=> '('.self::EMPTY_IN_VALUE.')',
				'params'	=> []
			];
		}
		
		if($prefix){
			$prefix=str_replace(array('`',"'",'_',':',' ','='),'',$prefix);
		}
		if($clean_data){
			$res=array('str'=>'(',
					   'params'=>array()
					);
			foreach ($values as $i => $value) {
				// ADDED BY MATT AS POC , changed by Itay 2.14 as another POC.
				if ($value === null) {
					$res['str'] .= ',NULL';
					continue;
				} // END POC
				
				if(!($value instanceof Data_MySQL_Operator_SQL)){
    				$index=":{$prefix}invv{$i}";
    				$res['str'].=',' . $index;
    				$res['params'][$index]=$value;
				}else{
				    $res['str'].=',' . $value->getString(null);
				}	
			}
			$res['str'] = str_replace('(,', '(', $res['str']);
			$res['str'].=')';
		}else{
			$res['str']="('".join("','",$values)."')";
		}
		
		return $res;
	}
	
	/**
	 *  From SiTEL_Model_Where which is being removed
	 *  Generate sql for a query
	 *  @param array $where
	 *  @param array $params
	 *  @return String
	 */
	public static function toSQL(array $where, array &$params) {
		$illegal_chars=array('.',',','(',')', ' ', "'", '"', '-','<','>','+');
		$tmp_params=array();
		$temp_where=array();
		foreach($where as $k=>$v){
			$param_key=str_replace($illegal_chars, '__', $k);
			// *This is to avoid parameter conflicts
			if (!isset(self::$param_count[$param_key])) {
				self::$param_count[$param_key] = 0;
			}
			self::$param_count[$param_key]++;
			$param_key .= '__'.self::$param_count[$param_key];
			// *End
			if(is_null($v)){ //IS NULL statments
				$sign=' IS NULL ';
			}elseif(is_array($v)){ //IN statments
				if (in_array(null, $v, true)) { // IN WITH IS NULL
					$IN=self::generateInData($v,true,$param_key);
					$sign=" IN {$IN['str']} ";
					$tmp_params=array_merge($tmp_params,$IN['params']);
					$temp_where[]="({$k}{$sign} OR {$k} IS NULL)";
					continue;
				} else { //STRAIGHT IN // ORIGINAL
					$IN=self::generateInData($v,true,$param_key);
					$sign=" IN {$IN['str']} ";
					$tmp_params=array_merge($tmp_params,$IN['params']);
				}
			} elseif($v instanceof Data_MySQL_Operator)
			{
				$k2 = ':' . $param_key;
				$sign = $v->getString($k2);
				$v->applyParameters($k2, $tmp_params);
			}/** elseif($v instanceof SiTEL_Model_Where)  SiTEL_Model Where being removed
			{
				$temp_where[]="({$v->toSQL($params)})";
				continue;
			} **/else
			{ //REGULAR statmens
				$tmp_params[':'.$param_key]=$v;
				$sign=" = :{$param_key}";
			}
			$temp_where[]="{$k}{$sign}";
		}
		$params=array_merge($params,$tmp_params);
		return join(' AND ', $temp_where);
	}
	
	/**
	 *  Reset parameter count
	 */
	static public function resetParamCount(){
		self::$param_count=[];
	}
	
	/**
	 * Get a where array [field]=value, [field]=value
	 * and returns the where string, It will update the params array
	 * if clean: field=:field AND field=:field
	 * if no clean field=value AND field=value
	 * 
	 * Knows how to handle IS NULL and IN statments
	 * Can use > or < operators if prepended to the value.
	 * 
	 * @return string where sql
	 */
	static public function generateWhereData($where,array &$params,$clean_where=true){
	    return self::toSQL($where, $params);
	    //TODO fix the below, for some reason it was rewritten badly....
	    
		//$illegal_chars=array('.',',','(',')', ' ', "'", '-');
		if(!$clean_where){
			//$Where = new SiTEL_Model_Where($where);
			return self::toSQL($where, $params);

		}else{
			$temp_where=array();
			foreach($where as $k=>$v){
				if(is_null($v)){ //IS NULL statments
					$sign=' IS NULL ';
				}elseif(is_array($v)){ //IN statments
					$IN="('".join("','",$v)."')";
					$sign=" IN {$IN} ";
				}else{ //REGULAR statmens
					$sign = preg_match('/[><]/', $k) ? $v : " = {$v}";
				}
				$temp_where[]="{$k}{$sign}";
			}
			$where=$temp_where;
		}
		$where=join(' AND ',$where);
		return $where;
	}
	
	/**
	 * Generate the SET statment part of an UPDATE sql statment
	 * 
	 * @var array $values (field=>value)
	 * @var array &$params the params array to be populated (passed by ref
	 * @var boolean $clean_values
	 * 
	 * @return string SET statment, without the "SET"
	 */
	static public function generateSetData(array $values,array &$params,$clean_values=true){
		$set=array();
		if($clean_values){
			foreach($values as $k=>$v){
				$params[':_'.$k]=$v;
				$set[]="{$k}=:_{$k}";
			}
		}else{
			foreach($values as $k=>$v){
				$set[]="{$k}={$v}";
			}
		}

		//Add modified by and modify date MW 4/12/10 now uses loggedIn
		$set[]='modified_by=' . User_Current::pupetMasterId();
		$set[]='date_modified=NOW()';
		$set=join(',',$set);
		return $set;	
	}
	
	/**
	 * A method to clean ALL control fields from an array of data which is supposed to e inserted/updated
	 */
	static public function cleanControlFields(array $data_to_clean){
		unset($data_to_clean['date_created']);
		unset($data_to_clean['date_modified']);
		unset($data_to_clean['created_by']);
		unset($data_to_clean['modified_by']);
		return $data_to_clean;		
	}
	
	/**
	 * Gets the current date in mysql format
	 * 
	 * @return string 'Y-m-d H:i:s' YYYY-MM-DD hh:mm:ss
	 */
	static public function now(){
	    return (new DateTime())->format('Y-m-d H:i:s');
	}
}