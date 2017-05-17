<?php
/**
 * DB class purpose is to wrap the actual DB extension we use 
 * So if we change extension, no changes in code will occur beside in this class. 
 * old class = SiTEL_DataStorage_Dbutils_DB
 * @author Samia Kourosh, Itay Moav
 * @Reviewer Itay Moav
 */
class Data_MySQL_DB{

	/**
	 * Db connection servers (write(master)|read(slave)|reports etc
	 */
	const	READ	= 0,
			WRITE	= 1,
			REPORTS	= 2
	;
	
	/**
	 * MySQL error codes
	 */
	const	MYSQL_ERROR__LOCK_WAIT_TIMEOUT		= 1205,
			MYSQL_ERROR__SERIALIZATION_FAILURE	= 1213,
			MYSQL_ERROR__MISSING_TABLE			= 1146,
			MYSQL_ERROR__DUPLICATE_ENTRY		= 1062
	;
	
	const	LOG_VERBOSITY_ALL			= 4,
		 	LOG_VERBOSITY_BACKTRACE_4	= 3,
		 	LOG_VERBOSITY_SQL_ONLY		= 2,
		 	LOG_VERBOSITY_NONE			= 1
	;
	
	const  PROCESSLIST_DELIMITER       = '!PROCESSLIST!';
	
	/**
	 * This list defines the number of instances this Class can hold at a single time.
	 * If we need to add another connection (say two read connections) then we need to add here 
	 * another entry and fix the switch statment in the getInstance block
	 */
	static private $connections = 	[self::READ		=> false,
									 self::WRITE	=> false,
									 self::REPORTS	=> false
	];
	
	/**
	  * In transaction flag
	  * For nested flags this will increment 1,2,3... 0 means no transaction
	  *
	  * @var integer
	  */
    static private $inTransaction = 0;
	/**
	 * How much to write to the current log
	 * (if there is a log)
	 * LOG_VERBOSITY_ALL			= 4,
	 * LOG_VERBOSITY_BACKTRACE_4	= 3,
	 * LOG_VERBOSITY_SQL_ONLY		= 2,
	 * LOG_VERBOSITY_NONE			= 1
	 * 
	 * @var integer const LOG_VERBOSITY_*
	 */
	private $logVerbosity = 1;
    /**
     * Native DB class. Most likely PDO
     * @var PDO
     */
	private $NativeDB=null;
	 /**
	  * Last SQL which has been performed
	  *
	  * @var String
	  */
	private $lastSql = '';
	 
	 /**
	  * Holds the last PDO Statment object
	  *
	  * @var  PDOStatement
	  */
	private $lastStatement = null;
	 
	 /**
	  * Array of Parameters last used in the last SQL
	  *
	  * @var Array
	  */
	protected $lastBindParams = [];
	 /**
	  * Number of the rows returned or affected
	  *
	  * @var Int
	  */
	public $numRows = 0;
	 /**
	  * Number of fields in returned rowset
	  *
	  * @var Int
	  */
	public $numFields = 0;
	 /**
	  * Holds the last inserted ID
	  *
	  * @var integer
	  */
	public $lastInsertID = false;
	 /**
	  * Wether to execute the query or not.
	  * Good to get back the SQL only, for Pagers, for example.
	  */
	private $noExecute=false;
	 /**
	  * last error code caught with no fail on error
	  * When false, no error was caught
	  * 
	  * @var boolean|integer
	  */
	public $lastErrorCode = false;
	private $connectionType = '';
	private $errorRecoveryCycles = 0;

	/**
	 * Creats an instance of the object
	 *
	 * @param String $connection_type
	 * @return Data_MySQL_DB
	 */
	public static function getInstance($connection_type=self::READ){
		$config = app_env();
		if($config['database']['unify_read_write'] && $connection_type != self::REPORTS){
			$connection_type = self::WRITE;
		}
		
		if (isset(self::$connections[$connection_type]) && self::$connections[$connection_type] === false){
				switch($connection_type) {
					case(self::WRITE):
						$Conf=$config['database']['master'];
						break;
					case(self::READ):
						$Conf=$config['database']['slave'];
						break;
					case(self::REPORTS):
						$Conf=$config['database']['reports'];
						break;
						
					default:
						throw new Exception("You have asked for unexisting DB instance");
					}//EOF switch
					
					self::$connections[$connection_type] = new self($Conf);
					self::$connections[$connection_type]->setConnectionType($connection_type);
		}//EOF IF
		return self::$connections[$connection_type];
	}
	
	/**
	 * Get's the last statments all dbs did
	 * @return array for each conn with [last_sql],[last_params]
	 */
	static public function getDebugData(){
		$db_array=array();
		foreach(self::$connections as $db_type=>$db){
			if($db){
				$db_array[$db_type]=array(
					'last_sql'				=> $db->getLastSql(),
					'last_binding_params'	=> print_r($db->getLastbindParams(),true)
				);
			}
		}
		return $db_array;
	}

