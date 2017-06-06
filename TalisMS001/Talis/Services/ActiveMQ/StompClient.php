<?php namespace Talis\Services\ActiveMQ;
use function Talis\Logger\dbgn;
use function Talis\Logger\fatal;
/**
 * Abstracting the usage of pecl's Stomp extension to connect 
 * to ActiveMQ with our own queues and topics.
 * Each concrete implementation will just 
 * differentiate by the topic/queue name
 * and what process it should activate given a message received.
 *   
 * MAKE SURE THE queue name is the last part of the class name.
 * 
 * @author Itay Moav
 * @date APR-13-2015
 */
abstract class StompClient{
    const QUEUE                 = 'queue',
          TOPIC                 = 'topic'
    ;
    
    /**
     * @var \ZendQueue\Queue The resource
     */
    protected $queue            = NULL;
    
    protected function __construct(){}
    
    /**
     * return an instance with an active connection
     * @return StompCLient
     */
    static public function get_client():StompClient{
        return (new static)->connect();
    }
    
    /**
     * Make sure you use one of the two traits,
     * tQueue or tTopic, which will satisfy this 
     * contract
     */
    abstract protected function type();
    
    /**
     * validate the name is in the right structure.
     * @return string get_queue_topic_name
     */
    protected function get_queue_name():string{
        $name = explode('_',get_class($this));
        $queue_name = strtolower($name[count($name) - 1]);
        $type = $this->type();
        return "/{$type}/{$queue_name}";
    }
    
    /**
     * Return an active connetcion
     * @return StompCLient
     */
    protected function connect(array $headers=[]):StompClient{
        $env = app_env()['database']['activeMQ'];
        $url = "tcp://{$env['host']}:{$env['port']}";
        dbgn("ActiveMQ: Connecting to {$url}");
        try{
            $this->queue = new \ZendQueue\Queue([
                'name'          => $this->get_queue_name(),
                'driverOptions' => ['host' => $env['host'],
                                    'port' => $env['port'],
                                   ]
            ]);
            $this->queue->setAdapter('Activemq');
        } catch (Exception $e){
            fatal("Faild connection to Active MQ at {$url}");
            throw $e;
        }
        return $this; 
    }
}
