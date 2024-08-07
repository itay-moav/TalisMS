<?php namespace Talis;

/**
 * MAIN APP ENTRY POINT!
 * 
 * Responsebility: Parses the user input to identify the API class to instantiate
 * This is the ROUTER
 * 
 * @author Itay Moav
 * @Date  2017-05-19
 */
class TalisMain{
    
    /**
     * instantiate this in the app bootstrap
     * @var string
     */
    static public string $APP_PATH;

	/**
	 * Logger used for all Talis lib log calls.
	 * 
	 * @var \Talis\commons\iLogger
	 */
	static private \Talis\commons\iLogger $talis_logger;
    
	/**
	 * @param \Talis\commons\iLogger $logger
	 */
	static public function set_logger(\Talis\commons\iLogger $logger):void{
		self::$talis_logger = $logger;
	}

	/**
	 * @return \Talis\commons\iLogger
	 */
	static public function logger():\Talis\commons\iLogger{
		return self::$talis_logger;
	}

	/**
	 * I am setting this up in the specific apps using Talis. 
	 * I usually would like to use  it to login someone or check generic roles etc
	 * 
	 * @var ?callable a function to run on init.
	 */
    static public $registered_init_func = null;
	
	/**
	 * The name of the router to use to get the API class and GET params.
	 * This is a string of the namespace + classname.
	 * Usually you will put it in the bootstrap file.
	 * 
	 * @var string
	 */
	static public string $registered_router = \Talis\Router\DefaultRouter::class;
	
	/**
	 * Context for this process, which is not part of the Response Request
	 * @var \Talis\Context
	 */
	static public \Talis\Context $Context;
	
	/**
	 * 
	 * @var \Talis\Router\aRouter
	 */
	private \Talis\Router\aRouter $Router;
	
	/**
	 * @var array<mixed>
	 */
	private array $extra_params = [];
	
	/**
	 * Body of the request, json decoded string
	 * @var ?\stdClass
	 */
	private ?\stdClass $req_body;
	
	/**
	 * The head of the BL process chain, usually that would be the API head class built from the route,
	 * but on occassions that would be the error class, in case there where issues
	 * 
	 * @var \Talis\Chain\aChainLink $RequestChainHead
	 */
	private \Talis\Chain\aChainLink $RequestChainHead;

	/**
	 * Holds the request parameters (GET/POST etc)
	 * 
	 * @var \Talis\Message\Request $Request
	 */
	private \Talis\Message\Request $Request;
	
	/**
	 * Main entry point after the Door for a specific protocol is finished (http/rest/stopmp/async etc)
	 * 
	 * @param array<string> $request_parts
	 * @param \stdClass $request_body
	 * @param string $full_uri
	 * @return \Talis\TalisMain
	 */
	public function begin(array $request_parts,?\stdClass $request_body,string $full_uri):\Talis\TalisMain{
		$this->req_body = $request_body;
		self::$Context = new \Talis\Context;
		$Response = new \Talis\Message\Response;
		
		try{
		    $this->Router = new self::$registered_router($request_parts);
		    $this->extra_params = $this->Router->generate_query();
		    $this->build_request($full_uri);
		    $this->Router->generate_route();
		    $this->RequestChainHead = $this->Router->get_chainhead($this->Request,$Response);
			
			//the dynamic init
			if(self::$registered_init_func){
				$func = self::$registered_init_func;
				$func($this->Request);
			}
			
		} catch(\Talis\Exception\BadUri $e){
		    $this->RequestChainHead = new Chain\Errors\ApiNotFound($this->Request,$Response,[$e->getMessage()]);
		}
		return $this;
	}
	
	/**
	 * Init the API class and call it's dependency checks
	 * 
	 * @return \Talis\commons\iRenderable
	 */
	public function nextLinkInchain():\Talis\commons\iRenderable{
		return $this->RequestChainHead->nextLinkInchain();
	}
	
	/**
	 * @param string $full_uri
	 */
	private function build_request(string $full_uri):void{
	    self::logger()->debug('BUILDING REQUEST WITH BODY');
	    self::logger()->debug($this->req_body);
		$this->Request = new \Talis\Message\Request($full_uri,$this->extra_params,$this->req_body);
	}
}
