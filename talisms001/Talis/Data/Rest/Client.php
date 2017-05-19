<?php
/**
 * A general rest request and response.
 * Provide shortcuts mainly and some debug handling
 * 
 * @author Itay Moav
 *
 */
class Data_Rest_Client{
    /**
     * @var boolean If marked true, it will redirect the request to Aybara to the REST Proxy action
     */
    protected $use_aybara_as_proxy = false;
    
    /**
     * @var ZendIHttpClient
     */
    protected $client = null;

    /**
     * @var string the main url of the service.
     */
    protected $service_url = '';

    /**
     * @var string the entire respons as a simple string
     */
    protected $response_body = '';
    
    /**
     * @var array of the header response
     */
    protected $response_header = [];
    
    /**
     * @var integer http response code
     */
    protected $response_code   = 0;
    
    /**
     * @var bool in case of request time out, should I retry the request?
     */
    protected $should_retry = false;
    
    /**
     * @var number in case of request time out, how many times should I retry before giving up all hope and resign myself to higher powers of law and chaos.
     */
    protected $no_of_tries = 0;
    
    /**
     * @var number in case of request time out, how long should I wait until next try
     */
    protected $wait_for_in_seconds = 0;
    
    /**
     * @var array Cashing the init headers. for reset the client
     */
    protected $headers = [];
  
    /**
     * This pair is comming from the environment ['external sources'][....]
     * @var string
     */
    protected $key,$secret;

    /**
     * @param string $url
     * @param array $headers [header name => value, header name => value]
     * @param bool $should_retry -> in case of request time out, should I retry the request?
     * @param number $no_of_retries -> in case of request time out, how many times should I retry before giving up all hope and resign myself to higher powers of law and chaos.
     * @param number $wait_for_in_seconds  -> in case of request time out, how long should I wait until next try
     */
    public function __construct($url, array $headers=[], $should_retry=false, $no_of_retries=5, $wait_for_in_seconds=3){
        $this->client = new ZendIHttpClient(
            $url,
            array(
                'adapter'       => 'Zend\Http\Client\Adapter\Curl',
                'sslverifypeer' => false,
                'maxredirects' => 1,
                'timeout'      => 5,
                'useragent'    => 'LMS_LiveAccess'
            )
        );
        $this->service_url = $url;
        $this->headers = array_merge($headers,['Content-type'  => 'application/json']);
        $this->client->setHeaders($this->headers);
        
        $this->should_retry         = $should_retry;
        $this->no_of_tries          = $no_of_retries;
        $this->wait_for_in_seconds  = $wait_for_in_seconds;
    }
    
    /**
     * Main function, activate the call to remote URI
     * This will populate all the response fields
     * 
     * @return string
     */
    protected function call(){
        dbgr('REQUEST headers',$this->client->getRequestHeaders());
        
        if($this->should_retry){
            $tries = 0;
            while(true){
                try{
                    dbgn("Try sending request, iteration [{$tries}]");
                    $response = $this->internal_call();
                    break; //if I got here, all is good. Breaking outside of the loop.
                    
                } catch (\Zend\Http\Client\Exception\ExceptionInterface $e){
                    $tries++;
                    if($tries >= $this->no_of_tries){
                        throw $e;
                    }
                    sleep($this->wait_for_in_seconds);
                }
            }
        }else{
            $response = $this->internal_call();
        }
        
        $this->response_body = json_decode($response->getBody());
        $this->response_code = $response->getStatusCode();
        $this->response_header = $response->getHeaders();

        dbgr('RESPONSE CODE',$this->response_code);
        dbgr('RESPONSE HEADERS',$this->response_header);
        //dbgr('RESPONSE BODY',$this->response_body);
        $this->client->resetParameters(true);
        $this->client->setHeaders($this->headers);
        
        /**
         * Handling 500s
         */
        if( ($this->response_code*1) >= 500){// we got one of the 500s status codes. Those should be handled on the application/business level
                                             // as they depend on the specific service provider
            $msg = print_r($this->body(),true);
            error('Error in a REST request: ' . $msg);
            $msg = substr($msg,0,200);
            throw new Exception_HTTP_ServerError($msg,$this->response_code);
        }
        return $this->body();
    }
    
