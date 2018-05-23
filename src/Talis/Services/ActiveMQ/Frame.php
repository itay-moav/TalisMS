<?php namespace Talis\Services\ActiveMQ;
//Inspired by ZendQueue;
use function Talis\Logger\dbgn;
use function Talis\Logger\dbgr;

/**
 * The StompFrame
 */
class Frame
{
    const END_OF_FRAME   = "\x00\n";
    const CONTENT_LENGTH = 'content-length';
    const EOL            = "\n";
    
    /**
     * Headers for the frame
     *
     * @var array
     */
    protected $_headers = [];
    
    /**
     * The command for the frame
     *
     * @var string
     */
    protected $_command = null;
    
    /**
     * The body of the frame
     *
     * @var string
     */
    protected $_body = null;
    
    /**
     * Do the content-length automatically?
     */
    protected $_autoContentLength = true;
    
    /**
     * Constructor
     */
    /*
    public function __construct(){
        $this->setHeaders(array());
        $this->setBody(null);
        $this->setCommand(null);
        $this->setAutoContentLength(true);
    }*/
    
    /**
     * get the status of the auto content length
     *
     * If AutoContentLength is true this code will automatically put the
     * content-length header in, even if it is already set by the user.
     *
     * This is done to make the message sending more reliable.
     *
     * @return boolean
     */
    public function getAutoContentLength():bool
    {
        return $this->_autoContentLength;
    }
    
    /**
     * setAutoContentLength()
     *
     * Set the value on or off.
     *
     * @param boolean $auto
     * @return Frame;
     */
    public function setAutoContentLength(bool $auto):Frame
    {
        /*
        if (!is_bool($auto)) {
            throw new Exception_InvalidArgument('$auto is not a boolean');
        }*/
        
        $this->_autoContentLength = $auto;
        return $this;
    }
    
    /**
     * Get the headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->_headers;
    }
    
    /**
     * Set the headers
     *
     * Throws an exception if the array values are not strings.
     *
     * @param array $headers
     * @return $this
     * @throws Exception_AMQ
     */
    public function setHeaders(array $headers)
    {
        foreach ($headers as $header => $value) {
            $this->setHeader($header, $value);
        }
        
        return $this;
    }
    
    /**
     * Sets a value for a header
     *
     * @param  string $header
     * @param  string $value
     * @return Frame
     * @throws Exception_InvalidArgument
     */
    public function setHeader(string $header, $value):Frame{
        if (!is_scalar($value)) {
            throw new Exception_InvalidArgument('$value is not a scalar: ' . print_r($value, true));
        }
        
        $this->_headers[$header] = $value;
        return $this;
    }
    
    
    /**
     * Returns a value for a header
     *
     * Returns false if the header does not exist.
     *
     * @param  string $header
     * @return string|false
     */
    public function getHeader(string $header)
    {
        return isset($this->_headers[$header])? $this->_headers[$header] : false;
    }
    
    /**
     * Return the body for this frame
     *
     * Returns false if the body does not exist
     *
     * @return false|string
     */
    public function getBody()
    {
        return $this->_body === null
        ? false
        : $this->_body;
    }
    
    /**
     * Set the body for this frame
     *
     * Set to null for no body.
     *
     * @param  string|null $body
     * @return Frame
     */
    public function setBody(?string $body)
    {
        $this->_body = $body;
        return $this;
    }
    
    /**
     * Return the command for this frame
     *
     * Return false if the command does not exist
     *
     * @return string|false
     */
    public function getCommand()
    {
        return $this->_command === null
        ? false
        : $this->_command;
    }
    
    /**
     * Set the body for this frame
     *
     * @param  string|null
     * @return Frame
     * @throws Exception_InvalidArgument
     */
    public function setCommand(?string $command):Frame{
        $this->_command = $command;
        return $this;
    }
    
    /**
     * Takes the current parameters and returns a Stomp Frame
     *
     * @return string
     * @throws Exception_Logic
     */
    public function toFrame()
    {
        if ($this->getCommand() === false) {
            throw new Exception_Logic('You must set the command');
        }
        
        $command = $this->getCommand();
        $headers = $this->getHeaders();
        $body    = $this->getBody();
        $frame   = '';
        
        // add a content-length to the SEND command.
        // @see http://stomp.codehaus.org/Protocol
        if ($this->getAutoContentLength()) {
            $headers[self::CONTENT_LENGTH] = strlen($this->getBody());
        }
        
        // Command
        $frame = $command . self::EOL;
        
        // Headers
        foreach ($headers as $key=>$value) {
            $frame .= $key . ':' . $value . self::EOL;
        }
        
        // Seperator
        $frame .= self::EOL; // blank line required by protocol
        
        // add the body if any
        if ($body !== false) {
            $frame .= $body;
        }
        $frame .= self::END_OF_FRAME;
        
        return $frame;
    }
    
    /**
     * @see toFrame()
     * @return string
     */
    public function __toString()
    {
        return $this->toFrame();
        /*
        try {
            $return = $this->toFrame();
        } catch (Exception\ExceptionInterface $e) {
            $return = '';
        }
        return $return;
        */
    }
    
    /**
     * Extract the Command from a response string frame or returns false
     *
     * @param string $frame - a stomp frame
     * @return string|false
     */
    public static function extractCommand($frame)
    {
        // todo: Commands are in caps per spec, this is not checked here
        if (preg_match("|^([A-Z]+)\n|i", $frame, $m) == 1) {
            return $m[1];
        }
        return false;
    }
    
    /**
     * Extract the headers from a response string
     *
     * @param string $frame - a stromp frame
     * @return array
     */
    public static function extractHeaders($frame)
    {
        $parts = preg_split('|(?:\r?\n){2}\n|m', $frame, 2);
        if (!isset($parts[0])) {
            return array();
        }
        
        if (!preg_match_all("|([\\w-]+):\s*(.+)\n|", $parts[0], $m, PREG_SET_ORDER)) {
            return array();
        }
        
        $headers = array();
        foreach ($m as $header) {
            $headers[mb_strtolower($header[1])] = $header[2];
        }
        
        return $headers;
    }
    
    /**
     * Extract the body from a response string
     *
     * @param string $frame - a stomp frame
     * @return string
     * @throws Exception_Domain when the body is badly formatted
     */
    public static function extractBody($frame):string{
        $parts = preg_split('|(?:\r?\n){2}|m', $frame, 2);
        
        if (!isset($parts[1])) {
            return '';
        }
        if (substr($parts[1], -2) != self::END_OF_FRAME) {
            throw new Exception_Domain('badly formatted body not frame terminated');
        }
        return substr($parts[1], 0, -2);
    }
    
    
    /**
     * Accepts a frame and deconstructs the frame into its component parts
     *
     * @param  string $frame - a stomp frame
     * @return Frame
     */
    public function fromFrame($frame):Frame{
        if (!is_string($frame)) {
            throw new Exception_InvalidArgument('$frame is not a string');
        }
        $this->setCommand(self::extractCommand($frame));
        $this->setHeaders(self::extractHeaders($frame));
        $this->setBody(self::extractBody($frame));
        return $this;
    }
}
