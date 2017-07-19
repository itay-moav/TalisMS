<?php
require_once '../../../config/environment/'.lifeCycle().'.php';
require_once app_env()['paths']['root_path']. '/config/bootstrap.php';
use function \Talis\Logger\dbgr;

class BaseLooper extends \Talis\Services\aAeonLooper{
	public function __construct($row,$row_type,array $user_params){
		$this->row = $row;
		$this->row_type = $row_type;
		parent::__construct($user_params);
	}
	
	public function tgetAllRow(){
		return $this->row;
	}
	
	public function tsetRowField($index,$value){
		return $this->setRowField($index,$value);
	}
	
	public function tgetRowField($index){
		return $this->getRowField($index);
	}
	
	public function tbuthcer($index){
		return $this->butcher($index);
	}
	
	public function tpreInit():\Talis\Services\aAeonLooper{
		return $this->preInit();
	}
	
	public function tpostInit():\Talis\Services\aAeonLooper{
		return $this->postInit();
	}
	
	public function thandle_errors(){
		return true;
	}
	
	public function tpostProcess():\Talis\Services\aAeonLooper{
		return $this->postProcess();
	}
	
	public function tapply_filters():\Talis\Services\aAeonLooper{
		return $this->apply_filters(0);
	}
	
	public function tvalidate():bool{
		return $this->validate();
	}
	
	public function tload_filters():\Talis\Services\aAeonLooper{
		return $this->load_filters();
	}
	
	public function tload_validators():\Talis\Services\aAeonLooper{
		return $this->load_validators();
	}
	
	public function tprocess(){
		return $this->process();
	}
	
	public function tgetParam($param_key, $default=null){
		return $this->getParam($param_key,$default);
	}
	
	public function tsetParam($param_key,$param_value):\Talis\Services\aAeonLooper{
		return $this->setParam($param_key, $param_value);
	}
	
	public function tunsetParam($param_key):\Talis\Services\aAeonLooper{
		return $this->unsetParam($param_key);
	}
}



$L = new BaseLooper(['a'=>'A','b'=>'B'],\Talis\Services\aAeonLooper::ROW_TYPE__ARRAY,[]);

//initial get
$a = $L->tgetRowField('a');
dbgr('first get all',$L->tgetAllRow());

//set a param and then get it
$L->tsetRowField('c','C');
dbgr('after set c',$L->tgetAllRow());

//just calling
dbgr('GET C',$L->tgetRowField('c'));

//unset the param, request it, and get NULL (no default supplied).
$L->tbuthcer('c');
dbgr('after butcher c',$L->tgetAllRow());
