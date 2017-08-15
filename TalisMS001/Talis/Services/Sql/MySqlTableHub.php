<?php namespace Talis\Services\Sql;
/**
 * TODO: eventually the DL class and this class should have one common ancsestor
 *
 * @author 	Itay Moav
 * @date	01-18-2011
 *
 * Centralized hub to do all Insert Delete Update (IDU) actions on a specific table
 */
abstract class MySqlTableHub{
	
	const
			DELTA_TYPE__EDIT	= 'edit',
			DELTA_TYPE__DELETE	= 'delete',
			DELTA_TYPE__CREATE	= 'create'
	;
	
	/**
	 * Databse name to manage IDU upon
	 *
	 * @var string database name
	 */
	protected $database_name;
	
	/**
	 * Table name to manage IDU upon
	 *
	 * @var string table name
	 */
	protected $table_name;
	
	/**
	 * Determines if the class has a delta table to record changes
	 *
	 * Defaults to false
	 */
	protected $has_delta = false;
	
	/**
	 * Array of filters and dependencies to either fail or modify data before we insert/update
	 * @var array
	 */
	protected $data_clean_rules = [];
	
	/**
	 * @var MySqlClient
	 */
	protected $db_client = null;
	
	/**
	 * Caches the last Instance created of this class
	 *
	 * @param mixed $id
	 * @return MySqlTableHub
	 */
	static public function getInstance($db_name=''){
		return new static($db_name);
	}
	
	/**
	 * @param array $where
	 * @param array $fields
	 *
	 * @return stdClass or false if nothing found 
	 */
	static public function quickSelect(array $where=[],array $fields=['*']){
		return self::getInstance()->iQuickSelect($where,$fields);
	}
	
	/**
	 *
	 * @param string $table db.tbl or just tbl
	 * @param array $where
	 * @param array $fields
	 */
	static public function quickSelectJoin(string $table,array $where=[],array $fields=['*']):\stdClass{
		return self::getInstance()->iQuickSelectJoin($table,$where,$fields);
	}
	
	/**
	 *  Returns an array of objects unless only a single field is selected.
	 *  When a single field is selected an array is returned
	 *
	 *  To get [id => [full record] use $mode = PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC and make sure id is the first field
	 *
	 *  @return array
	 */
	static public function select(array $where=[],array $fields=['*'],$append_sql='',$mode = \PDO::FETCH_OBJ):array{
		return self::getInstance()->iSelect($where,$fields,$append_sql,$mode);
	}
	
	/**
	 *  Returns an array of objects unless only a single field is selected.
	 *  When a single field is selected an array is returned
	 *
	 *  To get [id => [full record] use $mode = PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC and make sure id is the first field
	 *
	 *  @return array
	 */
	static public function selectJoin(string $table,$where=[],array $fields=['*'],$append_sql='',$mode = \PDO::FETCH_OBJ,$left_join=false):array{
		return self::getInstance()->iSelectJoin($table,$where,$fields,$append_sql,$mode,$left_join);
	}
	
	/**
	 * Shortcut to get key/pair result
	 *
	 * @param unknown $where
	 * @param array $fields
	 * @param string $append_sql
	 * @return array
	 */
	static function selectKeyValue(array $where=[],array $fields=['*'],$append_sql=''):array{
		return static::select($where,$fields,$append_sql,\PDO::FETCH_KEY_PAIR);
	}
	
	/**
	 * Shortcut to get key value pair result
	 * @param unknown $table
	 * @param unknown $where
	 * @param array $fields
	 * @param string $append_sql
	 */
	static public function selectJoinKeyPair(string $table,$where=[],array $fields=['*'],string $append_sql=''):array{
		return self::selectJoin($table,$where,$fields,$append_sql,\PDO::FETCH_KEY_PAIR);
	}
	
	/**
	 * COUNT
	 *
	 * @param array $whereData
	 * @param string $field (default *)
	 * @param boolean $distinct
	 * @return integer number of records found.
	 */
	static public function count(array $where=[],$field='*',$distinct=false):int{
		return self::getInstance()->iCount($where,$field,$distinct);
	}
	
	/**
	 * @param string $table
	 * @param array $where
	 * @param string $field ONE FIELD ONLY!
	 * @param bool $distinct whether to add the DISTINCT keyword INSIDE the COUNT ... COUNT(DISTINCT ...)
	 */
	static public function countJoin($table,array $where=[],$field='*',$distinct=false):int{
		$dis = $distinct?'DISTINCT ':'';
		$field = "COUNT({$dis}{$field})";
		return self::selectJoin($table,$where,[$field])[0];
	}