	/**
	 * Creating an instance
	 * Although this is a type of sigleton, we are using a public modifier here, as we inherit the PDO class
	 * which have a public constructor.
	 */
	private function __construct(array $conf_data) {
		$this->logVerbosity = $conf_data['verbosity'];
		
		//CONNECT!
		$port   = isset($conf_data['port']) ? $conf_data['port'] : null;
		$p = ($port != null) ? (";port={$port}") : '';
		$dns = 'mysql:dbname='.$conf_data['database'].";host=".$conf_data['host'].$p;

		$this->NativeDB = new PDO($dns,$conf_data['username'],$conf_data['password'],[PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']);
		$this->NativeDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->NativeDB->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);
	}
	
	/**
	 * @param string $connection_type
	 */
	private function setConnectionType($connection_type){
		$this->connectionType = $connection_type;
	}

	/**
	 * added by holly for testing
	 * @return connectionType
	 */
	public function getConnectionType(){
	    return $this->connectionType;
	}
	
	/**
	 * @return Data_MySQL_DB
	 */
	public function closeCursor(){
		$this->lastStatement->closeCursor();
		return $this;
	}

	/**
	 * Executing the query.
	 * 1. if fails in transaction - fails the entire thing and throws an exception
	 * 2. If deadlocl/serialization - tries 10 times and then failes
	 * 3. If duplicate entry - throw duplicate exception
	 * 
	 * @param string $sql
	 * @param array $params
	 * @throws PDOException
	 * @throws Data_MySQL_Exceptions_DuplicateEntry
	 * @return void
	 */
	private function execute($sql, array $params=[]){
		$this->lastSql = $sql;
		$this->lastBindParams=$params;
		$this->slog();
		if($this->noExecute) return;
		
		//in transaction, direct all queries to the connection in transaction!
		$DB = $this->NativeDB;
		if(self::$inTransaction && $this->connectionType == self::READ){
			$DB = self::getInstance(self::WRITE);
		}
		
		//error handling
		try{

		    if($params){
				$this->lastStatement = $DB->prepare($sql,[PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true]);
				$this->lastStatement->execute($params);
			}else{
				$this->lastStatement = $DB->query($sql);
			}
			$this->numFields = $this->lastStatement->columnCount();
			$this->numRows = $this->lastStatement->rowCount();
			//TODO SiTEL_Model_Where::resetParamCount();//to stop counting if we use the same queries in a pager inside the same request.ITAY/PRESTON/MATT
			
		}catch (PDOException $e){
			//The transaction was rolled back anyway, we need to stop!
			if(self::$inTransaction){
				throw $e;
			}
				
			//in some cases we automaticly try to re-submit the query, we give it just a few chance
			//$code = $this->extractMysqlErrorCode($e->getMessage());
			$code = $e->errorInfo[1];
			if($this->errorRecoveryCycles>10){
				$error = 'Can not recover from error by sleeping, error is ' . $e->getMessage() . ' [' . $code . '] I die!';
				$result = $DB->query("SHOW PROCESSLIST")->fetchAll();
				$error .= self::PROCESSLIST_DELIMITER . print_r($result, true);
				throw $e;
			}
			
			$this->errorRecoveryCycles++;
			
			//handle each error specificaly			
			switch($code){
				case (self::MYSQL_ERROR__LOCK_WAIT_TIMEOUT)://SQLSTATE[HY000]: General error: 1205 Lock wait timeout exceeded; try restarting transaction
				case (self::MYSQL_ERROR__SERIALIZATION_FAILURE)://SQLSTATE[40001]: Serialization failure: 1213 Deadlock found when trying to get lock; try restarting transaction
					sleep(3);
					$this->execute($sql,$params);//RECURSSION!
					break;

				case (self::MYSQL_ERROR__MISSING_TABLE)://missing table error, probably one of the mv's, we sleep for 3 seconds and try again
					if(strpos($e->getMessage(),'_mv')){
						sleep(3);
						$this->execute($sql,$params);
					}else{
						throw $e;
					}
					break;
					
				case (self::MYSQL_ERROR__DUPLICATE_ENTRY)://duplicate entries, mostl;y happens in the archive/history when we insert two records without waiting (key consists of NOW()
					throw new Data_MySQL_Exception_DuplicateEntry(print_r($this->lastBindParams,true));
					
				default:
					throw $e;		
			}
		}
		$this->errorRecoveryCycles = 0;
		Data_MySQL_Shortcuts::resetParamCount();
		return;
	}
	
	
	
	/**
	 * extract error code from exception message
	 * 'SQLSTATE[HY000]: General error: 1205 Lock wait timeout exceeded; try restarting transaction'
	 * 
	 * will return 1205
	 * @param string $msg
	 */
	private function extractMysqlErrorCode($msg){
		$p='/\b[0-9][0-9][0-9][0-9]\b/';
		preg_match($p,$msg,$m);
		
		//  PRESTON Display mysql errors during phpunit tests on command line
		if( defined('__TEST_RUN__') && !isset($m[0])){
			echo $m;
			echo $msg;
			echo $p;
		}
		return $m[0];		
	}
	
	/**
	 * Entry point for select statments.
	 * We have this spread of authorities for future use (like different server verifications)
	 *
	 * @param String $sql
	 * @param Array $param
	 * @return Data_MySQL_DB
	 */
	public function select($sql, array $params=[]){
		$this->execute($sql,$params);
		return $this;
	}

	/**
	 * Insert a record
	 *
	 * @param String $sql
	 * @param Array $bindparam (fieldanme=>value, fieldanme=>value, ...)
	 * @return Data_MySQL_DB
	 */
	public function insert($sql,array $params = []){
		$this->execute($sql,$params);
		$this->lastInsertID=$this->NativeDB->lastInsertId();
		return $this ;
	}

	/**
	 * Physically deletes a record or records from table
	 *
	 * @param String $sql
	 * @return Data_MySQL_DB
	 */
	public function delete($sql,array $params = []){
		$this->execute($sql,$params);
		return $this;
	}

	/**
	 * Updates a record
	 *
	 * @param String $sql
	 * @param Array $bindparam
	 * @return Data_MySQL_DB
	 */
	public function update($sql,array $bindparam = []){
		$this->execute($sql,$bindparam);
		return $this;
	}

    /**
     * Returns the last statement Object
     *
     * @return PDOStatement
     */
    private function getLastStatement(){
        return $this->lastStatement;
    }

    /**
     * Returns the last SQL
     *
     * @return String
     */
    public function getLastSql(){
        return $this->lastSql;
    }

    /**
     * Returns the last bind valye array
     *
     * @return array
     */
    public function getLastbindParams(){
        return $this->lastBindParams;
    }

	/**
	 * Fetch the rowset based on the PDO Type (FETCH_ASSOC,...)
	 *
	 * @param integer $fetch_type
	 * @return array
	 */
	public function fetchAll($fetch_type = PDO::FETCH_ASSOC){
		$res=$this->lastStatement->fetchAll($fetch_type);
		return $res?:[];
	}
	
	public function fetchAllObj(){
		return $this->lastStatement->fetchAll(PDO::FETCH_OBJ);
	}
	
	public function fetchAllUserObj($class_name,array $ctor_args=array()){
		return $this->lastStatement->fetchAll(PDO::FETCH_CLASS,$class_name,$ctor_args);
	}
	
	public function fetchAllUserFunc($func){
		return $this->lastStatement->fetchAll(PDO::FETCH_FUNC,$func);
	}
	
	/**
	 * returns the result index by the first selected field and an array of the 
	 * rest of the columns
	 * @return array
	 */
	public function fetchAllIndexed($func){//THIS IS STILL THOUGHT UPON!
	    return $this->lastStatement->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_FUNC,$func);
	}
	
