<?php namespace Talis\Services\Sql;

/**
 * DB class purpose is to wrap the actual DB extension we use and provide easy API and syntactic sugar
 * for the various operations we need.
 *
 * EACH INSTANCE IS ONE CONNECTION
 *
 * @author Itay Moav
 */
class MySqlClient {
	
	/**
	 * MySQL error codes
	 */
	const MYSQL_ERROR__LOCK_WAIT_TIMEOUT     = 1205, 
		  MYSQL_ERROR__SERIALIZATION_FAILURE = 1213, 
	      MYSQL_ERROR__MISSING_TABLE         = 1146, 
	      MYSQL_ERROR__DUPLICATE_ENTRY       = 1062
	;
	
	const LOG_VERBOSITY_ALL         = 4, 
	      LOG_VERBOSITY_BACKTRACE_4 = 3, 
	      LOG_VERBOSITY_SQL_ONLY    = 2, 
	      LOG_VERBOSITY_NONE        = 1
	;
	
	/**
	 * In transaction flag
	 * For nested flags this will increment 1,2,3...
	 * 0 means no transaction
	 *
	 * @var integer
	 */
	private $inTransaction = 0;
	
	/**
	 * How much to write to the current log
	 * (if there is a log)
	 * LOG_VERBOSITY_ALL = 4,
	 * LOG_VERBOSITY_BACKTRACE_4 = 3,
	 * LOG_VERBOSITY_SQL_ONLY = 2,
	 * LOG_VERBOSITY_NONE = 1
	 *
	 * @var integer const LOG_VERBOSITY_*
	 */
	private $logVerbosity = self::LOG_VERBOSITY_ALL;
	
	/**
	 * Native DB class.
	 * Most likely PDO
	 * 
	 * @var \PDO
	 */
	private $NativeDB = null;
	
	/**
	 * Last SQL which has been performed
	 *
	 * @var String
	 */
	private $lastSql = '';
	
	/**
	 * Holds the last PDO Statment object
	 *
	 * @var \PDOStatement
	 */
	private $lastStatement = null;
	
	/**
	 * Array of Parameters last used in the last SQL
	 *
	 * @var Array
	 */
	protected $lastBindParams = [ ];
	
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
	private $noExecute = false;
	
	/**
	 * Give a name to the connection so we can register/unregister in the factory
	 * Helpfull for debugging all active connections
	 */
	private $connection_name = '';
	
	/**
	 * last error code caught with no fail on error
	 * When false, no error was caught
	 *
	 * @var boolean|integer
	 */
	public $lastErrorCode = false;
	
	/**
	 * Creating an instance
	 * Although this is a type of sigleton, we are using a public modifier here, as we inherit the PDO class
	 * which have a public constructor.
	 */
	public function __construct(string $connection_name, array $conf_data) {
		$this->logVerbosity = $conf_data ['verbosity'];
		$this->connection_name = $connection_name;
		
		// CONNECT!
		$port = isset ( $conf_data ['port'] ) ? $conf_data ['port'] : null;
		$p = ($port != null) ? (";port={$port}") : '';
		$dns = 'mysql:dbname=' . $conf_data ['database'] . ";host=" . $conf_data ['host'] . $p;
		
		$this->NativeDB = new \PDO ( $dns, $conf_data ['username'], $conf_data ['password'], [ 
				\PDO::MYSQL_ATTR_INIT_COMMAND 		=> 'SET NAMES utf8',
				\PDO::ATTR_ERRMODE			  		=> \PDO::ERRMODE_EXCEPTION,
				\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
		] );
	}
	
