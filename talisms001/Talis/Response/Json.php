<?php
/**
 * Return responses as JSon
 * 
 * This is a more free form response type 
 * than the Ajax response type
 */
class Response_Json extends Response_Abstract{
	protected $headers		 = ['Content-Type: application/json; charset=utf-8'];
	/**
	 * @var stdClass
	 */
	private   $response_data = null,
	          $encoded       = false // Whether the data is already json encoded or not.
    ;
	
	public function setData(stdClass $Res){
		$this->response_data = $Res;
		return $this;
	}
	
	/**
	 * Get an already json encoded string - I trust the developer here ...
	 * @param unknown $Res
	 */
	public function setJSONEncodedData($Res){
		$this->response_data = $Res;
		$this->encoded = true;
		return $this;
	}
	
	/**
	 * Echo headers
	 * Echo layouts
	 * Echo view file itself
	 */
	public function render(){
		//headers
		foreach($this->headers as $header) header($header);
		
		//body
		if($this->encoded){
		    echo $this->response_data;
		} else{ 
		    echo json_encode($this->response_data);
		}	
		return $this;
	}
}

