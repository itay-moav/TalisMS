<?php
/**
 * Apply session handling to any class who wishes to
 * 
 * @author itaymoav
 */
trait BL_tSession{
	/**
	 * @var SiTEL_Data_Session
	 */
	protected $SessionCaching=null; //Session object for this model

	/**
	 * @return BL_tSession
	 */
	protected function setSession($namespace){
		$this->SessionCaching=new Data_Session($namespace);
		return $this;
	}
	
	/**
	 * destroys the session namespace
	 *
	 * @return BL_tSession
	 */
	protected function destroySession(){
		$this->SessionCaching->destroy();
		return $this;
	}
	
	/**
	 * Sets all the session
	 *
	 * @return BL_tSession
	 */
	protected function setSessionAllValue(array $value){
		return $this->SessionCaching->setAll($value);
	}
	
	/**
	 * set value in session with key
	 *
	 * @return BL_tSession
	 */
	protected function setSessionValue($key, $value){
		$this->SessionCaching->set($key, $value);
		return $this;
	}
	
	/**
	 * return the choosen value
	 *
	 * @return mixed
	 */
	protected function getSessionValue($key, $default=null){
		return $this->SessionCaching->get($key, $default);
	}
	
	/**
	 * get the namespace from session
	 *
	 * @return array
	 */
	protected function getSessionAllValue(){
		return $this->SessionCaching->getAll();
	}
	
}