<?php namespace Talis\Chain\Errors;
use \Talis\Logger as L;
use Talis\Chain\aChainLink;
use function Talis\commons\array_to_object;

/**
 * basic error/problem class
 * 
 * @author Itay Moav
 * @date 2017-05-23
 *
 */
abstract class aError extends \Talis\Chain\aChainLink implements \Talis\commons\iRenderable{
	protected $http_code	 = 0
	;
	
	abstract protected function format_human_message():string;
	
	/**
	 * This is an end of the line chain link, return itself.
	 * @return Talis\Chain\iReqRes
	 */
	public function process():\Talis\Chain\aChainLink{
		return $this;
	}
	
	/**
	 *  
	 */
	public function render(\Talis\commons\iEmitter $emitter):void{
		L\error('Following two entries are error prms and human message of the error');
		L\error(print_r($this->params,true));
		L\error($this->format_human_message());
		
		$response = new \Talis\Message\Response;
		$response->setBody(array_to_object(['type'=>'error','message'=>$this->format_human_message()]));
		$status_class = "\Talis\Message\Status\Code{$this->http_code}";
		$response->setStatus(new $status_class);
		$emitter->emit($response);
	}
}