	/**
	 * Returns array structured  [f1=>f2,f1=>f2,f1=>f2 ... f1=>f2]
	 * @return array
	 */
	public function fetchAllPaired(){
	    return $this->lastStatement->fetchAll(PDO::FETCH_KEY_PAIR);
	}
	
	/**
	 * Fetches one column as an array
	 *
	 * @param int $column index in select list
	 * @return array
	 */
	public function fetchAllColumn($column=0){
		return $this->lastStatement->fetchAll(PDO::FETCH_COLUMN, $column);
	}
	
	private function fetchRow($result_type){
		return $this->lastStatement->fetch($result_type);
	}
	
	public function fetchNumericArray(){
		return $this->fetchRow(PDO::FETCH_NUM);
	}

	public function fetchArray(){
		return $this->fetchRow(PDO::FETCH_ASSOC);
	}
	
	public function fetchObj(){
		return $this->fetchRow(PDO::FETCH_OBJ);
	}
	
	/**
	 * Calls a sp
	 * ATTENTION!!! I have no sanitation here!
	 *
	 * @param string $sp_name
	 *
	 * @return Data_MySQL_DB
	 */
	public function call($sp_name){
	    $params = func_get_args();
	    unset($params[0]); //this is the function name

		//convert params array into string to call sp function
		$sql_p = Data_MySQL_Shortcuts::generateInData($params);
		if(!$sql_p['params']){//for the IN statement we always get a value to prevent syntax error
			$sql_p['str'] = '()';
		}			
		$sql = "CALL {$sp_name}{$sql_p['str']}";
		return $this->select($sql,$sql_p['params']);
	}
	
