<?php namespace Talis\Message\Renderers;
/**
 * This is what we know as VIEW, it does the actual echo.
 * This one is for Cli responses (aka emit text + exit(status)
 * 
 * @author Itay Moav
 * @date 2017-06-07
 */
class Cli implements \Talis\commons\iEmitter{
	/**
	 * Formats and echoes the results headers and then body
	 */
	public function emit(\Talis\Message\Response $message):void{
		//TODO move to exit status? $stat   = $message->getStatus()->getCode();
		$body = json_encode($message->getBody());
		echo $body;
	}
}
