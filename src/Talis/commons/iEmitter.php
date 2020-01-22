<?php namespace Talis\commons;
interface iEmitter{
	/**
	 * Emits the input message according to the proper protocol
	 * @param \Talis\Message\Response $message
	 */
	public function emit(\Talis\Message\Response $message):void;
}