	/**
	 * Create one record
	 *
	 * @param array $records
	 * @return integer last inserted array
	 */
	static public function createRecord(array $records):int{
		return self::getInstance()->insertData($records);
	}
	
	/**
	 * Create many records
	 *
	 * @param array $records
	 * @return boolean
	 */
	static public function createMultipleRecords(array $records,$ignore=false):int{
		return self::getInstance()->insertMultipleData($records,false,$ignore);
	}
	
	/**
	 * Create many records, on update - Duplicate
	 *
	 * @param array $records
	 * @return boolean
	 */
	static public function createUpdateMultipleRecords(array $records){
		return self::getInstance()->insertMultipleData($records,true);
	}
	
	/**
	 * Create on duplicate Update one or many records
	 *
	 * @param integer $id main entity id (like course, user, org)
	 * @param array $records
	 * @return integer last inserted array
	 */
	static public function createUpdateRecord($id,array $records){
		return self::getInstance()->insertData($records,true);
	}
	
	/**
	 * @param integer $id main entity id (like course, user, org)
	 * @param array $records
	 * @return integer last inserted array
	 */
	static public function updateRecord(array $data,array $where){
		return self::getInstance()->iUpdateRecord($data,$where)->numRows;
	}
	
	/**
	 * Static call to update multiple row function
	 * @param array $data
	 * @return
	 */
	public static function updateMultipleRecords(array $data){
		return self::getInstance()->updateMultipleData($data);
	}
	
	/**
	 * @param mixed $id main entity id (like course, user, org)
	 * @param array $records
	 * @return integer last inserted array
	 */
	static public function deleteRecord(array $where){
		return self::getInstance()->deleteData($where)->numRows;
	}
	
	public function __construct($db_name=''){
		$this->db_client = $db_name?Factory::getConnectionMySQL($db_name) :
		                            Factory::getDefaultConnectionMySql();
	}
	/**
	 * @return MySqlClient
	 */
	protected function db(){
		return $this->db_client;
	}
	
	/**
	 *  Apply filters and dependencies (validators)
	 *  @param 	array $data
	 *  @return array
	 */
	protected function cleanData($data) {
		return $data;
	}
	
