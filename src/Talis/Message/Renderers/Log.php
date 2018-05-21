<?php namespace Talis\Message\Renderers;
/**
 * This is what we know as VIEW, it does the actual echo.
 * This one is for Cli responses (aka emit text + exit(status)
 * 
 * @author Itay Moav
 * @date 2017-06-07
 */
class Log implements \Talis\commons\iEmitter{
	/**
	 * Formats and echoes the results headers and then body
	 */
	public function emit(\Talis\Message\Response $message):void{
		$stat   = $message->getStatus()->getCode();
		$body = json_encode($message->getBody());
		if($stat>=500){
			\Talis\Logger\fatal("CHAIN BROKE {$body}");
			exit(1);
		}
		\Talis\Logger\dbgr('END OF PROCESS',$body);
	}
}
