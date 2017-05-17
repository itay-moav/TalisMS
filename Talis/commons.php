<?php
/**
 * functions.php
 * Contains certain low-level functions used throughout the LMS.
 *
 * @package SiTEL
 */

/**
 * Collection of functions that are used in the INIT process only
 */

function set_logger($log_name){
	$c = app_env();

	//Logger
	Logger_MainZim::factory(
		$c['log']['prefix'] . $log_name,
		$c['log']['handler'],
		$c['log']['verbosity'],
		$c['log']['uri']
	);
}

/**
 * In order to support lazy loading of classes within PHP, an autoload �magic� function is defined and installed in
 * PHP's execution stack.
 *
 * @param String $class
 * @throws Exception_ClassNotFound If the file does not exist or the class was not found in the file.
 * @return void
 */
function autoload($class) {
//Comment out untill further notice, do not remove	  if(!@include_once getClassAutoloadPath($class)){
    $file_path = str_replace(['_','\\'],'/',$class) . '.php';
    if(!@include_once $file_path){
        throw new Exception_ClassNotFound("{$file_path} {$class}");
    }
}

/** * This function will make sure warnings and notes are thrown as exceptions */
function error_handler($errno, $errstr, $errfile, $errline){
    error("ERROR HANDLER: File [{$errfile}]. Line: [{$errline}].\nMessage: [{$errstr}]\n");
}

//DB shortcuts - since we used it the same all over.

/**
 * READ
 * @return Data_MySQL_DB - READ
 */
function rddb(){
	return Data_MySQL_DB::getInstance(Data_MySQL_DB::READ);
}

/**
 * WRITE
 * @return Data_MySQL_DB - WRITE
 */
function rwdb(){
	return Data_MySQL_DB::getInstance(Data_MySQL_DB::WRITE);
}

/**
 * For SELECT2 smart search expected results
 * 
 * @param string $id
 * @param string $text
 */
function values_to_stdclass($id,$text){
	$res= new stdClass;
	$res->id = $id;
	$res->text = $text;
	return $res;
}

/**
 * Check for production environment. Used in the initialize_tests
 */
function isProduction() {
	return strpos(lifecycle(), 'prod') !== false;
}

/**
 * A key for full page cache
 */
function get_global_cache_key(){
	return 'PAGE-CACHE-' . md5($_SERVER['REQUEST_URI'] . $_SERVER['QUERY_STRING']);
}


/**
 * This function allows the LMS to load configuration directives from XML files. The function automatically caches the
 * parsed version of these XML files into memory (using APC). The function also checks the modification time of the
 * configuration file in order to determine if it needs to be reloaded and its cached version purged from memory.
 *
 * @param String $filename
 * @param Boolean $useCache
 * @return Array
 */
function getXMLFromFile($filename, $useCache = true) {
	
	$file = array();
	//use cache
	if ($useCache)
	{
		$key = $filename.filemtime($filename);
		//$file = apc_fetch($key);
		$RedisXML	= new Redis_XML($key);
		$file		= $RedisXML->file()->get();
		
		//return file is in cache
		if ($file){
			return $file;
		}

	} 
	$simpleXml = simplexml_load_file($filename, 'SimpleXMLElement', LIBXML_NOCDATA);
	
	if(!is_object($simpleXml)){
		fatal("The content specified does not exist. ({$filename})");
		throw new Exception_FileNotFound($filename);
	}
	
	$file = simpleXmlToArray($simpleXml);
	if ($useCache){
		//apc_store($key, $file, 86400);
		//(new Redis_XML($key))->file()->setex(86400,$file);
		$RedisXML->file()->setex(86400,$file);
	}
	return $file;
}

/**
 * Adapted from Zend Framework. Returns a XML document in an associative array.
 *
 * @param SimpleXMLElement $xmlObject
 * @throws Exception If problems are encountered with reading or parsing the XML document.
 * @return Array
 */
