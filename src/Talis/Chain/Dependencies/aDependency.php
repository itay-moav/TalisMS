<?php namespace Talis\Chain\Dependencies;
/**
 * 
 * @author admin
 *
 */
abstract class aDependency extends \Talis\Chain\aChainLink implements \Talis\commons\iRenderable{
	/**
	 * logic to validate
	 * @return bool
	 */
	abstract protected function validate():bool;

	/**
	 *
	 * {@inheritDoc}
	 * @see \Talis\Chain\AChainLink::process()
	 */
	final public function process():\Talis\Chain\aChainLink{
	    //for clear sake I added this condition...how can we have a dependency with no continuity? There always must be a BL at the end.
	    if($this->chain_container->isEmpty()) {
	        return new \Talis\Chain\Errors\BLLinkMissingInChain($this->Request,null);
	    }
	    
	    $valid = $this->validate();
	    if(!$valid){
	        $this->chain_container->clear();//stops the chain, render the current
	    }
	    
	    return $this;
	}
}
