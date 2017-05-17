<?php
/**
 * TODO: eventually the DL class and this class should have one common ancsestor
 * 
 * @author 	Itay Moav
 * @date	01-18-2011
 *
 * Centralized hub to do all Insert Delete Update (IDU) actions on a specific table  
 */
abstract class BL_Hub_Abstract{
	
	const 	LMS2ARCHIVE			= 'lms2archive',
			LMS2DELTA			= 'lms2delta',
			LMS2PROD			= 'lms2prod',
			LMS3USERS           = 'lms3users',
			
			STATUS__PENDING		= 'pending',
			STATUS__APPROVED	= 'approved',
			STATUS__REJECTED	= 'rejected',
			STATUS__EXPIRED		= 'expired',
			STATUS__ARCHIVED	= 'archived',
			STATUS__ENABLED		= 'enabled',
			STATUS__DISABLED	= 'disabled',
			STATUS__CANCELED	= 'canceled',
			
			DELTA_TYPE__EDIT	= 'edit',
			DELTA_TYPE__DELETE	= 'delete',
			DELTA_TYPE__CREATE	= 'create',
			LEFT_JOIN           = true
	;
	
	static public $LastInstance=null;  // ???? What is this used for??
	
	static protected $CachedResults = array();
	
	/**
	 * Databse name to manage IDU upon
	 *
	 * @var string database name
	 */
	protected $databaseName=self::LMS2PROD;
	/**
	 * Table name to manage IDU upon
	 *
	 * @var string table name
	 */
	protected $tableName='';
	/**
	 * Ids of the records we should affect
	 *
	 * @var mixed
	 */
	protected $recordsIds=null;
	public $failOnError=true;
	
	/**
	 * Determines if the class has a delta table to record changes
	 * 
	 * Defaults to false
	 */
	protected $hasDelta = false;
	
	protected $dataCleanRules = array();
	
	/**
	 *  Empties cache when necessary to prevent excessive memory usage
	 *  on exceptionally large queries
	 */
	static public function resetCache(){
		self::$CachedResults=[];
	}
	
	
	/**
	 * Caches the last Instance created of this class
	 * 
	 * @param mixed $id
	 * @return BL_Hub_Abstract
	 */
	static public function getInstance($id){
		return new static($id);
	}
	
	/**
	 *  Get the value of hasDelta variable
	 */
	static public function getHasDelta(){
		return self::getInstance(null)->hasDelta;
	}
	
	/**
	 *  Get the table name
	 */
	static public function getTableName(){
		return self::getInstance(null)->tableName;
	}
	
	/**
	 * @param unknown_type $where
	 * @param array $fields
	 * 
	 * @return stdClass
	 */
	static public function quickSelect($where=[],array $fields=['*']){
		$id=null;
		if(is_array($where) && isset($where['id'])){
			$id=$where['id'];
		}elseif(is_numeric($where)){
			$id=$where;
			$where=array();
		}
		
		$res = self::getInstance($id)->internalQuickSelect($where,$fields); 
		return $res;
	}
	
	/**
	 *
	 * @param string $table db.tbl or just tbl
	 * @param array $where
	 * @param array $fields
	 */
	static public function quickSelectJoin($table,array $where=[],array $fields=['*']){
	    return self::getInstance(null)->internalQuickSelectJoin($table,$where,$fields);
	}
	
	/**
	 *  This function is rarely used(2 places) consider replacing
	 *  @param unknown $where
	 *  @param unknown $fields
	 *  @param number $limit
	 *  @param string $as_array
	 *  @return stdObj
	 */
	static public function getHistory($where,$fields=array('*'),$limit=1,$as_array=false){
		return self::getInstance(null)->internalHistorySelect($where,$fields,$limit,$as_array); 
	}

	/**
	 * Used only inside of getHistory which is then called in two places.  Consider reworking or moving into Hub that uses them.
	 * TODO unite with select
	 * Return a stdObj with all the fields values in the table for the specified id/where statment
	 * 
	 * @var array $where not mandatory
	 * @var array $fields not mandatory will fetch all fields
	 *
	 * @return stdObj
	 */
	public function internalHistorySelect(array $where,array $fields=array('*'),$limit=1,$as_array=false){
		if($this->recordsIds){
			$where['id'] = $this->recordsIds;
		}
		$db = $this->databaseName;
		$this->databaseName = self::LMS2DELTA;
		$res = $this->selectFields($fields,$where,true," ORDER BY date_stored DESC LIMIT {$limit}");
		$this->databaseName = $db;
		if(count($fields) == 1 && $fields[0] != '*'){
			return $res->fetchAllColumn();
		}
		if($as_array){
			return $res->fetchAll();
		}
		return $res->fetchAllObj();
	}
	
