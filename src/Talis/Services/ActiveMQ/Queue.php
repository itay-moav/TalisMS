<?php namespace Talis\Services\ActiveMQ;
//Inspired by ZendQueue;

use \Countable;

/**
 * Class for connecting to queues performing common operations.
 */
class Queue implements \Countable
{
    /**
     * Use the TIMEOUT constant in the config of a \ZendQueue\Queue
     */
    const TIMEOUT = 'timeout';
    
    /**
     * Default visibility passed to count
     */
    const VISIBILITY_TIMEOUT = 30;
    
    /**
     * Use the NAME constant in the config of \ZendQueue\Queue
     */
    const NAME = 'name';
    
    /**
     * connection default
     * @var string
     */
    const DEFAULT_SCHEME = 'tcp';
    
    /**
     * @var \ZendQueue\Adapter
     */
    //protected $_adapter = null;
    
    /**
     * User-provided configuration
     *
     * @var array
     */
    protected $_options = array();
    
    /**
     * Zend_Queue message class
     *
     * @var string
     */
    protected $_messageClass = '\ZendQueue\Message';
    
    /**
     * @var \ZendQueue\Stomp\Client
     */
    private $_client = null;
    
    
    /**
     * @var array
     */
    private $_subscribed = false;
    
    /**
     * Zend_Queue message iterator class
     *
     * @var string
     */
    protected $_messageSetClass = '\ZendQueue\Message\MessageIterator';
    
