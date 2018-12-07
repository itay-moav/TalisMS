<?php namespace Talis\Message;
abstract class aMessage{
	protected $body = null;
	
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
