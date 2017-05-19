<?php
use Zend\Http\Header\From;
use Zend\ModuleManager\Listener\OnBootstrapListener;
/**
 * abstract the publishisng process.
 * MAKE SURE THE queue name is the last part of the class name.
 * 
 * @author itay moav
 *
 */
abstract class Data_ActiveMQ_Publisher extends Data_ActiveMQ_StompClient{
    /**
     * Place holder for filtering of messages
     *
     * @param string $msg
     * @return string filtered message
     */
    protected function filter_message($msg){
        return $msg.'';//cast to string
    }
    
    /**
     * PUBLISH!
     * @param string $msg
     * @return Ambigous <string, string>
     */
    public function publish($msg){
        $msg = $this->filter_message($msg);
        dbgn("ActiveMQ: Sending filtered message [{$msg}]");
        $destination = $this->get_queue_name();
        dbgn("ActiveMQ: posting to {$destination}");
        try{
            
            $this->queue->send($msg);
        } catch (Exception $e){
            fatal("ActiveMQ: Failure posting to {$destination}");
            throw $e;
        }
        return $msg;
    }  
}
