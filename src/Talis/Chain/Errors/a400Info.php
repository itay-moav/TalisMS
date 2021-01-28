<?php namespace Talis\Chain\Errors;

/**
 * basic bad request, this is a user error
 * not server. Devs should not worry about this
 *
 * @author Itay Moav
 * @date 2020-06009
 *
 */
abstract class a400Info extends \Talis\Chain\Errors\aError{
    
    /**
     *
     */
    public function render(\Talis\commons\iEmitter $emitter):void{
        \ZimLogger\MainZim::$CurrentLogger->info('Following two entries are error prms and human message of the error',false);
        \ZimLogger\MainZim::$CurrentLogger->info($this->params,false);
        \ZimLogger\MainZim::$CurrentLogger->info($this->format_human_message(),true);
        
        $response = new \Talis\Message\Response;
        $body     = new \stdClass;
        $body->type    = 'error';
        $body->message = $this->format_human_message();
        $response->setBody($body);
        
        $status_class = "\Talis\Message\Status\Code{$this->http_code}";
        $response->setStatus(new $status_class);
        $response->markError();
        $emitter->emit($response);
    }
}
