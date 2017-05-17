<?php
/**
 * Defines basic actions against the SOLR server
 * Specific cores and more complex stuff will be handled in the relevant DL class
 * old class = SiTEL_DataStorage_Dbutils__SOLR
 */
class Data_SOLR_DB{

	/**
	 * Creats an instance of the object
	 *
	 * @return Data_SOLR_DB
	 */
	public static function getInstance($core='content'){
		return new self($core);
	}
	
	public static $last_status = 0;
	
	const DATE_PATTERN = '/^\d{4}-\d\d-\d\dT\d\d:\d\d:\d\dZ$/';

	/**
	 * URL to SOLR instance, no core here
	 *
	 * @var string url
	 */
	private $solrUrl='';
	/**
	 * Core this object is pointed thwords 
	 *
	 * @var string core name
	 */
	private $core='';
	
	public $response='',
           $commitResponse = ''
    ;           
	
	public function __construct($core='content'){
		$config = app_env();
		$this->solrUrl = $config['database']['SOLR'];
		$this->core = $core . '/';
	}
	
	/**
	 * posts data to master
	 *
	 * @param string $data JSON formatted
	 * @return Data_SOLR_DB
	 */
	public function insert($data){
		$client = new \Zend\Http\Client(
                                       $this->solrUrl['master'] . $this->core . 'update/json',
                                       [
                                           'adapter' => 'Zend\Http\Client\Adapter\Curl',
                                           'timeout'      => 20
                                       ]
                );
		//$client->setUri($this->solrUrl['master'] . $this->core . 'update/json');
		$client->setMethod(\Zend\Http\Request::METHOD_POST);
		$client->setHeaders(['Content-type'=>'application/json']);
		$client->setRawBody($data);
		//dbgr('DATA',$this->raw_post_data);
		$this->response = $client->send();
		self::$last_status = $this->response->getStatusCode();
		dbgn(self::$last_status);
		if(self::$last_status != 200){
		    dbgn(self::$last_status);die;
		}
		return $this;
	}
	
	/**
	 * @return Data_SOLR_DB
	 */
	public function delete($id){
		$client = new \Zend\Http\Client(
                                       $this->solrUrl['master'] . $this->core . 'update',
                                       [
                                           'adapter' => 'Zend\Http\Client\Adapter\Curl',
                                           'timeout' => 20
                                       ]
                );
		//$client->setUri($this->solrUrl['master'] . $this->core . 'update');
		$client->setMethod(\Zend\Http\Request::METHOD_POST);
		$client->setHeaders(['Content-type'=>'application/xml']);
		
		$query = 'id:' . $id;
		if(is_array($id)){
			$query = 'id:' . join(' OR id:',$id);
		}
		
		$data = "<delete><query>{$query}</query></delete>";
		$client->setRawBody($data);
		$this->response = $client->send();
		self::$last_status = $this->response->getStatusCode();
		dbgn(self::$last_status);
		if(self::$last_status != 200){
		    dbgn(self::$last_status);die;
		}
		return $this;
	}
	
	/**
	 * @return Data_SOLR_DB
	 */
	public function commit(){
		$uri = $this->solrUrl['master'] . $this->core . 'update/json?commit=true';
		$this->commitResponse = file_get_contents($uri);
		dbgr('----------------------------COMMIT-----------------------------',$this->commitResponse);
		return $this;
	}

	/**
	 * Sends a query to SOLR and expects a JSON object in response
	 *
	 * @param unknown_type $query
	 * @return stdClass
	 */
	public function query($query,array $params,$order_by,$fields,$concat){
		$uri = $this->solrUrl['slave'];
		$prepared_order_by='';
		if($order_by){
			$prepared_order_by = '&sort=s' . urlencode($order_by);
		}
		$q_params='';
		if($params){
			$q_params = "&fq=" . urlencode(join(' AND ',$params));
		}
		if($fields != ''){
			$fields = '&fl=' . $fields;
		}
		dbgr('SOLR RAW QUERY',$query);
		$actual_query = $uri . $this->core . 'select/?q=' . urlencode($query) . $q_params . $prepared_order_by . $fields . $concat."&wt=json";
		dbgr('FLARE SOLR',$actual_query);
		$res = json_decode(file_get_contents($actual_query));
		return $this->decodeResult($res);
	}
	
	/**
	 * Recursive function to decode all of JSONs
	 * fields from a URL encoded
	 *
	 * @param unknown_type $obj
	 * @return stdClass
	 */
	private function decodeResult(&$obj){
		if(is_array($obj) || $obj instanceOf stdClass) {
			foreach($obj as &$member){
				$this->decodeResult($member);		
			}
		}
		else{
			$obj = urldecode($obj);
			if(preg_match(self::DATE_PATTERN,$obj)){//decodinge date fields
				$obj = str_replace(array('T','Z'),'',$obj);
			}
		}
		return $obj;
	}
}
