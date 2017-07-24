<?php namespace Api;
use Talis\Logger as L;

/**
 * Responsebility: Parses the user input to identify the API class to instantiate
 * @author Itay Moav
 * @Date  2017-05-19
 */
class TestPingRead extends \Talis\Chain\aFilteredValidatedChainLink implements \Talis\commons\iRenderable{

	public function render(\Talis\commons\iEmitter $emitter):void{
		L\dbgn('PONG');
		$response = new \Talis\Message\Response;
		$response->setBody(\Talis\commons\array_to_object(['type'=>'test','message'=>'boom','params'=>print_r($this->Request->getAllGetParams(),true)]));
		$response->setStatus(new \Talis\Message\Status\Code200);
		$emitter->emit($response);
	}
}