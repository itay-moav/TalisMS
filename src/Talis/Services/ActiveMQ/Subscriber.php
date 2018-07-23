<?php namespace Talis\Services\ActiveMQ;
//use function Talis\Logger\dbgn;
//use function Talis\Logger\fatal;

/**
 * abstract the reading process.
 * MAKE SURE THE queue/topic name is the last part of the class name.
 * This client is not setup to be a Daemon, it will read until no more frames and then 
 * it will quite.
 * 
 * @author itay moav
 *
 */
abstract class Subscriber extends Queue{
    /**
     * Listen to the queue, until no more frames.
     * Will let PHP release this resource, for now
     * 
     * @param \closure $do_the_baba_dance is a function which receives the body of each
     *                                   frame and does some magic on it. Use the 'use' to 
     *                                   bind this closure to something
     *                                   
     * @param array $subscribe_headers  array of headers to be used in the subscribe call buried deep in the recieve() method.
     *                                   
     * @return integer num of frames read.
     */
    public function listen(\closure $do_the_baba_dance,array $subscribe_headers=[]):int{
        $msg_count = 0;
        try{
            $msg_count = count($this->receive($do_the_baba_dance,50,50*30*4,$subscribe_headers));

        }
        catch (Exception_UnexpectedValue $e){
            $msg_count = $e->getCode();
            //fatal("somthing bad happened while reading frame no {$msg_count}");
            throw $e;
        }
        
        return $msg_count;
    }
}
