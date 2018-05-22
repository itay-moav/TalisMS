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
abstract class StompClientTOBEDELTEDONCE{
    const QUEUE                 = 'queue',
          TOPIC                 = 'topic'
    ;
    
    /**
     * @var Queue The resource
     */
    protected $queue            = NULL;
    
    /**
     * Config values for this connection [host,port]
     * @var array
     */
    protected $config           = [];
    
    protected function __construct(array $config){
        $this->config = $config;
    }
    
    /**
     * return an instance with an active connection
     * @return StompCLient
     */
    static public function get_client(array $config):StompClient{
        return (new static($config))->connect();
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
    protected function connect():StompClient{
        $url = "tcp://{$this->config['host']}:{$this->config['port']}";
        dbgn("ActiveMQ: Connecting to {$url}");
        try{
            $this->queue = new Queue([
                'name'          => $this->get_queue_name(),
                'driverOptions' => ['host' => $this->config['host'],
                                    'port' => $this->config['port'],
                                   ]
            ]);
            //$this->queue->setAdapter('Activemq');
        } catch (\Exception $e){
            fatal("Faild connection to Active MQ at {$url}");
            throw $e;
        }
        return $this; 
    }
}