function simpleXmlToArray(SimpleXMLElement $xmlObject) {
	$config = array();
	$count = 0;
	
	if(is_object($xmlObject)){
		$count = count($xmlObject->attributes());
	}else{
		echo 'There has been an error in SiTELMS.  Developers have been notified of the issue.';
		error_monitor('The content specified does not exist.', 2);
	}
	
	// Search for parent node values
	if ($count > 0) {
		foreach ($xmlObject->attributes() as $key => $value) {
			if ($key === 'extends') {
				continue;
			}

			$value = (string) $value;

			if (array_key_exists($key, $config)) {
				if (!is_array($config[$key])) {
					$config[$key] = array($config[$key]);
				}

				$config[$key][] = $value;
			} else {
				$config[$key] = $value;
			}
		}
	}
	
	// Search for children
	if (count($xmlObject->children()) > 0) {
		foreach ($xmlObject->children() as $key => $value) {
			if (count($value->children()) > 0) {
				$value = simpleXmlToArray($value);
			} else if (count($value->attributes()) > 0) {
				$attributes = $value->attributes();
				if (isset($attributes['value'])) {
					$value = (string) $attributes['value'];
				} else {
					$value = simpleXmlToArray($value);
				}
			} else {
				$value = (string) $value;
			}

			if (array_key_exists($key, $config)) {
				if (!is_array($config[$key]) || !array_key_exists(0, $config[$key])) {
					$config[$key] = array($config[$key]);
				}

				$config[$key][] = $value;
			} else {
				$config[$key] = $value;
			}
		}
	} else if (count($config) === 0) {
		// Object has no children nor attributes
		// attribute: it's a string
		$config = (string) $xmlObject;
	}
	$text = (string) $xmlObject;
	$text = trim($text);
	if ($text != '') {
		$config['value'] = $text;
	}

	return $config;
}

function is_https(){
	return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on');
}

/**
 * This function creates relative and absolute URLs based on a supplied relative path fragment.
 *
 * @param String $uri
 * @param Boolean $absolute
 * @param Boolean $includeTrailingSlash
 * @param String $queryString
 * @return String
 */
function url($useOrg = false, $force_https=DONT_FORCE_SCHEMA,$force_subdomain = '') {

    $http_schema = ((is_https() || $force_https == FORCE_HTTPS) && ($force_https != PREVENT_FORCE_HTTPS))?'https://':'http://';
    $subdomain = $force_subdomain?:SUBDOMAIN;
    
    $config = app_env();
    $relativePath = $config['paths']['sub'][$subdomain]['relativePath'];
    
    $baseUrl = $config['paths']['baseUrl'];
    if($relativePath){
        $url = "{$http_schema}{$baseUrl}{$relativePath}";
    }else{
        $url = "{$http_schema}{$subdomain}.{$baseUrl}";
    }
    
    if ($useOrg && Organization_Current::isOrg()){
        $url .= '/' . Organization_Current::path();
    }
    return $url;
}

/**
 * Going from beta/next -> www
 * @return String
 */
function url_www($force_https=DONT_FORCE_SCHEMA){
    return url(true, $force_https, 'www');
}

/**
 * Returns URL With organization
 */
function org_url($force_https=DONT_FORCE_SCHEMA,$force_subdomain = ''){
	return url(true,$force_https,$force_subdomain);
}

function static_url($force_https=DONT_FORCE_SCHEMA){
	$http_schema = ((is_https() || $force_https == FORCE_HTTPS) && ($force_https != PREVENT_FORCE_HTTPS))?'https://':'http://';
	return $http_schema . app_env()['paths']['baseUrl'] .'/files';
}

function content_url(){
	return static_url() . '/contentassets/';
}

function course_url(){
	return static_url() . '/courseassets/';
}

function is_ie() {
	return strpos($_SERVER['HTTP_USER_AGENT'],'MSIE') !== false;
}

/**
 *
 * This function wraps the basic gettext functionality built into PHP and allows for an arbitrary number of arguments
 * to be specified for string replacement.
 * @param String $message: string that you want to be translated. "Catalog 1 has 200 pageges" - >translate("Catalog %1d has %3d pageges",1,200)
 * @param mixed $v
 * @param String $file: translation file name
 * @return string
 */
function translate($message, $v = null) {
	return $message;
	/*
	 * FOR NOW I SHUT THIS DOWN!
	$parts = func_get_args();
	array_shift($parts);
	return vsprintf(gettext(trim($message)), $parts);
	*/
	
}


/**
 * extract one field from a two dim array
 *
 * @param array $array
 * @param string $key
 * @return array
 */
function ExtractField(array $array, $key) {
	$return = array();
	foreach ($array as $a) {
		if (!isset($a[$key])) {
			throw new Exception("Key $key not found in array");
		}
		$return[] = $a[$key];
	}
	return $return;
}