	/**
	 * If u use Omega, or wish to pass array of args instead of just args, choose this
	 * 
	 * @param string $sp
	 * @param array $args
	 * @return Data_MySQL_DB
	 */
	public function callArr($sp,array $args){
	    //convert params array into string to call sp function
	    $sql_p = Data_MySQL_Shortcuts::generateInData($args);
	    if(!$sql_p['params']){//for the IN statement we always get a value to prevent syntax error
	        $sql_p['str'] = '()';
	    }
	    $sql = "CALL {$sp}{$sql_p['str']}";
	    return $this->select($sql,$sql_p['params']);
	}
		
	/**
	 * Get the nested amount of transactions.  Can also determine if transaction is being used
	 * @return number
	 */
	public function getTransaction(){
		return self::$inTransaction;
	}
	
	/**
	 * This function control the transaction flow & lock the auto commit.
	 * 
	 * @throws LogicException in case we are a read connection
	 * @return Data_MySQL_DB
	 */
	public function beginTransaction(){
		if($this->connectionType == self::READ) throw new LogicException('Cant start transaction on a read connection');
		
		$this->lastSql = 'BEGIN TRANSACTION';
		$this->lastBindParams = [];
		$this->slog();
		if (!self::$inTransaction ) {
			$this->NativeDB->beginTransaction();
		}
		self::$inTransaction++;
		return $this;
	}

	/**
	 * This function commit the transactions, reset the flag and returns
	 * the true. In case of error it rollbacks and returns false flag
	 * 
	 * @throws LogicException in case there is no transaction to close.
	 * @return Data_MySQL_DB
	 */
	public function endTransaction(){
		$this->lastSql='END TRANSACTION';
		$this->lastBindParams=[];
		$this->slog();
		
		switch(self::$inTransaction){
			case 1:
				$this->NativeDB->commit();
				self::$inTransaction = 0;
				break;
				
			case 0:
				throw new LogicException('Trying to close a closed transaction');
				break;
					
			default:
				self::$inTransaction--;
				break;
		}
		
		return $this;
	}
	
	/**
	 * This function rolls back the transactions, reset the flag and returns
	 * the true.
	 *
	 * @return DataStorage_MySQL_DB
	 */
	public function rollbackTransaction(){
		$this->lastSql='ROLLBACK TRANSACTION';
		$this->lastBindParams=array();
		$this->slog();
		if (self::$inTransaction ) {
			$this->NativeDB->rollBack();
			self::$inTransaction = 0;
		}else{
			throw new LogicException('Trying to roleback a closed transaction');
		}
		return $this;
	}

	/**
	 * ADDED FUNCTION - HOLLY
	 * @return boolean
	 */
	public function inTransaction(){
	    return $this->NativeDB->inTransaction();
	}
	
	
    protected function close(){
		$this->NativeDB = null;
    }

	/**
	 * Attempts to get Caller function.
	 */
	private function getCaller()
	{
		$bt = debug_backtrace(BACKTRACE_MASK);
		$stack = [];
		$i=0;
		foreach ($bt as $trace_line)
		{
			if(!isset($trace_line['file'])){
				$trace_line['file'] = 'unknown, probably due to unittest reflection way';
			}
			if(!isset($trace_line['line'])){
				$trace_line['line'] = 'unknown, probably due to unittest reflection way';
			}
				
			if($i>4 && $this->logVerbosity < self::LOG_VERBOSITY_ALL){
				break;
			}
			$function = isset($trace_line['function'])?$trace_line['function']:'';
			//exclude some functions from debug trace
			if(in_array($function,array('getCaller','slog','execute','select','update','delete','insert'))){
				continue;
			}
			
			//unfold args
			$args	 = (isset($trace_line['args']) && !empty($trace_line['args']))?' args: ' . print_r($trace_line['args'],true):'';
			$stack[] = "{$trace_line['file']} ({$trace_line['line']}) function:{$function}{$args}";
			$i++;
		}

		return implode(PHP_EOL,$stack);
	}
	
	/**
	 * For debug purposes only.
	 * should not work when debug flag is off
	 */
	private function slog(){
		if(app_env()['log']['verbosity']<4 || $this->logVerbosity < self::LOG_VERBOSITY_SQL_ONLY){
			return;
		}
		$msg="\n\n";
		
		if($this->logVerbosity > self::LOG_VERBOSITY_SQL_ONLY){
			$msg .= $this->getCaller();	
		} 
		$msg .= "\n{$this->lastSql}\n";

		if($this->lastBindParams){
			$params=print_r($this->lastBindParams,true);
			$msg.="PARAMS: {$params}";
		}

		$msg.="\n\n";
		dbg($msg);
	}
}