	/**
	 *
	 * @return \Talis\Services\Sql\MySqlClient
	 */
	public function closeCursor() {
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
	 * @throws \PDOException
	 * @throws \Talis\Services\Sql\Exception\DuplicateEntry
	 * @return void
	 */
	private function execute(string $sql, array $params = []):void {
		$this->lastSql = $sql;
		$this->lastBindParams = $params;
		$this->log ();
		if ($this->noExecute)
			return;
		
		$DB = $this->NativeDB;
		
		// error handling
		try {
			
			if ($params) {
				$this->lastStatement = $DB->prepare ( $sql, [ 
						\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true 
				] );
				$this->lastStatement->execute ( $params );
			} else {
				$this->lastStatement = $DB->query ( $sql );
			}
			$this->numFields = $this->lastStatement->columnCount ();
			$this->numRows = $this->lastStatement->rowCount ();
		} catch ( \PDOException $e ) {
			// The transaction was rolled back anyway, we need to stop!
			if ($this->inTransaction) {
				throw $e;
			}
			
			// in some cases we automaticly try to re-submit the query, we give it just a few chance
			$code = $e->errorInfo [1];
			
			// handle each error specificaly
			switch ($code) {
				case (self::MYSQL_ERROR__LOCK_WAIT_TIMEOUT) : // SQLSTATE[HY000]: General error: 1205 Lock wait timeout exceeded; try restarting transaction
				case (self::MYSQL_ERROR__SERIALIZATION_FAILURE) : // SQLSTATE[40001]: Serialization failure: 1213 Deadlock found when trying to get lock; try restarting transaction
					sleep ( 10 );
					$this->execute ( $sql, $params ); // RECURSSION!
					break;
				
				case (self::MYSQL_ERROR__DUPLICATE_ENTRY) : // duplicate entries
					throw new \Talis\Services\Sql\Exception\DuplicateEntry ( print_r ( $this->lastBindParams, true ) );
				
				default :
					throw $e;
			}
		}
	}
	
	/**
	 * Entry point for select statments.
	 * We have this spread of authorities for future use (like different server verifications)
	 *
	 * @param String $sql        	
	 * @param Array $param        	
	 * @return MySqlClient
	 */
	public function select(string $sql, array $params = []):MySqlClient{
		$this->execute ( $sql, $params );
		return $this;
	}
	
	/**
	 * Insert a record
	 *
	 * @param String $sql        	
	 * @param Array $bindparam
	 *        	(fieldanme=>value, fieldanme=>value, ...)
	 * @return MySqlClient
	 */
	public function insert(string $sql, array $params = []):MySqlClient{
		$this->execute ( $sql, $params );
		$this->lastInsertID = $this->NativeDB->lastInsertId ();
		return $this;
	}
	
	/**
	 * Physically deletes a record or records from table
	 *
	 * @param String $sql        	
	 * @return MySqlClient
	 */
	public function delete(string $sql, array $params = []):MySqlClient{
		$this->execute ( $sql, $params );
		return $this;
	}
	
	/**
	 * Updates a record
	 *
	 * @param String $sql        	
	 * @param Array $bindparam        	
	 * @return MySqlClient
	 */
	public function update(string $sql, array $bindparam = []):MySqlClient{
		$this->execute ( $sql, $bindparam );
		return $this;
	}
	
	/**
	 * Returns the last statement Object
	 *
	 * @return \PDOStatement
	 */
	private function getLastStatement():\PDOStatement{
		return $this->lastStatement;
	}
	
	/**
	 * Returns the last SQL
	 *
	 * @return String
	 */
	public function getLastSql():string {
		return $this->lastSql;
	}
	
	/**
	 * Returns the last bind valye array
	 *
	 * @return array
	 */
	public function getLastbindParams():array {
		return $this->lastBindParams;
	}
	
	/**
	 * Fetch the rowset based on the PDO Type (FETCH_ASSOC,...)
	 *
	 * @param integer $fetch_type        	
	 * @return array
	 */
	public function fetchAll(int $fetch_type = \PDO::FETCH_ASSOC):array {
		$res = $this->lastStatement->fetchAll ( $fetch_type );
		return $res ?: [ ];
	}
	
	/**
	 * Fetch the rowset based on the PDO Type (FETCH_OBJ)
	 *
	 * @return array of stdClass
	 */
	public function fetchAllObj():array {
		return $this->lastStatement->fetchAll ( \PDO::FETCH_OBJ );
	}
	
	public function fetchAllUserObj(string $class_name, array $ctor_args = []):array {
		return $this->lastStatement->fetchAll ( \PDO::FETCH_CLASS, $class_name, $ctor_args );
	}
	
	public function fetchAllUserFunc($func):array {
		return $this->lastStatement->fetchAll ( \PDO::FETCH_FUNC, $func );
	}
	
	/**
	 * returns the result index by the first selected field and an array of the
	 * rest of the columns
	 * 
	 * @return array
	 */
	public function fetchAllIndexed(callable $func):array { // THIS IS STILL THOUGHT UPON!
		return $this->lastStatement->fetchAll ( \PDO::FETCH_UNIQUE | \PDO::FETCH_FUNC, $func );
	}
	
	/**
	 * Returns array structured [f1=>f2,f1=>f2,f1=>f2 ...
	 * f1=>f2]
	 * 
	 * @return array
	 */
	public function fetchAllPaired():array {
		return $this->lastStatement->fetchAll ( PDO::FETCH_KEY_PAIR );
	}
	
	/**
	 * Fetches one column as an array
	 *
	 * @param int $column
	 *        	index in select list
	 * @return array
	 */
	public function fetchAllColumn(int $column = 0):array {
		return $this->lastStatement->fetchAll ( \PDO::FETCH_COLUMN, $column );
	}
	private function fetchRow($result_type) {
		return $this->lastStatement->fetch ( $result_type );
	}
	public function fetchNumericArray():array {
		return $this->fetchRow ( \PDO::FETCH_NUM );
	}
	public function fetchArray():array {
		return $this->fetchRow ( \PDO::FETCH_ASSOC );
	}
	public function fetchObj(){
		return $this->fetchRow ( \PDO::FETCH_OBJ );
	}
	
	/**
	 * Calls a sp
	 * ATTENTION!!! I have no sanitation here!
	 *
	 * @param string $sp_name        	
	 *
	 * @return MySqlClient
	 */
	public function call(string $sp_name):MySqlClient{
		$params = func_get_args ();
		unset ( $params [0] ); // this is the function name
		                   
		// convert params array into string to call sp function
		$sql_p = Shortcuts::generateInData ( $params );
		if (! $sql_p ['params']) { // for the IN statement we always get a value to prevent syntax error
			$sql_p ['str'] = '()';
		}
		$sql = "CALL {$sp_name}{$sql_p['str']}";
		return $this->select ( $sql, $sql_p ['params'] );
	}
	
	/**
	 * If u use Omega, or wish to pass array of args instead of just args, choose this
	 *
	 * @param string $sp        	
	 * @param array $args        	
	 * @return MySqlClient
	 */
	public function callArr($sp, array $args):MySqlClient{
		// convert params array into string to call sp function
		$sql_p = Shortcuts::generateInData ( $args );
		if (! $sql_p ['params']) { // for the IN statement we always get a value to prevent syntax error
			$sql_p ['str'] = '()';
		}
		$sql = "CALL {$sp}{$sql_p['str']}";
		return $this->select ( $sql, $sql_p ['params'] );
	}
	
	/**
	 * Get the nested amount of transactions.
	 * Can also determine if transaction is being used
	 * 
	 * @return int
	 */
	public function getTransaction():int {
		return self::$inTransaction;
	}
	
	/**
	 * This function control the transaction flow & lock the auto commit.
	 *
	 * @throws \LogicException in case we are a read connection
	 * @return MySqlClient
	 */
	public function beginTransaction():MySqlClient{
		if ($this->connectionType == self::READ)
			throw new \LogicException ( 'Cant start transaction on a read connection' );
		
		$this->lastSql = 'BEGIN TRANSACTION';
		$this->lastBindParams = [ ];
		$this->log ();
		if (! self::$inTransaction) {
			$this->NativeDB->beginTransaction ();
		}
		self::$inTransaction ++;
		return $this;
	}
	
	/**
	 * This function commit the transactions, reset the flag and returns
	 * the true.
	 * In case of error it rollbacks and returns false flag
	 *
	 * @throws \LogicException in case there is no transaction to close.
	 * @return MySqlClient
	 */
	public function endTransaction():MySqlClient{
		$this->lastSql = 'END TRANSACTION';
		$this->lastBindParams = [ ];
		$this->log ();
		
		switch (self::$inTransaction) {
			case 1 :
				$this->NativeDB->commit ();
				self::$inTransaction = 0;
				break;
			
			case 0 :
				throw new \LogicException ( 'Trying to close a closed transaction' );
				break;
			
			default :
				self::$inTransaction --;
				break;
		}
		
		return $this;
	}
	
	/**
	 * This function rolls back the transactions, reset the flag and returns
	 * the true.
	 *
	 * @return MySqlClient
	 */
	public function rollbackTransaction():MySqlClient{
		$this->lastSql = 'ROLLBACK TRANSACTION';
		$this->lastBindParams = array ();
		$this->log ();
		if (self::$inTransaction) {
			$this->NativeDB->rollBack ();
			self::$inTransaction = 0;
		} else {
			throw new \LogicException ( 'Trying to roleback a closed transaction' );
		}
		return $this;
	}
	
	/**
	 * ADDED FUNCTION - HOLLY
	 * 
	 * @return boolean
	 */
	public function inTransaction():bool {
		return $this->NativeDB->inTransaction ();
	}
	public function close():void {
		$this->lastStatement = null;
		$this->NativeDB = null;
		Factory::unregister ( $this->connection_name );
	}
	
	/**
	 * Attempts to get Caller function.
	 */
	private function getCaller():string {
		$bt = debug_backtrace ( BACKTRACE_MASK );
		$stack = [ ];
		$i = 0;
		foreach ( $bt as $trace_line ) {
			if (! isset ( $trace_line ['file'] )) {
				$trace_line ['file'] = 'unknown, probably due to unittest reflection way';
			}
			if (! isset ( $trace_line ['line'] )) {
				$trace_line ['line'] = 'unknown, probably due to unittest reflection way';
			}
			
			if ($i > 4 && $this->logVerbosity < self::LOG_VERBOSITY_ALL) {
				break;
			}
			$function = isset ( $trace_line ['function'] ) ? $trace_line ['function'] : '';
			// exclude some functions from debug trace
			if (in_array ( $function, array (
					'getCaller',
					'slog',
					'execute',
					'select',
					'update',
					'delete',
					'insert' 
			) )) {
				continue;
			}
			
			// unfold args
			$args = (isset ( $trace_line ['args'] ) && ! empty ( $trace_line ['args'] )) ? ' args: ' . print_r ( $trace_line ['args'], true ) : '';
			$stack [] = "{$trace_line['file']} ({$trace_line['line']}) function:{$function}{$args}";
			$i ++;
		}
		
		return implode ( PHP_EOL, $stack );
	}
	
	/**
	 * For debug purposes only.
	 * should not work when debug flag is off
	 */
	private function log():void {
		if ($this->logVerbosity < self::LOG_VERBOSITY_SQL_ONLY) {
			return;
		}
		$msg = "\n\n";
		
		if ($this->logVerbosity > self::LOG_VERBOSITY_SQL_ONLY) {
			$msg .= $this->getCaller ();
		}
		$msg .= "\n{$this->lastSql}\n";
		
		if ($this->lastBindParams) {
			$params = print_r ( $this->lastBindParams, true );
			$msg .= "PARAMS: {$params}";
		}
		
		$msg .= "\n\n";
		\dbg ( $msg );
	}

	/**
	 * Debug info for who ever wants it
	 * 
	 * @return string
	 */
	public function getDebugInfo():string{
		return $this->lastSql . ' ' . print_r ( $this->lastBindParams, true );
	}
}
