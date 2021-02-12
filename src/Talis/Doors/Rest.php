<?php namespace Talis\Doors;

/**
 * Main entry point for the request chain
 * Translate the input into the initial request object
 * and moves it along
 * 
 * Will assume 2 levels [action][subaction] and [type] is calculated by the http method (post=create, get=read, delete=delete, put=update. for example event/repeat/create|update|read|delete
 * 
 * Loads the right controller and action.
 * Renders the $Result of the action
 * Can handle page caching.
 * Error handling
 *  
 */
class Rest extends HTTP{
	
	/**
	 * Parses the server input to generate raw uri parts
	 * @return array<string>
	 */
	protected function get_uri_from_server():array{
	    $method = '';
	    switch(isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET'){
	        case 'POST':
	            $method = 'create';
	            break;
	            
	        case 'PUT':
	            $method = 'update';
	            break;
	            
	        case 'DELETE':
	            $method = 'delete';
	            break;
	            
	        case 'HEAD':
	            $method = 'head';
	            break;
	        
	        case 'OPTIONS':
	            $method = 'options';
	            break;
	        
	        default:
	            $method = 'read';
	            break;
	    }
	    
	    $this->full_uri = $this->root_uri ? explode($this->root_uri,$_SERVER ['REQUEST_URI'])[1] : $_SERVER ['REQUEST_URI'];//@phpstan-ignore-line
		//remove ? and after if exists
		$without_question = rtrim(explode('?',$this->full_uri)[0],'/');
		$request_parts    = explode('/',$without_question);
		//insert the method as the third part
		array_splice($request_parts,3,0,$method);
		return $request_parts;
	}
}