/**
 * extract one field from an array of objects
 *
 * @param array $array
 * @param string $key
 * @return array
 */
function ExtractFieldObj(array $array, $key) {
	$return = array();
	foreach ($array as $a) {
		if (!isset($a->{$key})) {
			$a->{$key} = null;
			//throw new Exception("Key $key not found in array");
		}
		$return[] = $a->{$key};
	}
	return $return;
}

/**
 * Single point to start async processes from Apache/Cron
 *
 * @param string $request for example report/build will start async/report/build
 * @param array $params
 */
function run_async_proc($request,array $params = []){
    $param_string = '';
    $sep = '';
    foreach($params as $k=>$v){
        $param_string .= $sep . base64_encode($k) . '/' . base64_encode($v);
        $sep = '/';
    }

    $cl = "php -f " . ASYNC_PATH . "/Talis.php {$request} \"{$param_string}\" > /dev/null 2>/dev/null &";
    dbgr('ASYNC',$cl);
    system($cl);
}

/**
 * Cleanup for the above - TODO, r we using it?
 *
 * @param string $str
 * @return mixed
 */
function clean_for_cl($str){
    return str_replace(['-',';','=','`','|','&','*','(',')','^','$','#','@','!','?'],'',$str);
}

/**
 *
 * @param unknown $xml
 * @param unknown $checkValue
 * @return array  <unknown, multitype:unknown >
 */
function ImsToArray($xml, $checkValue) {
    $returnXml = [];
    if (isset($xml[$checkValue])){
        $returnXml[] = $xml;
    }else{
        $returnXml = $xml;
    }
    return $returnXml;
}

/**
 * Takes an array of arrays and recursivly translates to stdClass
 * 
 * @param array $array
 * 
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


/**
 * Generatethe unique user id hash for TES and WATERSHED.
 * This is not ultra secure, just unique enogh user id
 * This is what is also used in the SAML integration
 *
 * @param integer $external_lms_user_id id from table lms3users.external_user_register
 */
function generate_codeblue_name_id($external_lms_user_id){
    switch(lifeCycle()){
        case 'prod_util':
        case 'prod_web':
            $secretSalt  = 'Unknown Visitor, Stay a While, Stay forever!';//DO NOT CHANGE!!!!!!!!!
            $idpEntityId = 'https://www.medstarnow.org/saml/saml2/idp/metadata.php';
            break;

        default:
            $secretSalt = 'shubaba';//DO NOT CHANGE!!!!!!!!!
            $idpEntityId = 'https://www.staging.medstarnow.org/saml/saml2/idp/metadata.php';
            break;
    }
    $spEntityId = 'code_blue';
    $uidData = 'uidhashbase' . $secretSalt;
    $uidData .= strlen($idpEntityId) . ':' . $idpEntityId;
    $uidData .= strlen($spEntityId) . ':' . $spEntityId;
    $uidData .= strlen($external_lms_user_id) . ':' . $external_lms_user_id;
    $uidData .= $secretSalt;
    return sha1($uidData);
}


/**
 *
 *  show a status bar on the console
 *  Example:

 for($x=1;$x<=100;$x++){
 cli_progress_bar_kur($x, 100);
 usleep(100000);
 }

 *
 * @param number $done how many items are completed
 * @param number $total how many items are to be done total
 * @param number $size optional size of the status bar
 */
function cli_progress_bar_kur($done, $total, $size = 100){
    static $start_time;

    // if we go over our bound, just ignore it
    if ($done > $total){
        return;
    }

    if (empty($start_time)){
        $start_time = time();
    }

    $now = time();
    $perc = (double) ($done / $total);
    $bar = floor($perc * $size);
    $status_bar = "\r[";
    $status_bar .= str_repeat("=", $bar);

    if ($bar < $size) {
        $status_bar .= ">";
        $status_bar .= str_repeat(" ", $size - $bar);
    } else {
        $status_bar .= "=";
    }

    $disp = number_format($perc * 100, 0);
    $status_bar .= "] $disp%  $done/$total";
    $rate = ($now - $start_time) / $done;
    $left = $total - $done;
    $eta = round($rate * $left, 2);
    $elapsed = $now - $start_time;
    $status_bar .= " remaining: " . number_format($eta) . " sec.  elapsed: " . number_format($elapsed) . " sec.";

    echo "{$status_bar}  ";
    flush();

    // when done, send a newline
    if ($done == $total) {
        echo "\n";
    }
}