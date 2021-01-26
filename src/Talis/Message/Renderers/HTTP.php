<?php namespace Talis\Message\Renderers;
/**
 * This is what we know as VIEW, it does the actual echo.
 * This one is for REST responses (aka headers+JSON)
 * 
 * @author Itay Moav
 * @date 2017-05-30
 */
class HTTP implements \Talis\commons\iEmitter{
    /**
     * Formats and echoes the results headers and then body
     * {@inheritDoc}
     * @see \Talis\commons\iEmitter::emit()
     */
	public function emit(\Talis\Message\Response $message):void{
		$stat   = $message->getStatus()->getCode();
		$explanation = $message->getStatus()->getMsg();
		$header = "HTTP/1.1 {$stat} {$explanation}";
		header($header);
		$all_other_headers = $message->getHeaders();
		\ZimLogger\MainZim::$CurrentLogger->debug('SENDING HEADERS');
		\ZimLogger\MainZim::$CurrentLogger->debug([$header] + $all_other_headers);
		if($all_other_headers){
		    foreach($all_other_headers as $other_header){
		        header($other_header);
		    }
		}
		$body = json_encode($message->getBody());
		\ZimLogger\MainZim::$CurrentLogger->debug('SENDING BODY');
		\ZimLogger\MainZim::$CurrentLogger->debug($body);
		echo $body;
	}
}
