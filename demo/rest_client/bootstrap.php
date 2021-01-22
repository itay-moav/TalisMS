<?php
ini_set('error_reporting', E_ALL|E_STRICT);
ini_set('log_errors', true);
ini_set('include_path', PATH_SEPARATOR . '/usr/share/php/ZendFW2411');
function autoload($class) {
	$file_path = str_replace(['_','\\'],'/',$class) . '.php';
	if(!include_once $file_path){
		throw new Exception("{$file_path} {$class}");
	}
}
spl_autoload_register('autoload');

/**
 * @param string $uri
 * @param stdClass $obj
 * @param string $https
 * @return mixed
 */
function get_client($uri,stdClass $obj,$https=false){
	$s = $https ? 's' : '';
	$urls = [ 
			'itay_development' => "http{$s}://192.168.12.148/talis",
			'newstaging' => "http{$s}://api.newstaging.sitelms.org/scheduler",
			'prod_web' => "http{$s}://api.web01.sitelms.org/scheduler" 
	];
	
	$url = $urls [lifeCycle ()];
	echo "hitting [{$url}{$uri}]\n";
	echo "SENDING THE FOLLOWING:\n";
	var_dump ( json_encode ( $obj ) );
	
	/**
	 * INIT A NEW INSTALLATION ACTIVATION
	 */
	// initiate POST
	$Client = ZendIHttpClient::factory($url . $uri);
	
	// not relevant
	$Client->setRawBody(json_encode($obj));
	
	try {
		$response = $Client->send();
	} catch ( Exception $e ) {
		echo $e;
	}
	echo "\n\nRESPONSE IS:\n";
	printf ( "Return code is [%d %s]\n", $response->getStatusCode(),$response->getReasonPhrase());
	var_dump ( $response->getBody() );
	$Json = json_decode ( $response->getBody());
	return $Json;
}



/**
 * Wrapper for the Zend 1.x http class to add the getRequestHeaders()
 * This is for debug purposes
 *
 * @author Itay Moav
 */
class ZendIHttpClient extends \Zend\Http\Client{
	static public function factory(string $url):ZendIHttpClient{
		$client = new ZendIHttpClient ( $url, array (
				'adapter' => 'Zend\Http\Client\Adapter\Curl',
				'sslverifypeer' => false,
				'maxredirects' => 1,
				'timeout' => 5,
				'useragent' => 'LMS_LiveAccess' 
		));
		$client->setMethod(\Zend\HTTP\Request::METHOD_POST);
		$client->setHeaders (['Content-type'=>'application/json'] );
		return $client;
	}
	
	private $my_headers = [ ];
	
	/**
	 * Capture the headers for debug purposes later
	 *
	 * @see \Zend\Http\Client::prepareHeaders()
	 */
	protected function prepareHeaders($body, $uri){
		$this->my_headers = parent::prepareHeaders($body, $uri);
		return $this->my_headers;
	}
	
	public function getRequestHeaders(){
		return $this->my_headers;
	}
	
	/**
	 * @return string GET|POST|PUT|DELETE
	 */
	public function getCurrentMethod(){
		return $this->getRequest()->getMethod();
	}
	
	/**
	 * @return string getter for the POST/PUT raw data
	 */
	public function getRawRequestData(){
		return $this->getRequest()->getContent();
	}
}

/**
 * @param array $array
 * @return stdClass
 */
function array_to_object(array $array) {
	$object = new stdClass();
	foreach ($array as $key => $value) {
		if (is_array($value)) {
			$value = array_to_object($value);
		}
		$object->$key = $value;
	}
	return $object;
}