    /**
     * I need this logic to do all real calls from one place, and use Aybara Proxy when needed
     * I instantiate a seprate Client here for Proxy and put in the right details,
     * I also parse here the return so as to be able to enable the calling app to process this 
     * as if there was no proxy in the middle
     * 
     * @return \Zend\Http\Response
     */
    final protected function internal_call(){
        if($this->use_aybara_as_proxy){
            $sep = 'BALABALABALA';
            $raw_data = array_to_object([
                'action'        => 'ProxyAway',
                'params'        => null
            ]);
            $raw_data->params = [
                'should_try'    => $this->no_of_tries,
                'wait_for_in_seconds' => $this->wait_for_in_seconds,
                'uri'           => base64_encode($this->client->getUri(true).''),
                'headers'       => base64_encode(serialize($this->headers)),
                'key_secret'    => base64_encode("{$this->key}{$sep}{$this->secret}"),
                'method'        => $this->client->getCurrentMethod(),
                'body'          => base64_encode($this->client->getRawRequestData())
            ];
            
            $c = app_env()['external sources']['aybara'];
            $url = "{$c['endpoint_url']}/corwin/proxy/away"; 
            $Client = new ZendIHttpClient(
                $url,
                array(
                    'adapter' => 'Zend\Http\Client\Adapter\Curl',
                    'sslverifypeer' => false,
                    'maxredirects' => 1,
                    'timeout'      => ($this->no_of_tries * ($this->wait_for_in_seconds+1)),
                    'useragent'    => 'LMS_ProxyAybara'
                )
            );
            $Client->setHeaders(['Content-type' => 'application/json']);
            $Client->setAuth($c['key'],$c['secret'],ZendIHttpClient::AUTH_BASIC);
            $Client->setRawBody(json_encode($raw_data));
            $Client->setMethod(\Zend\HTTP\Request::METHOD_POST);
            
            //COMMENT OUT, AS THIS IS INTENSIVE IN LARGE QUANTITIES

            dbgr('PROXY TO '      ,$url);
            dbgr('PROXY RAW DATA' ,$raw_data);
            dbgr('uri'        ,base64_decode($raw_data->params['uri']));
            dbgr('headers'    ,base64_decode($raw_data->params['headers']));
            dbgr('key_secret' ,base64_decode($raw_data->params['key_secret']));
            dbgr('body'       ,base64_decode($raw_data->params['body']));
            $un_parsed_response = json_decode($Client->send()->getBody())->params;
            $response_headers = unserialize(base64_decode($un_parsed_response->headers));
            $response_body    = base64_decode($un_parsed_response->body);
            //dbgr('RETURN RAW RESPONSE',$un_parsed_response);
            dbgr('parsed headers',$response_headers);
            //dbgr('parsed body',$response_body);
            $response = new FakeResponse($un_parsed_response->code, $response_headers,$response_body);
            
            
            
        }else{
            $response = $this->client->send();
        } 
        return $response;
    }

    /**
     *
     */
    public function body(){
        return $this->response_body;
    }

    /**
     *
     */
    public function headers(){
        return $this->response_header;
    }

    /**
     *
     */
    public function code(){
        return $this->response_code;
    }

    /**
     * @var string $uri_requested either empty or uri starts with an /
     */
    public function get($uri_requested='',$page='',$page_size=''){
        $uri_requested = str_replace(' ','%20',$uri_requested);
        $paging = '';
        if(strpos($uri_requested,'?') === false && ($page || $page_size)){
            $paging = '?yuma=monster';
        }
        
        if($page){
            $paging .='&_offset='.$page;
        }
        
        if($page_size){
            $paging .='&_limit='.$page_size;
        }
        dbgn($this->service_url . $uri_requested . $paging);
        $this->client->setUri($this->service_url . $uri_requested . $paging);
        dbgr('GET',$this->client->getUri(true));
        $this->client->setMethod(\Zend\HTTP\Request::METHOD_GET);
        return $this->call();
    }

    /**
     * ['fieldname':value [,'fieldname':value ...] ]
     */
    public function delete(array $resource_identifier){
        $raw_data = json_encode(array_to_object($resource_identifier));
        $this->client->setUri($this->service_url);
        
        dbgr('DELETE',$this->client->getUri(true));
        dbgr('original data',$resource_identifier);
        dbgr('json',$raw_data);
        
        $this->client->setMethod(\Zend\HTTP\Request::METHOD_DELETE);
        $this->client->setRawBody($raw_data);
        return $this->call();
    }

    /**
     *
     */
    public function post(stdClass $insert_data){
        $this->client->setUri($this->service_url);
        $raw_data = json_encode($insert_data);
        
        dbgr('POST',$this->client->getUri(true));
        dbgr('original data', $insert_data);
        dbgr('json',$raw_data);
        $this->filterRawJSONPost($raw_data);//put dbg in this method if u choose to use it
        
        $this->client->setMethod(\Zend\HTTP\Request::METHOD_POST);
        $this->client->setRawBody($raw_data);
        
        return $this->call();
    }
    
    protected function filterRawJSONPost(&$raw_data){}

    /**
     *
     */
    public function put(stdClass $update_data){
        $this->client->setUri($this->service_url);
        $raw_data = json_encode($update_data);
        
        dbgr('PUT',$this->client->getUri(true));
        dbgr('original data',$update_data);
        dbgr('json',$raw_data);
        
        $this->client->setMethod(\Zend\HTTP\Request::METHOD_PUT);
        $this->client->setRawBody($raw_data);
        return $this->call();
    }
}



/**
 * Wrapper for the Zend 1.x http class to add the getRequestHeaders()
 * This is for debug purposes
 * 
 * @author Itay Moav
 *
 */
class ZendIHttpClient extends \Zend\Http\Client{
    private $my_headers = [];
    
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
 * Imitates the Zend Http Response class API for sake of PRoxy calls
 * 
 * @author Itay Moav
 */
class FakeResponse{
    private $code,$headers,$body;
    
    public function __construct($code,array $headers,$body){
        $this->code    = $code;
        $this->headers = $headers;
        $this->body    = $body;
    }
    
    public function getBody(){
        return $this->body;
    }
    
    public function getStatusCode(){
        return $this->code;
    }
    
    public function getHeaders(){
        return $this->headers;
    }
}
