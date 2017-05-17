<?php
/**
 * Facility to return complex statuses from Model actions.
 * 
 * @author itaymoav
 */
class BL_Action_ReturnStatus{

    const   FAILED  = FALSE,
            SUCCESS = TRUE
    ;
    
    protected $message = [],
              $status,
              $data = []
    ;
    
    /**
     * @return string Last message entered (not a pop)
     */
    public function getLastMsg(){
        $c = count($this->message);
        if($c){
            return $this->message[$c-1];
        }
        return '';
    }
    
    /**
     * @param string $msg
     */
    public function pushMsg($msg){
        $this->message[] = $msg;
    }
    
    /**
     *  Get the data of the action
     *  @return array
     */
    public function data(){
    	return $this->data;
    }
    
    /**
     *  Set responseData of the action
     *  @param mixed Information that needs to be passed to an outer function
     */
    public function pushData($data){
    	$this->data[] = $data;
    }
    
    /**
     * @return array
     */
    public function messages(){
        return $this->message;
    }
    
    /**
     * set the action to success
     * @param mixed data to respond with on success
     */
    public function success($data){
    	if(!empty($data)){
    		$this->pushData($data);
    	}
        $this->status = self::SUCCESS;
        return $this;
    }
    
    /**
     * Set to failed, can also accept optional message
     * 
     * @param string $msg
     */
    public function failed($msg=''){
        if($msg){
            $this->pushMsg($msg);
        }
        $this->status = self::FAILED;
        return $this;
    }
    
    /**
     * @return string FAILED | SUCCESS
     */
    public function status(){
        return $this->status;
    }
}
