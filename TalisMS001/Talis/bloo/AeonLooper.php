<?php
/**
 * Class for iterating iteratable files
 * 
 * $BL_AeonLooper = new BL_AeonLooper(new Data_CSV_Reader(file));
 * $BL_AeonLooper->run();
 * 
 * Enter processing logic under process() and/or processHeader()
 * 
 * @author itay revised by holly
 */
abstract class BL_AeonLooper{
    /**
     * @var Iterator
     */
    protected   $iterator                       = NULL,
                $row                            = [],
                
                /**
                 * Filters/validators for records (exclude headers)
                 */
                $record_level_filters           = [],
                $field_level_filters            = [],
                $record_level_validators        = [],
                $field_level_validators         = [],
                
                /**
                 * Filters/validators for headers only
                 */
                $record_header_level_filters    = [],
                $field_header_level_filters     = [],
                $record_header_level_validators = [],
                $field_header_level_validators  = [],
                
                /**
                 * Number of header rows
                 */
                $num_headers                    = 1,
                
                $last_error_message             = ''
    ;
    

    /**
     * Construct class 
     * @param Iterator $iterator
     */
    public function __construct(Iterator $iterator){
        $this->iterator = $iterator;
        $this->preInit()
             ->load_filters()
             ->load_validators()
             ->load_header_filters()
             ->load_header_validators()
             ->init()
        ;
    }
    
    /**
     * Pre-init
     * @return BL_AeonLooper
     */
    protected function preInit() {
        return $this;
    }
    
    /**
     * Init
     * @return BL_AeonLooper
     */
    protected function init(){
        return $this;
    }
                 
    /**
     * Default error handler
     */
    protected function handle_errors(){
        dbgr('Record did not pass validation',$this->row);
    }
    
    /**
     * Entry point for iteration
     * @return BL_AeonIterator
     */
        public function run(){
        $this->runHeaders();
        while($this->iterator->valid()) {
            $this->row = $this->iterator->current();
            //skip if certain rules apply
            if(!$this->skip()){
            
                if($this->validate()){ //this one calls the apply_filters to save a loop
                    $this->process();
                }else{
                    $this->handle_errors();
                }
                
            }
            $this->iterator->next();
        }
        
        //  Post Processing
        $this->postProcess();
        $this->closeResources();
        return $this;//for chaining and PONNIES!
    }
   /**
     * Run process for headers
     */
    protected function runHeaders() {
        $this->iterator->rewind();
        for ($i = 0; $i < $this->num_headers; $i++) {
            $this->row = $this->iterator->current();
            
            if($this->validate_header()){ //this one calls the apply_filter_header to save a loop
                $this->processHeader();
            }else{
                $this->handle_errors();
            }
            $this->iterator->next();
        }
    }
    /**
     * 
     */
    protected function skip(){
        return FALSE;
        
    }
    /**
     * Logic for post process
     */
    protected function postProcess() { 
        return $this;
    }
    
    /**
     * Close any resources
     */
    protected function closeResources() { 
        return $this;
    }
    
    /**
     * Apply filter to each record
     * @param int $current_index
     * @return BL_AeonLooper
     */
    final protected function apply_filters($current_index){
        if($this->record_level_filters){
            foreach($this->record_level_filters as $RecLvlFilter){
                $this->row[$current_index] = $RecLvlFilter->filter($this->row[$current_index]);
            }
        }
    
        if(isset($this->field_level_filters[$current_index])){
            foreach($this->field_level_filters[$current_index] as $FLvlFilter){
                $this->row[$current_index]=$FLvlFilter->filter($this->row[$current_index]);
            }
        }
        return $this;
    }
    
    /**
     * validate each record, applying filters first
     * @return boolean
     */
    final protected function validate(){
        //first make sure we even have a record
        if(empty($this->row)) return false;
        
        //use user validator
        foreach($this->row as $place => &$field){
            $this->apply_filters($place);
    
            //record level validation
            foreach($this->record_level_validators as $RecLvlValidator){
                if(!$RecLvlValidator->validate($field)){
                    $this->last_error_message= $RecLvlValidator->message();
                    
                    return false;
                }
            }
    
            //field level validation
            if(isset($this->field_level_validators[$place])){
                foreach($this->field_level_validators[$place] as $FLvlValidator){
                    if(!$FLvlValidator->validate($field)){
                        $this->last_error_message= $FLvlValidator->message()." -> Value: ".$field;
                        return false;
                    }
                }
            }
        }
    
        return true;
    }
    
