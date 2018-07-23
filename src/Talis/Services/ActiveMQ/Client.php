<?php namespace Talis\Services\ActiveMQ;
//Inspired by ZendQueue;

/*uncomment when want to debug
\Talis\Logger\MainZim::include();//enable dbg functions
use function Talis\Logger\dbgn;
use function Talis\Logger\dbgr;
*/

/**
 * The Stomp client interacts with a Stomp server.
 */
class Client
{
    /**
     * @var Connection
     */
    protected $_connection;
    
    /**
     * Add a server to connections
     *
     * @param string scheme
     * @param string host
     * @param integer port
     */
    public function __construct(string $scheme, string $host, int $port){
        $this->addConnection($scheme, $host, $port);
        //$this->getConnection()->setFrameClass('\ZendQueue\Stomp\Frame');
    }
    
    /**
     * Shutdown
     *
     * @return void
     */
    public function __destruct()
    {
        if ($this->getConnection()) {
            $this->getConnection()->close(true);
        }
    }
    
    /**
     * Add a connection to this client.
     *
     * Attempts to add this class to the client.  Returns a boolean value
     * indicating success of operation.
     *
     * You cannot add more than 1 connection to the client at this time.
     *
     * @param string  $scheme ['tcp', 'udp']
     * @param string  host
     * @param integer port
     * @return boolean
     */
    public function addConnection(string $scheme, string $host, int $port):bool{
        $connection = new Connection();
        
        if ($connection->open($scheme, $host, $port)) {
            $this->setConnection($connection);
            return true;
        }
        
        $connection->close();
        return false;
    }
    
    /**
     * Set client connection
     *
     * @param Connection
     * @return Client
     */
    public function setConnection(Connection $connection):Client{
        $this->_connection = $connection;
        return $this;
    }
    
    /**
     * Get client connection
     *
     * @return Connection|null
     */
    public function getConnection():?Connection{
        return $this->_connection;
    }
    
    /**
     * Send a stomp frame
     *
     * Returns true if the frame was successfully sent.
     *
     * @param Frame $frame
     * @return Client
     */
    public function send(Frame $frame):Client{
        $this->getConnection()->write($frame);
        return $this;
    }
    
    /**
     * Receive a frame
     *
     * Returns a frame or false if none were to be read.
     *
     * @return Frame|boolean
     */
    public function receive(){
        return $this->getConnection()->read();
    }
    
    /**
     * canRead()
     *
     * @return boolean
     */
    public function canRead()
    {
        return $this->getConnection()->canRead();
    }
    
    /**
     * creates a frame class
     *
     * @return Frame
     */
    public function createFrame(){
        return $this->getConnection()->createFrame();
    }
}
