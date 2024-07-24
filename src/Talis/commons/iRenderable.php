<?php namespace Talis\commons;
/**
 * A link of chainlinks must end with a chainlink that implements this
 * interface.
 * This is to assure Response is properly emmited.
 * 
 * @author itay
 *
 */
interface iRenderable{
	public function render(\Talis\commons\iEmitter $emitter):void;
}
