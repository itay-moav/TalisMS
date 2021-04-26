<?php namespace Talis;
/**
 * The context object purpose is to be shared via 
 * and entire process and hold information/resources/settings common to it all.
 * As opposed to Response/Request this is not a Message
 * that can be transfered over to another system (i.e. not serilizable /JSONable).
 * The contex is private to the process.
 * 
 * The context is being held in a static variable in 
 * @author itay
 *
 */
class Context{
    public const NaN = 'NaN';
    
    /**
     * 
     * @var array<mixed> of shared resources in the current process. 
     */
	private array $resources = [];
	
	/**
	 * getter/setter for resources array.
	 * 
	 * @param string $resource_name
	 * @param mixed $resource
	 * @return mixed
	 */
	public function resource(string $resource_name,$resource=null){
	    if($resource){
	        $this->resources[$resource_name] = $resource;
	    }
	    return $this->resources[$resource_name];
	}
		
}