<?php

// TO BE DELETED ONCE LAUNCHER RULES AND ANY OTHER USING MOVES TO NEW REDIS SCHEMA CLASSES (ITAY)
abstract class Data_Redis_DataPusherAbstract{
	
	
	
	/**
	 * get cache
	 * @param unknown_type id
	 */
	static public function get($id){
		$class = get_called_class();
		$Wrapper = new $class($id);
		return $Wrapper->getCache();
	}
	
	static public function destroy($id){
		$class = get_called_class();
		$Wrapper = new $class($id);
		return $Wrapper->destroyCache();
	}
	
	static public function destroy_create($id){ 
		$class = get_called_class();
		$Wrapper = new $class($id);
		$Wrapper->destroyCache();
		return $Wrapper->destroyCache(); 
	}
	
	
	
	
	
	protected $id;
	protected $cacheKey = NULL;
	protected $serialized = false; 
	
	

	
	public function __construct($id){
		$this->id = $id;

		if (!$this->cacheKey){
			error_monitor("DATAPUSHER: key is not set", 2);
		}
		
		$this->cacheKey .= '::'.$this->id;
	}
	
	public function getCache(){
		
		$result = Data_Redis_Client::getInstance()->get($this->cacheKey);
		
		if ($result){
			return $this->serialized ? unserialize($result) : $result;
		}
		
		
		return $this->storeCache();
		

	}
	public function destroyCache(){
		Data_Redis_Client::getInstance()->delete($this->cacheKey);
		return $this;
	}
	
	abstract protected function getData();
	
	private function storeCache(){
		$data = $this->getData();
		if ($data){
	
			$store_data = $this->serialized ? serialize($data) : $data;
	
			Data_Redis_Client::getInstance()->set($this->cacheKey, $store_data);
			return $data;
		}
		
		return Null;
	}
}