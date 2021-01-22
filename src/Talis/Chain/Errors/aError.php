<?php namespace Talis\Chain\Errors;

/**
 * basic error/problem class
 * 
 * @author Itay Moav
 * @date 2017-05-23
 *
 */
abstract class aError extends \Talis\Chain\aChainLink implements \Talis\commons\iRenderable{
	protected int $http_code	 = 0;
	
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
		\error('Following two entries are error prms and human message of the error');
		\error(print_r($this->params,true));
		\error($this->format_human_message());
		
		$response = new \Talis\Message\Response;
		$response->setBody(\Talis\commons\array_to_object(['type'=>'error','message'=>$this->format_human_message()]));
		$status_class = "\Talis\Message\Status\Code{$this->http_code}";
		$response->setStatus(new $status_class);
		$response->markError();
		$emitter->emit($response);
	}
}