	/**
	 * Get (try) a field from class cache, if not, fetches the entire row! 
	 * 
	 * @param integer $id
	 * @param string $field
	 * @return string
	 */
	static public function getFromCache($id,$field){
		$Hub = self::getInstance($id);
		
		$hub_name = get_class($Hub);
		
		if ($field == '*' && isset(self::$CachedResults[$hub_name][$id]) ){
			return self::$CachedResults[$hub_name][$id];
		}
		
		
		if(!isset(self::$CachedResults[$hub_name][$id]) || !isset(self::$CachedResults[$hub_name][$id]->$field)){
			$res = $Hub->internalQuickSelect();
					
			self::$CachedResults[$hub_name][$id] = $res;
		}
		
		
		if ($field == '*'){
			return self::$CachedResults[$hub_name][$id];
		}
		
		
		return self::$CachedResults[$hub_name][$id]->$field;
	}

	/**
	 *  Returns an array of objects unless only a single field is selected.
	 *  When a single field is selected an array is returned
	 *  
	 *  To get [id => [full record] use $mode = PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC and make sure id is the first field
	 *   
	 *  @return Data_MySQL_DB
	 */
	static public function select($where=[],array $fields=['*'],$append_sql='',$mode = PDO::FETCH_OBJ){
		$id=null;
		if(is_array($where) && isset($where['id'])){
			$id=$where['id'];
		}elseif(is_numeric($where)){
			$id=$where;
			$where=[];
		}
		
		return self::getInstance($id)->internalSelect($where,$fields,$append_sql,$mode);
	}
	
	/**
	 *  Returns an array of objects unless only a single field is selected.
	 *  When a single field is selected an array is returned
	 *  
	 *  To get [id => [full record] use $mode = PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC and make sure id is the first field
	 *  
	 *  @return Data_MySQL_DB
	 */
	static public function selectJoin($table,$where=[],array $fields=['*'],$append_sql='',$mode = PDO::FETCH_OBJ,$left_join=false){
		return self::getInstance(null)->internalSelectJoin($table,$where,$fields,$append_sql,$mode,$left_join);
	}
	
	/**
	 * Shortcut to get key/pair result
	 * 
	 * @param unknown $where
	 * @param array $fields
	 * @param string $append_sql
	 * @return Data_MySQL_DB
	 */
	static function selectKeyValue($where=[],array $fields=['*'],$append_sql=''){
	   return static::select($where,$fields,$append_sql,PDO::FETCH_KEY_PAIR); 
	}
	
	/**
	 * Shortcut to get key value pair result
	 * @param unknown $table
	 * @param unknown $where
	 * @param array $fields
	 * @param string $append_sql
	 */
	static public function selectJoinKeyPair($table,$where=[],array $fields=['*'],$append_sql=''){
		return self::selectJoin($table,$where,$fields,$append_sql,PDO::FETCH_KEY_PAIR);
	}
	
	/**
	 * COUNT
	 *
	 * @param array $whereData
	 * @param string $field (default *)
	 * @param boolean $distinct
	 * @return integer number of records found.
	 */
	static public function count(array $where=[],$field='*',$distinct=false){
		return self::getInstance(null)->internalCount($where,$field,$distinct);
	}
	
	/**
	 * @param string $table
	 * @param array $where
	 * @param string $field ONE FIELD ONLY!
	 * @param bool $distinct whether to add the DISTINCT keyword INSIDE the COUNT ... COUNT(DISTINCT ...)
	 */
	static public function countJoin($table,array $where=[],$field='*',$distinct=false){
	    $dis = $distinct?'DISTINCT ':'';
	    $field = "COUNT({$dis}{$field})";
	    return self::selectJoin($table,$where,[$field])[0];
	}
	
	/**
	 * DB time
	 */
	static public function time(){
		return self::getInstance(NULL)->internalTime();
	}
	
	
	/**
	 * THE FOLLOWING CONTAINS MISSING METHODS THAT MAY HAVE BEEN MISSED IN THE LAST MOVE
	 * Commented out for now by Holly -- reviewed by Preston
	 */
	
	/**
	 * get enum values from table based on field name
	 * @param string $field_name
	 * 
	 * @return array of enum values
	 *
	static public function getEnumValues($field_name){
		return self::getInstance(null)->internalGetEnumValues($field_name);
	}**/
	
