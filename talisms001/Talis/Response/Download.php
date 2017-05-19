<?php
/**
 * Return responses as a http file download
 */
class Response_Download extends Response_Abstract{
	/**
	 * @var string
	 */
	private   $file_data = null;
	
	/**
	 * @param array $file_data ['location' => absolute path to file on the file system.,'file_name'='how do we call the file' ,'file_type' => zip,]
	 * @return Response_Download
	 */
	public function setData(array $file_data){
		$this->file_data = $file_data;
		$this->headers[] = "{$_SERVER['SERVER_PROTOCOL']} 200 OK";
		switch($file_data['file_type']){
		    case 'zip':
		        $this->headers[] = 'Content-Type: application/octet-stream';
		        break;
		        
		    default:
		        throw new Exception('no file type was specified');
		}
		$this->headers[] = 'Content-Transfer-Encoding: Binary';
		$file_size = filesize($this->file_data['location']);
		$this->headers[] = "Content-Length: {$file_size}";
		$this->headers[] = "Content-Disposition: attachment; filename=\"{$this->file_data['file_name']}\"";
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
		$r = readfile($this->file_data['location']);
		dbgr('File Size',$r);
		return $this;
	}
}


/*
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename='.basename($file));
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file));
readfile($file);
*/