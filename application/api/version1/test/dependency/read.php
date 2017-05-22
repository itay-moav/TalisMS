<?php namespace Api;
use Talis\Logger as L;

/**
 * Responsebility: Parses the user input to identify the API class to instantiate
 * @author Itay Moav
 * @Date  2017-05-19
 */
class TestDependencyRead extends \Talis\Chain\AFilteredValidatedChainLink implements \Talis\commons\iRenderable{
	public function render():void{
		L\dbgn('dep read');
		echo "{type:test,msg:dep read}";
	}
}