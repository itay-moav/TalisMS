<?php namespace Talis\Services\ActiveMQ;
use function Talis\Logger\dbgn;

/**
 * abstract the publishisng process.
 * MAKE SURE THE queue name is the last part of the class name.
 * 
 * @author itay moav
 *
 */
abstract class Publisher extends Queue{
    /**
     * Place holder for filtering of messages
     *
     * @param string $msg
     * @return string filtered message
     */
    protected function filter_message($msg):string{
        return $msg.'';//cast to string
    }

    /**
     * 
     * @param string $msg
     * @throws \Exception
     * @return string
     */
    public function publish($msg):string{
        $msg = $this->filter_message($msg);
        dbgn("ActiveMQ: Sending filtered message [{$msg}]");
        $destination = $this->get_queue_name();
        dbgn("ActiveMQ: posting to {$destination}");
        try{
            
            $this->send($msg);
        } catch (\Exception $e){
            fatal("ActiveMQ: Failure posting to {$destination}");
            throw $e;
        }
        return $msg;
    }  
}