	/**
	 * Get a HTML options list for form data
	 *
	 * @param string $value_field
	 * @param string $label_field
	 * @param array $where
	 *
	static public function getFormAsOptions($value_field,$label_field,array $where=array()){
		return self::getInstance(null)->internalGetFormAsOptions($value_field,$label_field,$where);
	}
	
	
	
	/**
	 * Create one record
	 *
	 * @param array $records
	 * @return integer last inserted array
	 */
	static public function createRecord(array $records){
		return self::getInstance(null)->create($records);
	}

	/**
	 * Create many records
	 *
	 * @param array $records
	 * @return boolean
	 */
	static public function createMultipleRecords(array $records){
		return self::getInstance(null)->insertMultipleData($records);
	}
	
	/**
	 * Create many records, on update - Duplicate
	 *
	 * @param array $records
	 * @return boolean
	 */
	static public function createUpdateMultipleRecords(array $records){
		return self::getInstance(null)->insertMultipleData($records,true);
	}
	
	/**
	 * Create on duplicate Update one or many records
	 *
	 * @param integer $id main entity id (like course, user, org)
	 * @param array $records
	 * @return integer last inserted array
	 */
	static public function createUpdateRecord($id,array $records){
		return self::getInstance($id)->insertData($records,true);
	}
	
	/**
	 * @param integer $id main entity id (like course, user, org)
	 * @param array $records
	 * @return integer last inserted array
	 */
	static public function updateRecord($id,array $data,array $where=array()){
		return self::getInstance($id)->updateData($data,$where)->numRows;
	}	
	
	/**
	 * @param mixed $id main entity id (like course, user, org)
	 * @param array $records
	 * @return integer last inserted array
	 */
	static public function deleteRecord($id,array $where=array()){
		return self::getInstance($id)->deleteData($where)->numRows;
	}	
	

	public function __construct($records_ids=null){
		$this->recordsIds=$records_ids;
		
		//making sure I have an array there for me to use.
		$hub_name = get_class($this);
		if(!isset(self::$CachedResults[$hub_name])) self::$CachedResults[$hub_name] = array();
		self::$LastInstance=$this;
	}
	
	protected function rddb(){
	    return rddb();
	}
	
	protected function rwdb(){
	    return rwdb();
	}
	
	protected function getDelta() {
		$config = app_env();
		if (isset($config['delta-tracking']) && isset($config['delta-tracking'][$this->tableName])) {
			$this->hasDelta = $config['delta-tracking'][$this->tableName];
		}
		return $this->hasDelta;
	}
		
	/**
	 *  Data Cleaning to fix problems with bad data sent to db that could break an insert or update
	 *  Applies various restrictions
	 *  TODO Make this code less iffy
	 *  @param 	array $data
	 *  @return array
	 */
	protected function cleanData($data) {
		//  Look for a filter class and use if it exists
		$filter_class	= get_class($this).'Filter';
		if(class_exists($filter_class,false)){
			$filter	= new $filter_class;
			$this->dataCleanRules	= $filter->setFilter();
			
			//  Set the rules based on classes in filter
			foreach($data as $k => &$d){
				if(isset($this->dataCleanRules[$k])){
					
					//  Apply each of the individual rules
					if(is_array($this->dataCleanRules[$k])){
						foreach($this->dataCleanRules[$k] as $data_clean_rule){
							$d	= $this->data_clean_rule->filter($d);
						}
					}else{
						$d	= $this->dataCleanRules[$k]->filter($d);
					}
				}
			}
		}else{
			
			//  Original data clean rules set as strings
			foreach($data as $k => &$d) {
				if (isset($this->dataCleanRules[$k])) {
					if (is_array($this->dataCleanRules[$k])) {
						
					} else {
						//  Instantiate rule and call filter
						$cleaner = new $this->dataCleanRules[$k]();
						$d = $cleaner->filter($d);
					}
				}
			}
		}
		return $data;
	}

	/**
	 * Wrapper for INSERT queries. Default sets the DB obj to a WRITE one.
	 * 
	 * @return Data_MySQL_DB
	 */
	protected function insert($sql,array $params=array()){
		return $this->rwdb()->insert($sql,$params);
	}//EOF insert

	/**
	 * Prepare an INSERT statment from array of input
	 * 
	 * Input is array where the keys are the field names in the DB
	 * and the values are the values to insert.
	 * ! DO NOT INCLUDE THE CONTROL FIELDS!
	 * 
	 * @return Data_MySQL_DB
	 */
	public function insertData(array $data, $on_duplicate_update=false){
		if($this->recordsIds && $on_duplicate_update){
			$data['id']=$this->recordsIds;
		}
		$data=[$data];
		return $this->insertMultipleData($data,$on_duplicate_update);
	}
	
