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
     * 
     * {@inheritDoc}
     * @see \Talis\commons\iEmitter::emit()
     */
	public function emit(\Talis\Message\Response $message):void{
		$body = json_encode($message->getBody());
		if($message->getResponseType() != \Talis\Message\Response::RESPONSE_TYPE__RESPONSE){
		    \Talis\TalisMain::logger()->fatal("CHAIN BROKE {$body}",true);
		} else {
		    \Talis\TalisMain::logger()->debug('END OF PROCESS');
		    \Talis\TalisMain::logger()->debug($body);
		}
	}
}
