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
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Talis\Chain\AChainLink::process()
	 */
	final private function processOLDPROCESS_TOBEDELETED():\Talis\Chain\aChainLink{
		$FinalLink = $this;
		$valid    = $this->validate();
		if($valid && !$this->chain_container->isEmpty()){
			$next_link_class = $this->chain_container->pop();
			$name   = $next_link_class[0];
			$params = $next_link_class[1];
			$next_link = new $name($this->Request,$this->Response,$params);
			$next_link->set_chain_container($this->chain_container);
			$FinalLink = $next_link->process();
			
		} elseif($valid && $this->chain_container->isEmpty()) {//for clear sake I added the second condition...how can we have a dependency with no continue? There always must be a BL at the end.
			$FinalLink =  new \Talis\Chain\Errors\BLLinkMissingInChain($this->Request,null);
			
		} elseif(!$valid) {//making sure this is the last link in the chain. 
		    $this->chain_container->clear();
		}
		return $FinalLink;
	}
}
