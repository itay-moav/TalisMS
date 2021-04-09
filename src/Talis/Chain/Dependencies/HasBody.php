<?php namespace Talis\Chain\Dependencies;
/**
 * Making sure that a body with params was sent
 * 
 * @author Itay Moav
 */
class HasBody extends aDependency{
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Talis\Chain\Dependencies\ADependency::validate()
	 */
	protected function validate():bool{
		return isset($this->Request->getBody()->params) && count($this->Request->getBody()->params);
	}
	
	public function render(\Talis\commons\iEmitter $emitter):void{
		
		\ZimLogger\MainZim::$CurrentLogger->debug('RENDER input body');
		\ZimLogger\MainZim::$CurrentLogger->debug($this->Request->getBody());
		
		$response = new \Talis\Message\Response;
		$response->markDependency();
		$response->setMessage("Missing body:{params:[...]} part of request");
		$response->setStatus(new \Talis\Message\Status\Code400);
		$emitter->emit($response);
	}		
}
