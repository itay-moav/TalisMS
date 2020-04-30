<?php namespace Talis\Message\Renderers;
/**
 * This decorates a chain link with the iRenderer to echo a pure JSON of the payload with
 * status 200.
 * 
 * @author Itay Moav
 * @date 20202-04-30
 */
class tJson200 implements \Talis\commons\iRenderable{
    public function render(\Talis\commons\iEmitter $emitter):void{
        $res = json_encode($this->Response->getPayload());
        dbgr('JSON RESPONSE',$res);
        header('HTTP/1.1 200 Ok');
        header('Content-Type: application/json');
        $all_other_headers = $this->Response->getHeaders();
        if($all_other_headers){
            \dbgr('SENDING HEADERS',$all_other_headers);
            
            foreach($all_other_headers as $other_header){
                header($other_header);
            }
        }
        echo $res;
    }
}
