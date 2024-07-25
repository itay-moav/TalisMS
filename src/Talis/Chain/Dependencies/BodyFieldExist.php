<?php namespace Talis\Chain\Dependencies;

/**
 * Making sure that a body with some field was sent
 * 
 * @author Itay Moav
 */
class BodyFieldExist extends aDependency{
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Talis\Chain\Dependencies\ADependency::validate()
	 */
	protected function validate():bool{
		$body  = $this->Request->getBody();
		$field = $this->params['field'];
		return isset($body->$field);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Talis\commons\iRenderable::render()
	 */
	public function render(\Talis\commons\iEmitter $emitter):void{

	    \Talis\TalisMain::logger()->debug('RENDER input body');
		\Talis\TalisMain::logger()->debug($this->Request->getBody());
		
		$response = new \Talis\Message\Response;
		$response->setMessage("Mising field [{$this->params['field']}] in request body");
		$response->markDependency();
		$response->setStatus(new \Talis\Message\Status\Code400);
		$emitter->emit($response);
	}		
}
