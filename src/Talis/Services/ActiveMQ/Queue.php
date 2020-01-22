<?php namespace Talis\Services\ActiveMQ;
//Inspired by ZendQueue;

/**
 * Class for connecting to queues performing common operations.
 * MAKE SURE THE queue name is the last part of the class name.
 */
abstract class Queue implements \Countable
{
    /**
     * return an instance with an active connection
     * @return Queue
     */
    static public function get_client(array $config):Queue{
        $options = ['driverOptions' => ['host' => $config['host'],
                                        'port' => $config['port']]
        ];
        
        $logger = $config['logger'] ?? \Talis\Logger\MainZim::factory2('nananananana',\Talis\Logger\Streams\Nan::class,\Talis\Logger\Streams\aLogStream::VERBOSITY_LVL_FATAL,null,true);
        return (new static($options,$logger));
    }
    
    
    const QUEUE                 = 'queue',
          TOPIC                 = 'topic'
    ;
    
    /**
     * Use the TIMEOUT constant in the config of a Queue
     */
    const TIMEOUT = 'timeout';
    
    /**
     * Default visibility passed to count
     */
    const VISIBILITY_TIMEOUT = 30;
    
    /**
     * connection default
     * @var string
     */
    const DEFAULT_SCHEME = 'tcp';
    
    /**
     * @var string
     */
    protected $queue_name = '';
    
    /**
     * User-provided configuration
     *
     * @var array
     */
    private $_options = array();
    
    /**
     * @var Client
     */
    private $_client = null;
    
    
    /**
     * @var array
     */
    private $_subscribed = false;
    
    /**
     * @var \Talis\Logger\Streams\aLogStream
     */
    protected $logger      = null;
    
    /**
     * Make sure you use one of the two traits,
     * tQueue or tTopic, which will satisfy this
     * contract
     */
    abstract protected function type();
    
    /**
     * Constructor
     *
     * @param  array $options
     */
    public function __construct(array $options,$logger=null){
        if(!$logger){
            $logger = \Talis\Logger\MainZim::factory2('nananananana',\Talis\Logger\Streams\Nan::class,\Talis\Logger\Streams\aLogStream::VERBOSITY_LVL_FATAL);
        }
        $this->logger = $logger; 
        
        // Make sure we have some defaults to work with
        if (! isset($options['driverOptions'][self::TIMEOUT])) {
            $options['driverOptions'][self::TIMEOUT] = self::VISIBILITY_TIMEOUT;
        }
        
        if (! isset($options['driverOptions']['scheme'])) {
            $options['driverOptions']['scheme'] = self::DEFAULT_SCHEME;
        }
        $this->setOptions($options);
        $this->set_queue_name();
        
        $driverOptions = $options['driverOptions'];
        
        $this->_client = new Client($driverOptions['scheme'], $driverOptions['host'], $driverOptions['port']);
        $connect = $this->_client->createFrame();
        
        // Username and password are optional on some messaging servers
        // such as Apache's ActiveMQ
        $connect->setCommand('CONNECT');
        if (isset($driverOptions['username'])) {
            $connect->setHeader('login', $driverOptions['username']);
            $connect->setHeader('passcode', $driverOptions['password']);
        }
        
        $response = $this->_client->send($connect)->receive();
        
        if ((false !== $response)
            && ($response->getCommand() != 'CONNECTED')
            ) {
                throw new Exception_Connection(
                    "Unable to authenticate to '{$driverOptions['scheme']}://{$driverOptions['host']}:{$driverOptions['port']}'"
                );
            }
    }
    
    /**
     * Set queue options
     *
     * @param  array $options
     * @return Queue
     */
    public function setOptions(array $options):Queue{
        $this->logger->debug('SET OPTIONS FOR QUEUE');
        $this->logger->debug($options);
        $this->_options = array_merge($this->_options, $options);
        return $this;
    }
    
    /**
     * Set an individual configuration option
     * @param string $name
     * @param mixed $value
     * @return Queue
     */
    public function setOption(string $name, $value):Queue{
        $this->_options[(string) $name] = $value;
        return $this;
    }
    
    /**
     * Returns the configuration options for the queue
     *
     * @return array
     */
    public function getOptions():array{
        return $this->_options;
    }
    
    /**
     * Determine if a requested option has been defined
     *
     * @param  string $name
     * @return bool
     */
    public function hasOption($name)
    {
        return array_key_exists($name, $this->_options);
    }
    
    /**
     * Retrieve a single option
     *
     * @param  string $name
     * @return null|mixed Returns null if option does not exist; option value otherwise
     */
    public function getOption($name)
    {
        if ($this->hasOption($name)) {
            return $this->_options[$name];
        }
        return null;
    }
    
    /**
     * Get the name of the queue
     *
     * Note: _setName() used to exist, but it caused confusion with createQueue
     * Will evaluate later to see if we should add it back in.
     *
     * @return string
     */
    public function get_queue_name(){
        return $this->queue_name;
    }
    
