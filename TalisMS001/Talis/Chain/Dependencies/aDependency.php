<?php namespace Talis\Chain\Dependencies;
/**
 * 
 * @author admin
 *
 */
abstract class aDependency extends \Talis\Chain\aChainLink implements \Talis\commons\iRenderable{
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
	public function __construct(\Talis\Message\Request $Request,array $params=[]){
		parent::__construct($Request);
		$this->params = $params;
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Talis\Chain\AChainLink::process()
	 */
	final public function process():\Talis\Chain\AChainLink{
		$response = $this;
		$valid    = $this->validate();
		if($valid && !$this->chain_container->isEmpty()){
			$next_link_class = $this->chain_container->pop();
			$name   = $next_link_class[0];
			$params = $next_link_class[1];
			$next_link = new $name($this->Request,$params);
			$next_link->set_chain_container($this->chain_container);
			$response = $next_link->process();
		} elseif($valid && $this->chain_container->isEmpty()) {//for clear sake I added the second condition...how can we have a dependency with no continue? There always must be a BL at the end.
			$response =  new \Talis\Chain\Errors\BLLinkMissingInChain($this->Request);
		}
		return $response;
	}
}
