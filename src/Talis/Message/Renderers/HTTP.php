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
		header('Content-Type: application/json; charset=utf-8');
		$all_other_headers = $message->getHeaders();
		\Talis\Corwin::logger()->debug('SENDING HEADERS');
		\Talis\Corwin::logger()->debug([$header] + $all_other_headers);
		if($all_other_headers){
		    foreach($all_other_headers as $other_header){
		        header($other_header);
		    }
		}
		$body = json_encode($message->getBody());
		if($body === false){
		    throw new \Exception('Could not json encode the payload');
		}
		\Talis\Corwin::logger()->debug('SENDING BODY');
		\Talis\Corwin::logger()->debug($body);
		echo $body;
	}
}