	/**
	 * Create a multiple insert command.
	 * TODO (need to think about this) Will perform an insert using a batch of no more than 50 records each time.
	 * Will clean all incomming data.
	 * 
	 * I expect the data to arrive as ['field_name']=>$value
	 * 
	 * @param array $data (two dim array of data to input.
	 * @param string $table_name
	 * @param integer $db_type [optional] defaults to WRITE db connection
	 * 
	 * @return Data_MySQL_DB
	 */
	public function insertMultipleData(array $data,$on_duplicate_update=false){
		if(empty($data) || !is_array($data[key($data)])){
			return $this->rwdb()->lastInsertID = 0;
		}
		
		$datas = array_chunk($data, 50);
		$id = 0;
		foreach ($datas as $d) {
			$id = $this->insertMultipleDataRaw($d, $on_duplicate_update);
		}
		return $id;
	}
	
	protected function insertMultipleDataRaw(array $data,$on_duplicate_update=false){
		$this->preInsertEvent($data);
		$fields=array_keys(Data_MySQL_Shortcuts::cleanControlFields($data[0]));//get the field names for the insert
		$fields_str=join('`,`',$fields);
		$modified_by = User_Current::pupetMasterId();
		$sql="INSERT INTO {$this->databaseName}.{$this->tableName} (`{$fields_str}`,date_created,created_by,modified_by)\nVALUES\n";
		$params=array();
		
		foreach($data as $k=>$cell){
		
			$data[$k] = $this->cleanData(Data_MySQL_Shortcuts::cleanControlFields($cell));
			$sql .= '(:' . join("{$k},:",$fields) . "{$k},NOW(),{$modified_by},{$modified_by}),\n";
			foreach($fields as $field){
				$current_param_index = ':'.$field.$k;

				if( isset($data[$k][$field]) ){
					$current_value = $data[$k][$field];
					if(is_object($current_value) && get_class($current_value) == 'Data_MySQL_UnmodifiedSql'){
						$sql = str_replace($current_param_index,$current_value,$sql);
					}else{
						$params[$current_param_index] = $current_value;
					}
					
				}else{
					$params[$current_param_index] = null;
				}
			}
		}
	       
		$sql=substr($sql,0,-2);
		
		//on duplicate
		$sql=$this->onDuplicateSql($sql,$on_duplicate_update,$fields,$modified_by);
		$ret = $this->insert($sql,$params);
		$id = $ret->lastInsertID;
		if($this->getDelta()){
			if(count($data)===1){
				$data[0]['id'] = $id;
				$this->createHistoryRecord(reset($data),self::DELTA_TYPE__CREATE);
			}
		}
		$this->postInsertEvent($data, $id);
		return $id;
	}
	
	/**
	 * Creates a new entry
	 *
	 * @param array $data
	 * @return id of inserted record
	 */
	public function create(array $data){
		$this->preInsertEvent($data);
		return $this->insertData($data);
	}
	
	/**
	 * Generates the ON DUPLICATE part of the INSERT statment
	 */
	protected function onDuplicateSql($sql,$on_duplicate_update,$fields,$modified_by){
		if($on_duplicate_update){
			$sql.=' ON DUPLICATE KEY UPDATE ';
			foreach($fields as $field){
				$sql.=" `{$field}`=VALUES(`{$field}`),";
			}
			$sql.="modified_by={$modified_by}";
		}
		return $sql;
	}
	
	/**
	 * Wrapper for UPDATE queries. Default sets the DB obj to a WRITE one.
	 * 
	 * @return Data_MySQL_DB
	 */
	public function update($sql,array $params=array()){
		return $this->rwdb()->update($sql,$params);
	}//EOF update

	/**
	 * Creates an update from an array of data.
	 * It will explode each cell ofthe array into an AND condition. For more complex conditions,
	 * Write your own SQL.
	 * 
	 * @return Data_MySQL_DB
	 */
	public function updateData(array $values,array $where=[],$clean_values=true,$clean_where=true){
		//get SET fields
		$params=[];
		$this->recordsIds?$where['id']=$this->recordsIds:'';
		$values = $this->cleanData($values);
		$set=Data_MySQL_Shortcuts::generateSetData($values,$params,$clean_values);
		//Clean the where array and add to the $params array and rebuild the $where array
		$where_sql = Data_MySQL_Shortcuts::generateWhereData($where,$params,$clean_where);
		//sql
		if($this->getDelta()){
			$this->createHistoryRecord($where,self::DELTA_TYPE__EDIT);
		}
		$this->preUpdateEvent([$values,$where]);
		$sql="UPDATE 
					{$this->databaseName}.{$this->tableName}
				SET
					{$set}
				WHERE
					{$where_sql}";
		$ret= $this->update($sql,$params);
		$edited_rows = $this->rwdb()->numRows;
		$this->postUpdateEvent([$values,$where]);
		$this->rwdb()->numRows = $edited_rows;
		//remove cache result from hub
		if ($this->recordsIds){
			$hub_name = get_class($this);
			if(is_array($this->recordsIds)){
				foreach($this->recordsIds as $record_id){
					unset(self::$CachedResults[$hub_name][$record_id]);
				}
			}else{
				unset(self::$CachedResults[$hub_name][$this->recordsIds]);
			}
		}
		return $ret;					
	}
	