    /**
     * Constructor
     *
     * @param  array $options
     */
    public function __construct(array $options){
            
        // Make sure we have some defaults to work with
        if (! isset($options['driverOptions'][self::TIMEOUT])) {
            $options['driverOptions'][self::TIMEOUT] = self::VISIBILITY_TIMEOUT;
        }
        
        if (! isset($options['driverOptions']['scheme'])) {
            $options['driverOptions']['scheme'] = self::DEFAULT_SCHEME;
        }
        $this->setOptions($options);
        
        $driverOptions = $options['driverOptions'];
        
        $this->_client = new \ZendQueue\Stomp\Client($driverOptions['scheme'], $driverOptions['host'], $driverOptions['port']);
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
    public function setOptions(array $options):Queue
    {
        $this->_options = array_merge($this->_options, $options);
        return $this;
    }
    
    /**
     * Set an individual configuration option
     *
     * @param  string $name
     * @param  mixed $value
     * @return \ZendQueue\Queue
     */
    public function setOption($name, $value)
    {
        $this->_options[(string) $name] = $value;
        return $this;
    }
    
    /**
     * Returns the configuration options for the queue
     *
     * @return array
     */
    public function getOptions()
    {
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
     * Set the adapter for this queue
     *
     * @param  string $adapter
     * @return \Talis\Services\ActiveMQ\Queue 
     */
    /*
    public function setAdapter(string $adapter)
    {
        /*
         * Create an instance of the adapter class.
         * Pass the configuration to the adapter class constructor.
         */
    /*  
    $adapter_obj = new $adapter($this->getOptions(), $this);
        if (!$adapter instanceof Adapter) {
            throw new InvalidArgumentException("Adapter class [{$adapter}] does not implement \Talis\Services\ActiveMQ\Adapter");
        }
        
        $this->_adapter = $adapter;
        $this->_adapter->setQueue($this);
        
        $name = $this->getOption(self::NAME);
        if (null !== $name) {
            $this->_setName($name);xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
        }
        
        return $this;
    }
    
    */
    
    /**
     * Get the adapter for this queue
     *
     * @return Adapter
     */
    /*
    public function getAdapter()
    {
        return $this->_adapter;
    }
    */
    
    /**
     * @param  string $className
     * @return \ZendQueue\Queue Provides a fluent interface
     */
    public function setMessageClass($className)
    {
        $this->_messageClass = (string) $className;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessageClass()
    {
        return $this->_messageClass;
    }
    
    /**
     * @param  string $className
     * @return Queue Provides a fluent interface
     */
    public function setMessageSetClass($className):Queue{
        $this->_messageSetClass = (string) $className;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getMessageSetClass()
    {
        return $this->_messageSetClass;
    }
    
    /**
     * Get the name of the queue
     *
     * Note: _setName() used to exist, but it caused confusion with createQueue
     * Will evaluate later to see if we should add it back in.
     *
     * @return string
     */
    public function getName()
    {
        return $this->getOption(self::NAME);
    }
    
    /**
     * Delete the queue this object is working on.
     *
     * This queue is disabled, regardless of the outcome of the deletion
     * of the queue, because the programmers intent is to disable this queue.
     *
     * @return boolean
     */
    /*
    public function deleteQueue()
    {
        if ($this->isSupported('delete')) {
            $deleted = $this->getAdapter()->delete($this->getName());
        } else {
            $deleted = true;
        }
        
        /**
         * @see \ZendQueue\Adapter\Null
         */
    /*
        $this->setAdapter(new Adapter\Null($this->getOptions()));
        
        return $deleted;
    }
    */
    
    /**
     * Delete a message from the queue
     *
     * Returns true if the message is deleted, false if the deletion is
     * unsuccessful.
     *
     * Returns true if the adapter doesn't support message deletion.
     *
     * @param  \ZendQueue\Message $message
     * @return boolean
     * @throws \Exception
     */
    public function deleteMessage(\ZendQueue\Message $message)
    {
        $frame = $this->_client->createFrame();
        $frame->setCommand('ACK');
        $frame->setHeader('message-id', $message->handle);
        $this->_client->send($frame);
    }
    
    /**
     * Send a message to the queue
     *
     * @param  mixed $message message
     * @return \ZendQueue\Message
     * @throws \Exception
     */
    public function send($message)
    {
        //return $this->getAdapter()->send($message);
        $frame = $this->_client->createFrame();
        $frame->setCommand('SEND');
        $frame->setHeader('destination', $this->getName());
        $frame->setHeader('content-length', strlen($message));
        $frame->setBody((string) $message);
        $this->_client->send($frame);
        
        $data = array(
            'message_id' => null,
            'body'       => $message,
            'md5'        => md5($message),
            'handle'     => null
        );
        
        $options = array(
            'queue' => $this,
            'data'  => $data
        );
        $classname = $this->getMessageClass();
        return new $classname($options);
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
     * @param  \ZendQueue\Queue $queue
     * @return boolean
     */
    protected function isSubscribed():bool{
        return $this->_subscribed;
    }
    
    /**
     * Subscribes the client to the queue.
     *
     * @param  \ZendQueue\Queue $queue
     * @return void
     */
    protected function subscribe(array $subscribe_headers = [])
    {
        $frame = $this->_client->createFrame();
        $frame->setCommand('SUBSCRIBE');
        $subscribe_headers['destination'] = $this->getName();
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
     * @return \ZendQueue\Message\MessageIterator
     */
    public function receive(\Closure $frame_handler,?int $maxMessages=100,?int $timeout=self::RECEIVE_TIMEOUT_DEFAULT,array $subscribe_headers=[]):array
    {
        //return $this->getAdapter()->receive($frame_handler,$maxMessages, $timeout, $subscribe_headers);
        
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
                                if(function_exists('dbgr')) dbgr('FRAME RECEIVED',$datum);
                                $data[] = $datum;
                                $frame_handler($response->getBody());
                                $this->deleteThyMessage($datum['handle']);
                                break;
                            default:
                                $block = print_r($response, true);
                                throw new \ZendQueue\Exception\UnexpectedValueException('Invalid response received: ' . $block,$i);
                        }
                    }catch(\ZendQueue\Exception\ConnectionException $e){
                        //nothing new comes into the socket. Obviously, this does not behave like a daemon
                        break;
                    }//eof catch
                    
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
     * Return a list of queue capabilities functions
     *
     * $array['function name'] = true or false
     * true is supported, false is not supported.
     *
     * @param  string $name
     * @return array
     */
    /*
    public function getCapabilities()
    {
        return $this->getAdapter()->getCapabilities();
    }
    */
    /**
     * Indicates if a function is supported or not.
     *
     * @param  string $name
     * @return boolean
     */
    /*
    public function isSupported($name)
    {
        $translation = array(
            'deleteQueue' => 'delete',
            'createQueue' => 'create'
        );
        
        if (isset($translation[$name])) {
            $name = $translation[$name];
        }
        
        return $this->getAdapter()->isSupported($name);
    }
    */
    /**
     * Get an array of all available queues
     *
     * @return array
     * @throws \ZendQueue\Exception
     */
  /*  public function getQueues()
    {
        if (!$this->isSupported('getQueues')) {
            throw new Exception\UnsupportedMethodCallException(__FUNCTION__ . '() is not supported by ' . get_class($this->getAdapter()));
        }
        
        return $this->getAdapter()->getQueues();
    }
    */
    /**
     * Set the name of the queue
     *
     * @param  string           $name
     * @return \ZendQueue\Queue|false Provides a fluent interface
     */
    /*
    protected function _setName(string $name)
    {
        if ($this->getAdapter()->isSupported('create')) {
            if (!$this->getAdapter()->isExists($name)) {
                $timeout = $this->getOption(self::TIMEOUT);
                
                if (!$this->getAdapter()->create($name, $timeout)) {
                    // Unable to create the new queue
                    return false;
                }
            }
        }
        
        $this->setOption(self::NAME, $name);
        
        return $this;
    }
    */
    
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
        //$info['adapter']                  = get_class($this->getAdapter());
        /*
        foreach ($this->getAdapter()->getCapabilities() as $feature => $supported) {
            $info['adapter-' . $feature]  = ($supported) ? 'yes' : 'no';
        }*/
        $info['options']                  = $this->getOptions();
        //$info['options']['driverOptions'] = '[hidden]';
        $info['currentQueue']             = $this->getName();
        $info['messageClass']             = $this->getMessageClass();
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
        $frame = $this->_client->createFrame();
        $frame->setCommand('DISCONNECT');
        $this->_client->send($frame);
        unset($this->_client);
    }
}
