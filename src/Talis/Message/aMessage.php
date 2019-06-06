<?php namespace Talis\Message;
abstract class aMessage{
	protected $headers = [],
	          $body = null
    ;
	
    public function getHeaders():array{
        return $this->headers;
    }
    
    public function setHeader(string $header):aMessage{
        $this->headers[] = $header;
        return $this;
    }
	
    /**
	 * The json decoded body or stdClass
	 *
	 * @return \stdClass|NULL
	 */
	public function getBody():\stdClass{
		return $this->body??$this->setBody(new \stdClass);
	}
	
	public function setBody(\stdClass $body):\stdClass{
		return $this->body = $body;
	}
}