	/**
	 * Wrapper for DELETE queries. Default sets the DB obj to a WRITE one.
	 * 
	 * @return Data_MySQL_DB
	 */
	public function delete($sql,array $params=array()){
		return $this->rwdb()->delete($sql,$params);
	}//EOF delete

	/**
	 * Shortcut for simple DELETE queries.
	 * 
	 * @param array @where array of where clauses, ONLY ANDs
	 * @param boolean $clean_where wether to clean the where params or not. 
	 * 
	 * @return Data_MySQL_DB
	 */
	public function deleteData(array $where=array(),$clean_where=true){
		$params=[];
		$this->recordsIds?$where['id']=$this->recordsIds:'';
		$sql_where=Data_MySQL_Shortcuts::generateWhereData($where,$params,$clean_where);
		if($sql_where) $sql="DELETE FROM {$this->databaseName}.{$this->tableName} WHERE {$sql_where}";
		else throw new Exception('NO TRUNCATE IS ALLOWED - use where'); //$sql="TRUNCATE `{$this->tableName}`";
		if($this->getDelta()){
			$this->createHistoryRecord($where,self::DELTA_TYPE__DELETE);
		}
		$this->preDeleteEvent($where);
		$ret = $this->delete($sql,$params);
		$this->postDeleteEvent($where);
		return $ret;
	}
	
	/**
	 * @param mixed id or array of ids to delete by.
	 * 
	 * @return Data_MySQL_DB
	 */
	public function deleteByIds(){
		if(!$this->recordsIds) throw new Exception_MissingParam('No id was given');
		return $this->deleteData();
	}

	/**
	 * Verifies the process that tries to remove the record also has the ownership
	 * of that record
	 */
	public function isOwner(){
	    throw new Exception('You must implement isOwner');
	}
	
	/**
	 * 
	 * @return Data_MySQL_DB
	 */
	public function deleteRequest(){
		throw new Exception('You must implement deleteRequest');
	}
	
	/**
	 * Creates a duplicate record in a history table
	 * 
	 * @param array $where
	 * @param string $type (edit/delete) MANDATORY!
	 */
	public function createHistoryRecord(array $where=array(),$type) {
		$params=array();
		
		/**
		 * Alerting user/developer if missing meta data
		 * @var string
		 */
		$error_msg_missing_params = "History record created without reason or type for table {$this->databaseName}.{$this->tableName}. U need to setup message for this action.";
		$params[':expl'] = BL_Action_LogMsgs::getActionLogMsg()?:$error_msg_missing_params;
		$this->recordsIds?$where['id']=$this->recordsIds:'';
		$where=Data_MySQL_Shortcuts::generateWhereData($where,$params);
		$params[':user'] = User_Current::pupetMasterId();
		$params[':type'] = $type;
		
		$sql="
			INSERT INTO lms2delta.{$this->tableName}
			SELECT *,CURRENT_TIMESTAMP, :type, :expl, :user
			FROM {$this->databaseName}.{$this->tableName}
			WHERE {$where}
		";
		sleep(1);//to avoid duplication of PK
		return $this->insert($sql,$params);
	}
	
	/** Naghmeh
	 * Creates a duplicate record in a history table
	 *
	 * @param array $where
	 */
	public function createHistoryRecordForMerge($table_name,array $where, $merge_inst_id, $source_database='') {
	    $table_only = str_replace('.', '_', $this->tableName);
	    $params=array(':merge_inst_id' => $merge_inst_id);
	    $where=Data_MySQL_Shortcuts::generateWhereData($where,$params);
	    $sql="
	    INSERT IGNORE INTO {$this->databaseName}.{$table_only}
	        SELECT *, :merge_inst_id
	        FROM {$table_name}
	        WHERE {$where}
	    ";
	    return $this->insert($sql,$params);
	}
	
	
	/**
	 * Creates a duplicate record in a history table
	 * 
	 * TODO: this has to be protected! (Itay)
	 *
	 * @param array $where
	 * @param string $type (edit/delete) MANDATORY!
	 */
	public function createArchiveRecord(array $where=array()) {
	
		$params=array();
	
		$this->recordsIds?$where['id']=$this->recordsIds:'';
		$where=Data_MySQL_Shortcuts::generateWhereData($where,$params);
	
		$sql="
		INSERT INTO lms2archive.{$this->tableName}
		SELECT *
		FROM {$this->databaseName}.{$this->tableName}
		WHERE {$where}
		";
		return $this->insert($sql,$params);
	}
	
