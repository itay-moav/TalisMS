<?php
/**
 * TODO make this Response_Json, Make the response JSON a tailored response for API
 * 
 * Return responses for AJAX as JSon, for HTML ajax, simply use The Response_View class
 */
class Response_Ajax extends Response_Abstract implements BL_iDataTransport{
	const	RESPONSE_SUCCESS	=	'success',	//to manage BL return statuses, as opposed to sofware http response codes.
			RESPONSE_FAILURE	=	'failure'
	;
	
	/**
	 * @var DataStorage_MySQL_Pager
	 */
	private $pager = null;
	
	protected	$headers		 = ['Content-Type: application/json; charset=utf-8'],
				$response_status = '',
				$response_data	 = null
	;
	
	public function setSuccess(){
		$this->response_status = self::RESPONSE_SUCCESS;
	}

	public function setFailure(){
		$this->response_status = self::RESPONSE_FAILURE;
	}
	
	/**
	 * set's status by value, treats status as boolean
	 * 
	 * @param mixed $status
	 */
	public function setStatus($status){
		$this->response_status = $status?self::RESPONSE_SUCCESS:self::RESPONSE_FAILURE;
	}
	
	protected function init(){
		$this->response_data = new stdClass;
	}
	
	/**
	 * Set data by k and v. Make sure to call after setData, as it will overwrite
	 * 
	 * @param string $k
	 * @param string $v
	 * @return Response_Ajax
	 */
	public function setDataKey($k,$v){
		$this->response_data->$k = $v;
		return $this;
	}
	
	/**
	 * TODO Populates the response with filter data
	 *  
	 *  (non-PHPdoc)
	 * @see BL_iDataTransport::setFilter()
	 */
	public function setFilter(BL_Filter_Abstract $Filter = null) {
		// TODO Auto-generated method stub
	}
	
	/**
	 * Put found data into ->found
	 * 
	 * @see BL_iDataTransport::setData()
	 * @return BL_iDataTransport
	 */
	public function setData($v){
		$this->setDataKey('total',$this->pager->getTotal());
		return $this->setDataKey('found',$v);
	}
	
	public function getData(){
		return $this->response_data;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see BL_iDataTransport::addLine()
	 */
	public function addLine($row){
		if(!isset($this->response_data->found)){
			$this->response_data->found = [];
		}
		$this->response_data->found[]=$row;
		return $row;
	}
	
	/**
	 * 
	 * @param DataStorage_MySQL_Pager $Pager
	 */
	public function setPager(Data_MySQL_Pager $Pager){
		$this->pager = $Pager;
	}
	
	/**
	 * Echo headers
	 * Echo layouts
	 * Echo view file itself
	 */
	public function render(){
		//headers
		foreach($this->headers as $header) header($header);
		$response = new stdClass;
		$response->status	= $this->response_status;
		$response->data		= $this->getData();
		echo json_encode($response);
		return $this;
	}
}

