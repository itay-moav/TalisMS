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
class Corwin{
	/**
	 * I am setting this up in the specific apps using Talis. 
	 * I usually would like to use  it to login someone or check generic roles etc
	 * 
	 * @var ?callable a function to run on init.
	 */
    static public ?callable $registered_init_func = null;
	
	/**
	 * The name of the router to use to get the API class and GET params.
	 * This is a string of the namespace + classname.
	 * Usually you will put it in the bootstrap file.
	 * 
	 * @var string
	 */
	static public string $registered_router    = \Talis\Router\DefaultRouter::class;
	
	/**
	 * Context for this process, which is not part of the Response Request
	 * @var \Talis\Data\Context
	 */
	static public \Talis\Data\Context $Context = null;
	
	/**
	 * 
	 * @var \Talis\Router\aRouter
	 */
	private \Talis\Router\aRouter $Router = null;
	
	/**
	 * @var array
	 */
	private array $extra_params = [];
	
	/**
	 * Body of the request, json decoded string
	 * @var \stdClass
	 */
	private \stdClass $req_body  = null;
	
	/**
	 * The head of the BL process chain, usually that would be the API head class built from the route,
	 * but on occassions that would be the error class, in case there where issues
	 * 
	 * @var \Talis\Chain\aChainLink $RequestChainHead
	 */
	private \Talis\Chain\aChainLink $RequestChainHead = null;

	/**
	 * Holds the request parameters (GET/POST etc)
	 * 
	 * @var \Talis\Message\Request $Request
	 */
	private \Talis\Message\Request $Request;
	
	/**
	 * Main entry point after the Door for a specific protocol is finished (http/rest/stopmp/async etc)
	 * 
	 * @param array $request_parts
	 * @param \stdClass $request_body
	 * @param string $full_uri
	 * @return \Talis\Corwin
	 */
	public function begin(array $request_parts,?\stdClass $request_body,string $full_uri){
		$this->req_body = $request_body;
		self::$Context = new \Talis\Data\Context;
		
		try{
		    $this->Router = new self::$registered_router($request_parts);
		    $this->Router->generate_route();
			$this->extra_params = $this->Router->generate_query();
			
			$this->build_request($full_uri);
			$this->RequestChainHead = $this->Router->get_chainhead($this->Request,new \Talis\Message\Response);

			//the dynamic init
			if(self::$registered_init_func){
				$func = self::$registered_init_func;
				$func($this->Request);
			}
			
		} catch(\Talis\Exception\BadUri $e){
		    $req = $this->Request ?: null;
		    $this->RequestChainHead = new Chain\Errors\ApiNotFound($req,new \Talis\Message\Response,[$e->getMessage()]);
		}
		return $this;
	}
	
	/**
	 * Init the API class and call it's dependency checks
	 * 
	 * @return Chain\aChainLink
	 */
	public function nextLinkInchain():\Talis\commons\iRenderable{
		return $this->RequestChainHead->nextLinkInchain();
	}
	
	private function build_request(string $full_uri):void{
		\dbgr('BUILDING REQUEST WITH BODY',$this->req_body);
		$this->Request = new \Talis\Message\Request($full_uri,$this->extra_params,$this->req_body);
	}
}
