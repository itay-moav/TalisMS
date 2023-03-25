<?php namespace Talis\Chain\Errors;

/**
 * basic error/problem class
 * 
 * @author Itay Moav
 * @date 2017-05-23
 *
 */
abstract class aError extends \Talis\Chain\aChainLink implements \Talis\commons\iRenderable{
	/**
	 * 
	 * @var integer
	 */
    protected int $http_code	 = 0;
	
    /**
     * 
     * @return string
     */
	abstract protected function format_human_message():string;
	
	/**
	 * This is an end of the line chain link, return itself.
	 * @return \Talis\Chain\aChainLink
	 */
	public function process():\Talis\Chain\aChainLink{
		return $this;
	}
	
	/**
	 *  
	 */
	public function render(\Talis\commons\iEmitter $emitter):void{
	    \ZimLogger\MainZim::$CurrentLogger->error('Following two entries are error prms and human message of the error',false);
	    \ZimLogger\MainZim::$CurrentLogger->error($this->params,false);
	    \ZimLogger\MainZim::$CurrentLogger->error($this->format_human_message(),true);
				
		$this->Response->setMessage($this->format_human_message());
		$status_class = "\Talis\Message\Status\Code{$this->http_code}";
		$this->Response->setStatus(new $status_class);
		$this->Response->markError();
		$emitter->emit($this->Response);		
	}
}
