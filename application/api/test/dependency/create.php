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
class TestDependencyCreate extends \Talis\Chain\aFilteredValidatedChainLink{

	protected array $filters                  = [];
	
	protected array $dependencies 			  = [
			  		[\Talis\Chain\Dependencies\GetFieldExist::class,['field'=>'user']],
			  		[\Talis\Chain\Dependencies\GetFieldExist::class,['field'=>'baba']],
			  ]
	;
			  
	/**
	 * a default way to finish a chain.
	 * Mostly for debug purposes
	 * 
	 * @return array<array>
	 * 
	 * @see \Talis\Chain\AFilteredValidatedChainLink::get_next_bl()
	 */
	protected function get_next_bl():array{
		return [[\Talis\Chain\DoneSuccessfull::class,[]]];
	}
}