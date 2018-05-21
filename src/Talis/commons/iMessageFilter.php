<?php namespace Talis\commons;
interface iMessageFilter{
	/**
	 * gets as input a message. MODIFIES THE ACTUAL MESSAGE which is pased by reference.
	 * returns NOTHING!
	 * BE AWARE THIS IS AGAINST THE CURRENT buzz to go fully immutable!
	 *  
	 * @param \Talis\Message\Response $message
	 */
	public function filter(\Talis\Message\aMessage $message):void;
}