    /**
     * validate the name is in the right structure.
     * @return string get_queue_topic_name
     */
    protected function set_queue_name():string{
        $name = explode('_',get_class($this));
        $queue_name = strtolower($name[count($name) - 1]);
        $type = $this->type();
        return $this->queue_name = "/{$type}/{$queue_name}";
    }
    
    /**
     * Delete a message from the queue
     *
     * Returns true if the message is deleted, false if the deletion is
     * unsuccessful.
     *
     * Returns true if the adapter doesn't support message deletion.
     *
     * @param  array $message
     * @return boolean
     * @throws \Exception
     */
    public function deleteMessage(array $message)//NOTICE! This message includes some meta data, this is not the pure message in send()
    {
        $frame = $this->_client->createFrame();
        $frame->setCommand('ACK');
        $frame->setHeader('message-id', $message['handle']);
        $this->_client->send($frame);
    }
    
    /**
     * Send a message to the queue
     *
     * @param  mixed $message message
     * @return array
     * @throws \Exception
     */
    public function send($message):array{
        $frame = $this->_client->createFrame();
        $frame->setCommand('SEND');
        $frame->setHeader('destination', $this->get_queue_name());
        $frame->setHeader('content-length', strlen($message));
        $frame->setBody((string) $message);
        $this->_client->send($frame);
        
        $data = array(
            'message_id' => null,
            'body'       => $message,
            'md5'        => md5($message),
            'handle'     => null
        );
        return $data;
    }
    
    /**
     * Returns the approximate number of messages in the queue
     *
     * @return integer
     */
    public function count()
    {
        if ($this->getAdapter()->isSupported('count')) {
            return $this->getAdapter()->count();
        }
        return 0;
    }
    
    /**
     * Checks if the client is subscribed to the queue
     *
     * @return boolean
     */
    protected function isSubscribed():bool{
        return $this->_subscribed;
    }
    
    /**
     * Subscribes the client to the queue.
     *
     * @return void
     */
    protected function subscribe(array $subscribe_headers = [])
    {
        $frame = $this->_client->createFrame();
        $frame->setCommand('SUBSCRIBE');
        $subscribe_headers['destination'] = $this->get_queue_name();
        $subscribe_headers['ack']         = 'client';
        $frame->setHeaders($subscribe_headers);
        $this->_client->send($frame);
        $this->_subscribed = TRUE;
    }
    
    /**
     * Return the first element in the queue
     *
     * @param  \Closure $frame_handler function to handle the received frames the signature is (string $body)
     * @param  integer $maxMessages
     * @param  integer $timeout
     * @return array
     */
    public function receive(\Closure $frame_handler,?int $maxMessages=100,?int $timeout=self::RECEIVE_TIMEOUT_DEFAULT,array $subscribe_headers=[]):array
    {
        if ($maxMessages === null) {
            $maxMessages = 100;
        }
        if ($timeout === null) {
            $timeout = self::RECEIVE_TIMEOUT_DEFAULT;
        }
        
        // read
        $data = [];
        
        // signal that we are reading
        if(!$this->isSubscribed()) {
            $this->subscribe($subscribe_headers);
        }
        
        if ($maxMessages > 0) {
            if ($this->_client->canRead()) {
                for ($i = 0; $i < $maxMessages; $i++) {
                    try{
                        $response = $this->_client->receive();
                        switch ($response->getCommand()) {
                            case 'MESSAGE':
                                $datum = array(
                                'message_id' => $response->getHeader('message-id'),
                                'handle'     => $response->getHeader('message-id'),
                                'body'       => $response->getBody()
                                );
                                $this->logger->debug('FRAME RECEIVED');
                                $this->logger->debug($datum);
                                
                                $data[] = $datum;
                                $frame_handler($response->getBody());
                                $this->deleteThyMessage($datum['handle']);
                                break;
                            default:
                                $block = print_r($response, true);
                                throw new Exception_UnexpectedValue('Invalid response received: ' . $block,$i);
                        }
                    }
                    
                    catch(Exception_Connection $e){
                        //nothing new comes into the socket. Obviously, this does not behave like a daemon
                        break;
                    }
                }
            }
        }
        return $data;
    }
    

    /**
     * 
     * @param string $handle
     */
    public function deleteThyMessage(string $handle)
    {
        $frame = $this->_client->createFrame();
        $frame->setCommand('ACK');
        $frame->setHeader('message-id', $handle);
        $this->_client->send($frame);
    }
    
    /**
     * returns a listing of \ZendQueue\Queue details.
     * useful for debugging
     *
     * @return array
     */
    public function debugInfo()
    {
        $info = array();
        $info['self']                     = get_called_class();
        $info['options']                  = $this->getOptions();
        $info['currentQueue']             = $this->get_queue_name();
        $info['messageSetClass']          = $this->getMessageSetClass();
        
        return $info;
    }
    
    /**
     * Close the socket explicitly when destructed
     *
     * @return void
     */
    public function __destruct()
    {
        // Gracefully disconnect
        if($this->_client) $this->_client->getConnection()->close(true);
        unset($this->_client);
    }
}