	/**
	 * Simple Archive queries.
	 *
	 * @param array @where array of where clauses, ONLY ANDs
	 * @param boolean $clean_where wether to clean the where params or not.
	 *
	 * @return Data_MySQL_DB
	 */
	public function archiveData(array $where=array(),$clean_where=true){
		$params=array();
		$this->recordsIds?$where['id']=$this->recordsIds:'';
		if($this->getDelta()){
			$this->createHistoryRecord($where,self::DELTA_TYPE__DELETE);
		}
		$this->createArchiveRecord($where);
		$where=Data_MySQL_Shortcuts::generateWhereData($where,$params,$clean_where);
		if($where) $sql="DELETE FROM {$this->databaseName}.{$this->tableName} WHERE {$where}";
		else throw new Exception('NO TRUNCATE IS ALLOWED - use where'); //$sql="TRUNCATE `{$this->tableName}`";
		return $this->delete($sql,$params);
	}
	
	/**
	 *  Revert archived record
	 *  
	 *  @param array $where
	 *  
	 *  @return Data_MySql_DB
	 */
	protected function revertArchiveRecord(array $where=array()){
		$params=array();
		
		$this->recordsIds?$where['id']=$this->recordsIds:'';
		$where=Data_MySQL_Shortcuts::generateWhereData($where,$params);
		
		if($where){
			$sql="
			INSERT INTO {$this->databaseName}.{$this->tableName}
				SELECT *
				FROM lms2archive.{$this->tableName}
				WHERE {$where}
			";
		}else{
			throw new Exception('Do not restore full archive table - use where');
		}
		
		return $this->insert($sql,$params);
	}
	
	/**
	 * 
	 * @param string $command INSERT | UPDATE | DELETE
	 * @param array $where
	 * @return string json encoded Events queue message {source:db,table:table,event_type: 'event_type', params:{field:value,field:value}}
	 */
    protected function sendEventMessage($command,$where){
        $msg = DataPusher_ActiveMQ_Publisher_Events::construct_message($this->databaseName,$this->tableName,$command,$where);
        return DataPusher_ActiveMQ_Publisher_Events::get_client()->publish($msg);
    }
    
	/**
	 * Event called before insert
	 *  @param array $params
	 * @return BL_Hub_Abstract
	 */
	protected function preInsertEvent(array $param){
		return $this;
	}
	
	/**
	 * Event called before deletion.
	 *  @param array $params
	 * @return BL_Hub_Abstract
	 */
	protected function preDeleteEvent(array $param){
		return $this;
	}
	
	/**
	 *  Event called before update
	 *  @param array $params
	 *  @return BL_Hub_Abstract
	 */
	protected function preUpdateEvent(array $param=[]){
		return $this;
	}
	
	/**
	 * Event called after insert
	 * @param array $params
	 * @param unknown $last_insert_id
	 * @return BL_Hub_Abstract
	 */
	protected function postInsertEvent(array $params, $last_insert_id){
		return $this;
	}
	
	/**
	 *  Event called after delete
	 *  @param array $params
	 *  @return BL_Hub_Abstract
	 */
	protected function postDeleteEvent(array $params){
		return $this;
	}
	
	/**
	 *  Event called after update
	 *  @param array $params
	 *  @return BL_Hub_Abstract
	 */
	protected function postUpdateEvent(array $params){
		return $this;
	}
	
	/**
	 *  Determine if the queries are within a transaction
	 *  Returns the number of nest transactions
	 *  @return integer
	 */
	static public function inTransaction(){
		return rwdb()->getTransaction();
	}
	
