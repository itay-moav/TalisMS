<?php namespace Api;

/**
 * Responsebility: Parses the user input to identify the API class to instantiate
 * 
 * Notice last element in the chain must implement  \Talis\commons\iRenderable
 * otherwise the response can not be rendered and it will error out after the last chainlink is processed
 * 
 * @author Itay Moav
 * @Date  2017-05-19
 */
class TestFilterRead extends \Talis\Chain\aFilteredValidatedChainLink{
	protected $filters                  = [
			[\Talis\Chain\Filters\TransformParam::class,['mumble','blabla','brumbrum']]
	],
		      $dependencies 			= [
				[\Talis\Chain\Dependencies\HasBody::class,[]]
	]
	
	;
	
	/**
	 * a default way to finish a chain.
	 * Mostly for debug purposes
	 *
	 * @see \Talis\Chain\AFilteredValidatedChainLink::get_next_bl()
	 */
	protected function get_next_bl():array{
		return [[\Talis\Chain\DoneSuccessfull::class,[]]];
	}
}