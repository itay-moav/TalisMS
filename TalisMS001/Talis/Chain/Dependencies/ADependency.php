<?php namespace Talis\Chain\Dependencies;
use Talis\Chain\AChainLink;

abstract class ADependency extends \Talis\Chain\AChainLink implements \Talis\commons\iRenderable{
	/**
	 * Params to know what to validate
	 * 
	 * @var array
	 */
	protected $params = [];
	
	/**
	 * logic to validate
	 * @return bool
	 */
	abstract protected function validate():bool;

	/**
	 * 
	 * @param array $get_params
	 * @param stdClass $req_body
	 * @param array $params
	 */
	public function __construct(array $get_params,?stdClass $req_body,array $params=[]){
		parent::__construct($get_params, $req_body);
		$this->params = $params;
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Talis\Chain\AChainLink::process()
	 */
	final public function process():\Talis\Chain\AChainLink{
		$response = $this;
		if($this->validate() && !$this->chain_container->isEmpty()){
			$next_link_class = $this->chain_container->pop();
			$name   = $next_link_class[0];
			$params = $next_link_class[1];
			$next_link = new $name($this->get_params,$this->req_body,$params);
			$next_link->set_chain_container($this->chain_container);
			$response = $next_link->process();
		}
		return $response;
	}
}