	/**
	 * Wrapper for INSERT queries. Default sets the DB obj to a WRITE one.
	 *
	 * @return Data_MySQL_DB
	 */
	protected function insert($sql,array $params=array()){
		return $this->db()->insert($sql,$params);
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
	 * @return int
	 */
	public function insertMultipleData(array $data,$on_duplicate_update=false,$ignore=false):int{
		$datas = array_chunk($data, 50);
		$id = 0;
		foreach ($datas as $d) {
			$id = $this->insertMultipleDataRaw($d, $on_duplicate_update,$ignore);
		}
		return $id;
	}
	
	protected function insertMultipleDataRaw(array $data,$on_duplicate_update=false,$ignore=false){
		$put_ignore = $ignore?' IGNORE ':'';
		$data = $this->cleanData($data);
		$this->preInsertEvent($data);
		$fields=array_keys(Shortcuts::cleanControlFields($data[0]));//get the field names for the insert
		$fields_str=join('`,`',$fields);
		$modified_by = \User_Current::pupetMasterId();
		$sql="INSERT {$put_ignore} INTO {$this->database_name}.{$this->table_name} (`{$fields_str}`,date_created,created_by,modified_by)\nVALUES\n";
		$params=array();
		
		foreach($data as $k=>$cell){
			
			$data[$k] = $this->cleanData(Shortcuts::cleanControlFields($cell));
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
		if($this->has_delta){
			$this->createInsertDeltaRecord(count($data));
		}
		$this->postInsertEvent($data, $id);
		return $id;
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
	
	protected function createInsertDeltaRecord(int $n_of_records_just_created){
		$type        = self::DELTA_TYPE__CREATE;
		$explanation = Action_LogMsgs::getActionLogMsg()?:
		                 "History record created without reason or type for table {$this->databaseName}.{$this->tableName}. U need to setup message for this action.";
		$user        = \User_Current::pupetMasterId();
		$sql = "
			INSERT INTO {this.database_name}_delta.{$this->table_name}
			SELECT *, CURRENT_TIMESTAMP,'{$type}','{$explanation}',{$user}
			FROM {this.database_name}.{$this->table_name}
			ORDER BY date_created DESC
			LIMIT {$n_of_records_just_created}";//TODO make the order by the key, which means I should define it in the hub too.
		try{
			$this->insert($sql);
		} catch(Exception $e){
			throw new FailedDeltaRecordCreation("Failed creating INSERT delta records for {$this->databaseName}.{$this->tableName}");
		}
	}
	
	protected function createUpdateDeltaRecord(array $where){
		$type        = self::DELTA_TYPE__EDIT;
		$explanation = Action_LogMsgs::getActionLogMsg()?:
		"History record created without reason or type for table {$this->databaseName}.{$this->tableName}. U need to setup message for this action.";
		$user        = \User_Current::pupetMasterId();
		$params      = [];
		$where_sql   = Shortcuts::generateWhereData($where,$params,true);
		$sql = "
		INSERT INTO {this.database_name}_delta.{$this->table_name}
		SELECT *, CURRENT_TIMESTAMP,'{$type}','{$explanation}',{$user}
		FROM {this.database_name}.{$this->table_name}
		WHERE {$where_sql}
		";
		try{
			$this->insert($sql,$params);
		} catch(Exception $e){
			throw new FailedDeltaRecordCreation("Failed creating UPDATE delta records for {$this->databaseName}.{$this->tableName}");
		}
	}
	
	/**
	 * Wrapper for UPDATE queries. Default sets the DB obj to a WRITE one.
	 *
	 * @return Data_MySQL_DB
	 */
	public function update($sql,array $params=array()){
		return $this->db()->update($sql,$params);
	}//EOF update
	
	/**
	 * Creates an update from an array of data.
	 * It will explode each cell ofthe array into an AND condition. For more complex conditions,
	 * Write your own SQL.
	 *
	 * @return Data_MySQL_DB
	 */
	public function iUpdateRecord(array $values,array $where=[],$clean_values=true,$clean_where=true){
		//get SET fields
		$params=[];
		$values = $this->cleanData($values);
		$set=Shortcuts::generateSetData($values,$params,$clean_values);
		//Clean the where array and add to the $params array and rebuild the $where array
		$where_sql = Shortcuts::generateWhereData($where,$params,$clean_where);
		//sql
		if($this->has_delta){
			$this->createUpdateDeltaRecord($where);
		}
		$this->preUpdateEvent([$values,$where]);
		$sql="UPDATE
		{$this->database_name}.{$this->table_name}
		SET
		{$set}
		WHERE
		{$where_sql}";
		$ret= $this->update($sql,$params);
		$edited_rows = $this->db()->numRows;
		$this->postUpdateEvent([$values,$where]);
		$this->db()->numRows = $edited_rows;
		
		return $ret;
	}
	
	/**
	 * Wrapper for DELETE queries. Default sets the DB obj to a WRITE one.
	 *
	 * @return Data_MySQL_DB
	 */
	public function delete($sql,array $params=array()){
		return $this->db()->delete($sql,$params);
	}//EOF delete
	
	/**
	 * Shortcut for simple DELETE queries.
	 *
	 * @param array @where array of where clauses, ONLY ANDs
	 * @param boolean $clean_where wether to clean the where params or not.
	 *
	 * @return Data_MySQL_DB
	 */
	public function deleteData(array $where,$clean_where=true){
		$params=[];
		$sql_where=Shortcuts::generateWhereData($where,$params,$clean_where);
		if($sql_where) $sql="DELETE FROM {$this->database_name}.{$this->table_name} WHERE {$sql_where}";
		else throw new \Exception('NO TRUNCATE IS ALLOWED - use where'); //$sql="TRUNCATE `{$this->tableName}`";
		
		$this->preDeleteEvent($where);
		$ret = $this->delete($sql,$params);
		$this->postDeleteEvent($where);
		return $ret;
	}
	


	/**
	 *
	 * @param string $command INSERT | UPDATE | DELETE
	 * @param array $where
	 * @return string json encoded Events queue message {source:db,table:table,event_type: 'event_type', params:{field:value,field:value}}
	 */
	protected function sendEventMessage($command,$where){
		$msg = DataPusher_ActiveMQ_Publisher_Events::construct_message($this->database_name,$this->table_name,$command,$where);
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
	 *  Event called before a mass update
	 *  @param array $params
	 *  @return BL_Hub_Abstract
	 */
	protected function preMultipleUpdateEvent(array $param=[]){
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
	 *  Event called after mass update
	 *  @param array $params
	 *  @return BL_Hub_Abstract
	 */
	protected function postMultipleUpdateEvent(array $params){
		return $this;
	}

	/**
	 * Return a stdObj with all the fields values in the table for the specified id/where statment
	 *
	 * @var array $where not mandatory
	 * @var array $fields not mandatory 9will fetch all fields
	 *
	 * @return \stdObj|false
	 */
	public function iQuickSelect(array $where=[],array $fields=['*']){
		return $this->selectFields($fields,$where,true,'LIMIT 1')->fetchObj();
	}
	
	/**
	 *
	 * @param String $table db.tbl
	 * @param array $where
	 * @param array $fields
	 * @return ?\stdClass
	 */
	public function iQuickSelectJoin($table,array $where=[],array $fields=['*']):?\stdClass{
		$join = "JOIN {$table} ON {$this->table_name}.id = {$table}.{$this->table_name}_id";
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
	public function iSelect(array $where=[],array $fields=['*'],$append_sql='',$mode = \PDO::FETCH_OBJ):array{
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
	public function iSelectJoin($table,array $where=[],array $fields=['*'],$append_sql='',$mode = \PDO::FETCH_OBJ,$left_join = false):array{
		$join = ($left_join?'LEFT ':'') . "JOIN {$table} ON {$this->table_name}.id = {$table}.{$this->table_name}_id";
		$res = $this->selectFields($fields,$where,true, $append_sql,[],$join);
		
		if(count($fields) == 1 && $fields[0] != '*'){
			return $res->fetchAllColumn();
		}
		return $res->fetchAll($mode);
	}
	
	/**
	 * COUNT
	 *
	 * @param array $whereData
	 * @param string $field
	 * @param boolean $distinct
	 * @return integer number of records found.
	 */
	public function iCount(array $where=array(),$field='*',$distinct=false):int{
		return $this->selectCount($where,$field,$distinct);
	}
	
	/**
	 * Quick method to get list of enum values for one field
	 *
	 * @param string $field_name
	 *
	 * @return array of enum values
	 */
	public function iGetEnumValues($field_name):array{
		return $this->db()->getEnumValues($this->database_name . '.' . $this->table_name, $field_name);
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
	protected function selectFields( array $fields,array $where=array(), $clean_where=true, $concat_sql='',array $concat_params=array(),$join_stmt=''):MySqlClient
	{
		$fields=join(',',$fields);
		$params=[];
		$where= Shortcuts::generateWhereData($where,$params,$clean_where);
		$params=array_merge($params,$concat_params);
		$where = $where ? ' WHERE ' . $where : '';
		$sql="SELECT {$fields} FROM {$this->database_name}.{$this->table_name} {$join_stmt} {$where} {$concat_sql}";
		return $this->db()->select($sql,$params);
	}
	
	/**
	 *  Moved from DL
	 *  Performs a count.  Originally contained $table param.  Not need in Hub though.
	 *  @param array	$whereData
	 *  @param string	$field
	 *  @param string	$distinct
	 */
	protected function selectCount(array $whereData,$field='*',$distinct=false):int {
		$distinct=$distinct?' DISTINCT ':'';
		$params=array();
		$where = Shortcuts::generateWhereData($whereData,$params,true);
		if($where)$where=' WHERE '.$where;
		else $where='';
		$sql = "
		SELECT
		COUNT({$distinct}{$field}) AS c
		FROM
		{$this->database_name}.{$this->table_name}
		{$where}";
		$res = $this->db()->select($sql, $params)->fetchAllObj();
		return $res[0]->c;
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
		$database	= $this->database_name;
		$table		= $this->table_name;
		
		//  Get Primary Key for validation
		$sql			= "SHOW KEYS FROM {$database}.{$table} WHERE key_name = 'PRIMARY'";
		$primary_key	= $this->db()->select($sql)->fetchAll();
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
		$modified_by	= \User_Current::pupetMasterId();
		
		$update_fields	= $fields;
		unset($update_fields[array_search($primary_key,$update_fields)]);
		
		
		$data_chunks	= array_chunk($input_data, 500);
		unset($input_data);
		
		foreach($data_chunks as $data){
			
			
			$this->preMultipleUpdateEvent([$primary_key,$data]);
			
			
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
				
				$data[$k]	= $this->cleanData(Shortcuts::cleanControlFields($cell));
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
			$result	= $this->db()->update($sql,$params);
			$this->db()->closeCursor();
			$this->postMultipleUpdateEvent([$primary_key,$data]);
		}
		
		return $result;
	}
	
}//EOF CLASS


class MissingPrimaryKeyException extends \Exception{
	
}

class InconsistentDataException extends \Exception{
	
}

class NoDataException extends \Exception{
	
}

class FailedDeltaRecordCreation extends \Exception {}
