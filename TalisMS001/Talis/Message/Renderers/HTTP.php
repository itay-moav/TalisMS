<?php namespace Talis\Message\Renderers;
use Talis\Logger as L;

class HTTP implements \Talis\commons\iEmitter{
	/**
	 * Formats and echoes the results headers and then body
	 */
	public function emit(\Talis\Message\Response $message):void{
		$stat   = $message->getStatus()->getCode();
		$explanation = $message->getStatus()->getMsg();
		$header = "HTTP/1.1 {$stat} {$explanation}";
		header($header);
		$body = json_encode($message->getBody());
		echo $body;
	}
}