    /**
     * instantiate the filters into the record(row level)
     * and field level arrays.
     * Specific for each concrete class
     * 
     *  IN comments is a demo on how this can be used. Do not delete the comment
     *
     * @return BL_AeonLooper
     */
    protected function load_filters(){
        /* DO NOT DELETE COMMENT
        $this->record_level_filters = [new Form_Filter_Trim];
        $this->field_level_filters  = [User_Upload_GuestParser::PARSED_PLACE__FIRST_NAME  => [new Form_Filter_Name, new Some_Other_Filter implementing the Form_Filter_i interface],
                                       User_Upload_GuestParser::PARSED_PLACE__MIDDLE_NAME => [new Form_Filter_Name],
                                       User_Upload_GuestParser::PARSED_PLACE__LAST_NAME   => [new Form_Filter_Name]
        ];
        */
        return $this;
    }
    
    /**
     * instantiate the validators into the record(row level)
     * and field level arrays 
     * Specific for each concrete class
     * 
     * In comment a demo how to use it.
     * 
     * @return BL_AeonLooper
     */
    protected function load_validators(){
        /* DO NOT DELETE THIS COMMENT
          
        $this->record_level_validators = [new Form_Validator_stringLength(false,['min'=>0,'max'=>255])];
        $this->field_level_validators  = [User_Upload_GuestParser::PARSED_PLACE__EMAIL       => [new Form_Validator_notEmpty,new Form_Validator_emailAddress],
            User_Upload_GuestParser::PARSED_PLACE__FIRST_NAME  => [new Form_Validator_notEmpty],
            User_Upload_GuestParser::PARSED_PLACE__LAST_NAME   => [new Form_Validator_notEmpty]
        ];
        */
        return $this;
    }

    /**
     * house of actual logic 
     */
    abstract protected function process();
    
    /**
     * house of actual logic for headers
     */
    protected function processHeader() {  }
    
    /**
     * Instantiate the filters into the record or fields (header level)
     * Demo in comments. Do not delete!
     * 
     * @return BL_AeonLooper
     */
    protected function load_header_filters() {
        /* DO NOT DELETE THIS COMMENT
        
        $this->record_header_level_filters = [new Form_Filter_Trim];
        $this->field_header_level_filters = [
                                                User_Upload_GuestParser::PARSED_PLACE__FIRST_NAME  => [new Form_Filter_Name, new Some_Other_Filter implementing the Form_Filter_i interface],
                                                User_Upload_GuestParser::PARSED_PLACE__MIDDLE_NAME => [new Form_Filter_Name],
                                                User_Upload_GuestParser::PARSED_PLACE__LAST_NAME   => [new Form_Filter_Name]
                                            ];
        */
        
        return $this;
    }
    
    /**
     * Instantiate the filters into the record or fields (header level)
     * Demo in comments. Do not delete!
     * 
     * @return BL_AeonLooper
     */
    protected function load_header_validators() {
        /* DO NOT DELETE THIS COMMENT

        $this->record_header_level_validators = [new Form_Validator_stringLength(false,['min'=>0,'max'=>255])];
        $this->field_header_level_validators = [
                                                    User_Upload_GuestParser::PARSED_PLACE__EMAIL       => [new Form_Validator_notEmpty,new Form_Validator_emailAddress],
                                                    User_Upload_GuestParser::PARSED_PLACE__FIRST_NAME  => [new Form_Validator_notEmpty],
                                                    User_Upload_GuestParser::PARSED_PLACE__LAST_NAME   => [new Form_Validator_notEmpty]
                                               ];
        */
        
        return $this;
    }
    
    /**
     * Validate the header
     * @return boolean
     */
    final protected function validate_header() {
        //first make sure we even have a record
        if(!isset($this->row[0])) return false;
        
        foreach($this->row as $place => $field) {
            $this->apply_filter_header($place);
            
            // record level validation
            foreach($this->record_header_level_validators as $RecLvlValidator){
                if(!$RecLvlValidator->validate($field)){
                    return false;
                }
            }
            
            // field level validation
            if(isset($this->field_header_level_validators[$place])){
                foreach($this->field_header_level_validators[$place] as $FLvlValidator){
                    if(!$FLvlValidator->validate($field)){
                        return false;
                    }
                }
            }
        }
        
        return true;
    }
    
    /**
     * Apply filters to header
     * @param int $current_index
     * @return BL_AeonLooper
     */
    final protected function apply_filter_header($current_index) {
        // record level filter
        if($this->record_header_level_filters){
            foreach($this->record_header_level_filters as $RecLvlFilter){
                $this->row[$current_index] = $RecLvlFilter->filter($this->row[$current_index]);
            }
        }
        
        // field level filter
        if(isset($this->field_header_level_filters[$current_index])){
            foreach($this->field_header_level_filters[$current_index] as $FLvlFilter){
                $this->row[$current_index]=$FLvlFilter->filter($this->row[$current_index]);
            }
        }
        return $this;
    }
}