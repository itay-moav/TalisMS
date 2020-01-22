<?php namespace Talis\Services\Session;
/**
 * Apply session handling to any class who wishes to
 *
 * @author itaymoav
 */
trait tHelper{
	/**
	 * @var Client
	 */
	protected $SessionCaching=null; //Session object for this model
	
	/**
	 * @return tHelper
	 */
	protected function setSession(string $namespace){
		$this->SessionCaching=new Client($namespace);
		return $this;
	}
	
	/**
	 * destroys the session namespace
	 *
	 * @return tHelper
	 */
	protected function destroySession(){
		$this->SessionCaching->destroy();
		return $this;
	}
	
	/**
	 * Sets all the session
	 *
	 * @return tHelper
	 */
	protected function setSessionAllValue(array $value){
		return $this->SessionCaching->setAll($value);
	}
	
	/**
	 * set value in session with key
	 *
	 * @return tHelper
	 */
	protected function setSessionValue(string $key, $value){
		$this->SessionCaching->set($key, $value);
		return $this;
	}
	
	/**
	 * return the choosen value
	 *
	 * @return mixed
	 */
	protected function getSessionValue(string $key, $default=null){
		return $this->SessionCaching->get($key, $default);
	}
	
	/**
	 * get the namespace from session
	 *
	 * @return array
	 */
	protected function getSessionAllValue():array{
		return $this->SessionCaching->getAll();
	}
}
