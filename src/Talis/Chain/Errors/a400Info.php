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
        \info('Following two entries are error prms and human message of the error');
        \info(print_r($this->params,true));
        \info($this->format_human_message());
        
        $response = new \Talis\Message\Response;
        $response->setBody(\Talis\commons\array_to_object(['type'=>'error','message'=>$this->format_human_message()]));
        $status_class = "\Talis\Message\Status\Code{$this->http_code}";
        $response->setStatus(new $status_class);
        $response->markError();
        $emitter->emit($response);
    }
}
