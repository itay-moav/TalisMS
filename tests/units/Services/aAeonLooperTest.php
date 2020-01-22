<?php
use PHPUnit\Framework\TestCase;

class BaseLooper extends \Talis\Data\aAeonLooper{
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
	
	public function tpreInit():\Talis\Data\aAeonLooper{
		return $this->preInit();
	}
	
	public function tpostInit():\Talis\Data\aAeonLooper{
		return $this->postInit();
	}
	
	public function thandle_errors(){
		return true;
	}
	
	public function tpostProcess():\Talis\Data\aAeonLooper{
		return $this->postProcess();
	}
	
	public function tapply_filters():\Talis\Data\aAeonLooper{
		return $this->apply_filters(0);
	}
	
	public function tvalidate():bool{
		return $this->validate();
	}
	
	public function tload_filters():\Talis\Data\aAeonLooper{
		return $this->load_filters();
	}
	
	public function tload_validators():\Talis\Data\aAeonLooper{
		return $this->load_validators();
	}
	
	public function tprocess(){
		return $this->process();
	}
	
	public function tgetParam($param_key, $default=null){
		return $this->getParam($param_key,$default);
	}
	
	public function tsetParam($param_key,$param_value):\Talis\Data\aAeonLooper{
		return $this->setParam($param_key, $param_value);
	}
	
	public function tunsetParam($param_key):\Talis\Data\aAeonLooper{
		return $this->unsetParam($param_key);
	}
}

/**
 * 
 * @author Itay Moav
 * 
 * Testing the abstract Looper for it's API and internal working 
 * I test internals as some of those methodes are supposed to be 
 * overriden i.e. they are API
 *
 */
class Services_aAeonLooperTest extends TestCase {
	
	private function getClass(array $row,$row_type,array $params=[]){
		return new BaseLooper($row,$row_type,$params);
	}
	
	/**
	 * Tests the get/set params
	 */
	public function testGetSetParams(){
		$L = $this->getClass(['a'=>'A','b'=>'B'],\Talis\Data\aAeonLooper::ROW_TYPE__ARRAY,['ra'=>'rA','rb'=>'rB']);
		
		//initial get
		$ra = $L->tgetParam('ra', 'rC');
		$this->assertEquals('rA', $ra);
		
		//get default
		$rd = $L->tgetParam('rd', 'rC');
		$this->assertEquals('rC', $rd);
		
		//set a param and then get it
		$L->tsetParam('new_param', 'new_param_value');
		$new_param = $L->tgetParam('new_param');
		$this->assertEquals('new_param_value',$new_param);

		//unset the param, request it, and get NULL (no default supplied).
		$L->tunsetParam('new_param');
		$new_param2 = $L->tgetParam('new_param');
		$this->assertNull($new_param2);
	}
	
	public function testGetSetRowValuesArray(){
		$L = $this->getClass(['a'=>'A','b'=>'B'],\Talis\Data\aAeonLooper::ROW_TYPE__ARRAY);
		
		//initial get
		$a = $L->tgetRowField('a');
		$this->assertEquals('A', $a);
		
		//set a param and then get it
		$L->tsetRowField('c','C');
		$new_field = $L->tgetRowField('c');
		$this->assertEquals('C',$new_field);
		
		//unset the param, request it, and get NULL (no default supplied).
		$this->assertEquals(['a'=>'A','b'=>'B','c'=>'C'],$L->tgetAllRow());
		$L->tbuthcer('c');
		$this->assertEquals(['a'=>'A','b'=>'B'],$L->tgetAllRow());
		
	}
}