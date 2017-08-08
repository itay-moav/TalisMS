<?php namespace Talis\Services\Redis;
/**
 * This class is where u manage the key values for each
 * entity we create 
 * 
 * @author itaymoav
 */
class KeyBoss implements iKeyBoss{
	/**
	 * @var char key field separator
	 */
	const  FIELD_SEPARATOR = ':';	
	/**
	 * @var mixed Main entity id, usually will be the last field in the key
	 */
	protected  $main_id=0;
	/**
	 * @var string sub entity part of the key
	 */
	protected $sub_entity='';
	
	/**
	 * 
	 * @param unknown $main_id
	 * @throws Exception_NoId
	 */
	public function __construct($main_id){
		if(!$main_id){
			throw new Exception_NoId(get_class($this));
		}
		$this->main_id = $main_id;
	}

	/**
	 * @return string main entity name part of the key
	 */
	public function getEntityName(){
		return str_replace(['Redis_','_Main'],'',get_class($this));
	}
	
	/**
	 * returns the key as a string 
	 */
	public function __toString(){
		return $this->getEntityName() . self::FIELD_SEPARATOR . $this->main_id . self::FIELD_SEPARATOR . $this->getSubEntity();
	}
	
	/**
	 * @return string
	 */
	protected function getSubEntity(){
		return $this->sub_entity;
	}
}