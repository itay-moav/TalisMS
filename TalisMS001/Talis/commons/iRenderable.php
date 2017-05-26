<?php namespace Talis\commons;
interface iRenderable{
	public function render(\Talis\commons\iEmitter $emitter):void;
}