	/**
	 * Return a stdObj with all the fields values in the table for the specified id/where statment
	 * 
	 * @var array $where not mandatory
	 * @var array $fields not mandatory 9will fetch all fields
	 *
	 * @return stdObj
	 */
	public function internalQuickSelect(array $where=[],array $fields=['*']){
		
		if($this->recordsIds){
			if(is_array($this->recordsIds) ){
				switch(count($this->recordsIds)){
					case 1:
						$this->recordsIds = reset($this->recordsIds);
						break;
						
					default:
						warning('Internal quick select is beind passed multiple record ids ' . print_r($this->recordsIds,true));
						break;
					
				}
			}
			$where['id'] = $this->recordsIds;
		}
		
		$res = $this->selectFields($fields,$where,true,'LIMIT 1')->fetchObj();
		return $res;
	}

	/**
	 *
	 * @param String $table db.tbl
	 * @param array $where
	 * @param array $fields
	 * @return stdClass | NULL
	 */
	public function internalQuickSelectJoin($table,array $where=[],array $fields=['*']){
	    $join = "JOIN {$table} ON {$this->tableName}.id = {$table}.{$this->tableName}_id";
	    return $this->selectFields($fields,$where,true,'LIMIT 1',[],$join)->fetchObj();
	}
	
	/**
	 * Return a stdObj with all the fields values in the table for the specified id/where statment
	 * 
	 * @var array $where not mandatory
	 * @var array $fields not mandatory will fetch all fields
	 *
	 * @return stdObj
	 */
	public function internalSelect(array $where=[],array $fields=['*'],$append_sql='',$mode = PDO::FETCH_OBJ){
		$res = $this->selectFields($fields,$where,true,$append_sql);

		if(count($fields) == 1 && $fields[0] != '*'){
			return $res->fetchAllColumn();
		}
		return $res->fetchAll($mode);
	}

	/**
	 * Return a stdObj with all the fields values in the table for the specified id/where statment
	 *
	 * @var array $where not mandatory
	 * @var array $fields not mandatory 9will fetch all fields
	 *
	 * @return stdObj
	 */
	public function internalSelectJoin($table,array $where=[],array $fields=['*'],$append_sql='',$mode = PDO::FETCH_OBJ,$left_join = false){
	    $join = ($left_join?'LEFT ':'') . "JOIN {$table} ON {$this->tableName}.id = {$table}.{$this->tableName}_id";
	    $res = $this->selectFields($fields,$where,true, $append_sql,[],$join);
	
	    if(count($fields) == 1 && $fields[0] != '*'){
	        return $res->fetchAllColumn();
	    }
	    return $res->fetchAll($mode);
	}
	
	/**
	 * return the current mysql time
	 */
	public function internalTime(){
		return $this->rddb()->select("SELECT CONCAT(CURDATE(), ' ', CURTIME()) AS 'D'")->fetchObj()->D;
	}
	
	/**
	 * COUNT
	 *
	 * @param array $whereData
	 * @param string $field
	 * @param boolean $distinct
	 * @return integer number of records found.
	 */
	public function internalCount(array $where=array(),$field='*',$distinct=false){
		return $this->selectCount($where,$field,$distinct);
	}

	/**
	 * Quick method to get list of enum values for one field
	 *
	 * @param string $field_name
	 * 
	 * @return array of enum values
	 */
	public function internalGetEnumValues($field_name){
		return $this->rddb()->getEnumValues($this->databaseName . '.' . $this->tableName, $field_name);
	}
	
	/**
	 *  Functions moved from old DL to remove the need for DL
	 *  Selects a set of fields based on where array
	 *  @param $fields			Fields to be selected from query
	 *  @param $where			Where portion of select query
	 *  @param $clean_where		Boolean determining if where cleaning is needed
	 *  @param $concat_sql		String to be appended to the end of the query (Grouping and Limits)
	 *  @param $concat_params	Array of params that require concatination
	 */
	protected function selectFields( array $fields,array $where=array(), $clean_where=true, $concat_sql='',array $concat_params=array(),$join_stmt='')
	{
		$fields=join(',',$fields);
		$params=[];
		$where= Data_MySQL_Shortcuts::generateWhereData($where,$params,$clean_where);
		$params=array_merge($params,$concat_params);
		$where = $where ? ' WHERE ' . $where : '';
		$sql="SELECT {$fields} FROM {$this->databaseName}.{$this->tableName} {$join_stmt} {$where} {$concat_sql}";
		return $this->rddb()->select($sql,$params);
    }
	
	/**
	 *  Moved from DL
	 *  Performs a count.  Originally contained $table param.  Not need in Hub though.
	 *  @param array	$whereData
	 *  @param string	$field
	 *  @param string	$distinct
	 */
	protected function selectCount(array $whereData,$field='*',$distinct=false) {
		$distinct=$distinct?' DISTINCT ':'';
		$params=array();
		$where = Data_MySQL_Shortcuts::generateWhereData($whereData,$params,true);
		if($where)$where=' WHERE '.$where;
		else $where='';
		$sql = "
		SELECT
		COUNT({$distinct}{$field}) AS c
		FROM
		{$this->databaseName}.{$this->tableName}
		{$where}";
		$res = $this->rddb()->select($sql, $params)->fetchAllObj();
		return $res[0]->c;
	}
	
