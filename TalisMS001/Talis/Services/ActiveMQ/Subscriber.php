<?php
//@DEPRECATED  - we use the python Daemon as listener.
/**
 * abstract the reading process.
 * MAKE SURE THE queue/topic name is the last part of the class name.
 * This client is not setup to be a Daemon, it will read until no more frames and then 
 * it will quite.
 * 
 * @author itay moav
 *
 */
abstract class Data_ActiveMQ_Subscriber extends Data_ActiveMQ_StompClient{
    
    /**
     * Override this to get more than one message per read
     * 
     * @var integer
     */
    protected $max_messages = 1;
    
    /**
     * Listen to the queue, until no more frames.
     * Will let PHP release this resource, for now
     * 
     * @param closure $do_the_baba_dance is a function which receives the body of each
     *                                   frame and does some magic on it. Use the 'use' to 
     *                                   bind this closure to something
     *                                   
     * @return integer num of frames read.
     */
    public function listen(closure $do_the_baba_dance){
        $msg_count = 0;
        try{
            $processed_messages = $this->queue->receive($do_the_baba_dance,50,50*30*4);

        } catch (Exception $e){
            $msg_count = $e->getCode();
            fatal("somthing bad happened while reading frame no {$msg_count}");
            throw $e;
        }
        return $msg_count;
    }
    
    /**
     * 
     */
    protected function process_message($msg){
        return $msg;
    }
    
    static public function delete(array $msgs){
        static::get_client()->delete_all($msgs);
    }
}
