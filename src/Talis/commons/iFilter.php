<?php namespace Talis\commons;
interface iFilter{
	/**
	 * gets as input a message. MODIFIES THE ACTUAL MESSAGE which is pased by reference.
	 * returns NOTHING!
	 * BE AWARE THIS IS AGAINST THE CURRENT buzz to go fully immutable!
	 *  
	 * @param \Talis\Message\Request $Request
	 */
	public function filter(\Talis\Message\Request $Request):void;
}