	/**
	 * Static call to update multiple row function
	 * @param array $data
	 * @return Ambigous <Data_MySQL_DB, Data_MySQL_DB>
	 */
	public static function updateMultipleRecords(array $data){
		
		return self::getInstance(null)->updateMultipleData($data);
	}
	
	/**
	 *  Update multiple records
	 *  @param array $data
	 */
	public function updateMultipleData(array $input_data){
		
		//  Validate that top level array is not empty
		if(empty($input_data)){
			throw new NoDataException;
		}
		
		$fields		= array_keys($input_data[0]);
		$database	= $this->databaseName;
		$table		= $this->tableName;
		
		//  Get Primary Key for validation
		$sql			= "SHOW KEYS FROM {$database}.{$table} WHERE key_name = 'PRIMARY'";
		$primary_key	= $this->rddb()->select($sql)->fetchAll();
		$primary_key	= $primary_key[0]['Column_name'];
		
		
		foreach($input_data as $row){
				
			//  Validate that data being included, more than just primary key
			if(count($row) < 2){
				throw new NoDataException;
			}
				
			//  Validate that the primary key is included with the data
			if(!array_key_exists($primary_key,$row)){
				throw new MissingPrimaryKeyException;
			}
				
			//  Validate that rows have the same fields
			if(array_keys($row) !== $fields){
				throw new InconsistentDataException;
			}
		}
		

		$fields_str		= join('`,`',$fields);
		$modified_by	= User_Current::pupetMasterId();
		
		$update_fields	= $fields;
		unset($update_fields[array_search($primary_key,$update_fields)]);
		
		
		$data_chunks	= array_chunk($input_data, 500);
		unset($input_data);
		
		foreach($data_chunks as $data){
		
		
		
		
		//  Create a temporary table matching the fields of the desired table.
		$sql	= 
"DROP TEMPORARY TABLE IF EXISTS {$database}.massupdate_{$table};
CREATE TEMPORARY TABLE {$database}.massupdate_{$table} ENGINE=MEMORY
SELECT `{$fields_str}` FROM {$database}.{$table} LIMIT 0;
INSERT INTO {$database}.massupdate_{$table} (`{$fields_str}`)
VALUES
";
		
		
		$params			= [];
		
		foreach($data as $k	=> $cell){
		
			$data[$k]	= $this->cleanData(Data_MySQL_Shortcuts::cleanControlFields($cell));
			$sql		.= '(:' . join("{$k},:",$fields) . "{$k}),\n";
			foreach($fields as $field){
				$current_param_index = ':'.$field.$k;
		
				if( isset($data[$k][$field]) ){
					$current_value = $data[$k][$field];
					if(is_object($current_value) && get_class($current_value) == 'Data_MySQL_UnmodifiedSql'){
						$sql = str_replace($current_param_index,$current_value,$sql);
					}else{
						$params[$current_param_index] = $current_value;
					}
						
				}else{
					$params[$current_param_index] = null;
				}
			}
		}
		
		$sql	= substr($sql,0,-2);
		$sql	.= ";";
		
		//  Update the main table with data from the temporary table.  Joining using the primary key.
		$sql	.= "

UPDATE {$database}.{$table}
JOIN {$database}.massupdate_{$table} mud
ON {$table}.{$primary_key} = mud.{$primary_key}
SET\n";
		
		foreach($update_fields as $field){
			$sql	.= "{$table}.{$field} = mud.{$field},\n";
		}
		
		
		$sql	.= 
"{$table}.modified_by = {$modified_by},
{$table}.date_modified = NOW();
DROP TEMPORARY TABLE IF EXISTS {$database}.massupdate_{$table};";
		
		$edit_ids	= ExtractField($data,$primary_key);
		
		//  Store delta
		if($this->getHasDelta()){
			$this->createHistoryRecord([$primary_key=>$edit_ids],'edit');
		}
		
		
		//  Perform the update
		$result	= $this->rwdb()->update($sql,$params);
		$this->rwdb()->closeCursor();
		}
		
		return $result;
	}
	
}//EOF CLASS


class MissingPrimaryKeyException extends Exception{
	
}

class InconsistentDataException extends Exception{
	
}

class NoDataException extends Exception{
	
}